<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;

class DatabaseAuditor {


    public $universalRelationship;

    public $decompositionsByTable;
    public $primaryKeysByTable;
    public $foreignKeysByTable;

    public $functionalDependencies;

    public $joinsClusters;

    public $databaseSchemaGenerators;
    public $validationAlgorithms;

    public function __construct() {

        $this->reset();

        $this->databaseSchemaGenerators=[];

    }

    public function reset() {

        $this->universalRelationship=[];
        $this->functionalDependencies=[];
        $this->joinsClusters=[];

        $this->decompositionsByTable=[];
        $this->primaryKeysByTable=[];
        $this->foreignKeysByTable=[];

    }

    public function getTableNames(){
        return array_keys($this->decompositionsByTable);
    }

    public function generateDatabaseSchema(){ 

        foreach ($this->databaseSchemaGenerators as $key => $databaseSchemaGenerator) {
            $databaseSchemaGenerator->generate();
        } 
        

    }

    public function regenerateDatabaseSchemaFromListTableNames($listTableNames){

        $this->reset();

        foreach ($this->databaseSchemaGenerators as $key => $databaseSchemaGenerator) {
            $databaseSchemaGenerator->regenerateFromListTableNames($listTableNames);
        } 


    }

    public function loadDatabaseSchema($universalRelationship,$decompositionsByTable,$functionalDependencies){

        $this->universalRelationship=$universalRelationship;
        $this->decompositionsByTable=$decompositionsByTable;
        $this->functionalDependencies=$functionalDependencies;

    }


    public function executeValidationAlgorithm(){

        foreach ($this->validationAlgorithms as $key => $validationAlgorithm) {
            $validationAlgorithm->execute();
        } 

    }


    /**
     * Este metodo es una version simplificada de 
     * getNotTrivialFunctionalDependenciesProyectionInTable
     *
     * Basicamente permite generar el conjunto de dependencias 
     * funcionales necesarias para la entrada del algoritmo de verificacion de la forma 
     * BCNF.
     * 
     * El objetivo del metodo es permitirno ejecutar el algoritmo de BCNF
     * Sin tener que usar un algoritmo para generar la proyeccion del conjunto de dependencias funcionales 
     * ni la clausura
     *
     * Algoritmos que si bien ya se estan desarrollando aun no estan 100% disponibles, notese que la 
     * diferencia esta en que en vez de trabajar con la clausura del conjunto de dependencias funcionales
     * Se trabaja con solo el conjutno de dependencias funcionales.
     * La demostracion que garantiza el funcionamiento del algoritmo se
     * presenta en el archivo README.md del proyecto     * 
     */
    public function getFunctionalDependenciesForBCNFInTable($tableColumns){

        $functionalDependenciesProyection=$this->functionalDependencies;

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) {
                return !$this->isTrivialFunctionalDependency($functionalDependency);
            }
        );

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) use($tableColumns) {
                return $this->isFunctionalDependencyInTable($functionalDependency, $tableColumns);
            }
        );
        
        return $functionalDependenciesProyection;

    }

    public function getNotTrivialFunctionalDependenciesProyectionInTable($tableColumns){

        $functionalDependenciesProyection=$this->closureOfASetOfFunctionalDependenciesWithoutTrivial($this->functionalDependencies);

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) {
                return !$this->isTrivialFunctionalDependency($functionalDependency);
            }
        );

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) use($tableColumns) {
                return $this->isFunctionalDependencyInTable($functionalDependency, $tableColumns);
            }
        );
        
        return $functionalDependenciesProyection;

    }

    public function isFunctionalDependencyInTable($functionalDependency,$tableColumns){
       
        $xIsASubsetOfTable=$this->isASubset($functionalDependency['x'],$tableColumns);

        $yIsASubsetOfTable=$this->isASubset($functionalDependency['y'],$tableColumns);

        return $xIsASubsetOfTable&&$yIsASubsetOfTable;
    }

    public function closureOfASetOfFunctionalDependenciesWithoutTrivial(array $functionalDependencies): array {
        $closure = $functionalDependencies;
    
        do {
            $oldClosure = $closure;
            
            //Esto cubre la inferencia transitiva tambien
            $closure=$this->pseudotransitiveInference($closure);
            $closure=$this->decompositionInference($closure);
            //$closure=$this->unionInference($closure);

            $closure=array_filter(
                $closure, 
                function($functionalDependency) {
                    return !$this->isTrivialFunctionalDependency($functionalDependency);
                }
            );
    
        } while (!$this->areEqual($oldClosure,$closure));
    
        return $closure;
    }

    public function decompositionInference($functionalDependencies){
        $closure = $functionalDependencies;

        do {
            $oldClosure = $closure;

            foreach ($functionalDependencies as $dependency) {

                foreach ($dependency['y'] as $y) {

                    $closure = $this->union($closure,[[
                        'x'=>$dependency['x'],
                        'y'=>[$y]
                    ]]);
                            
                }
                
            }
        } while (!$this->areEqual($oldClosure,$closure));

        return $closure;
    }


    public function pseudotransitiveInference($functionalDependencies){
        $closure = &$functionalDependencies;

        do {
            $oldClosure = $closure;

            foreach ($functionalDependencies as $exterDependency) {


                foreach ($functionalDependencies as $interDependency) {


                    if($this->isASubset($exterDependency['y'],$interDependency['x'])){
                        
                        $w=$this->difference($interDependency['x'],$exterDependency['y']);
                        $wx=$this->union($w,$exterDependency['x']);

                        $closure = $this->union($closure,[[
                            'x'=>$wx,
                            'y'=>$interDependency['y']
                        ]]);
                            
                    }

                }
                
            }
        } while (!$this->areEqual($oldClosure,$closure));

        return $closure;
    }

    public function isTrivialFunctionalDependency($functionalDependency){
        return $this->isASubset($functionalDependency['y'], $functionalDependency['x']);
    }

    public function closureOfASetOfAttributes(array $atributes, array $functionalDependencies): array {
        $closure = $atributes;
    
        do {
            $oldClosure = $closure;
    
            foreach ($functionalDependencies as $dependency) {
                $x = $dependency['x'];
                $y = $dependency['y'];
    
                if($this->isASubset($x,$closure)){
                    $closure = $this->union($closure,$y);
                     
                }
            }
        } while (!$this->areEqual($oldClosure,$closure));
    
        return $closure;
    }

    public function printSet($set,$setLabel=null,$sufix="\n"){
        if($setLabel){
            print("$setLabel={");
        }else{
            print("{");
        }

        foreach ($set as $key => $value) {
            if($key!==array_key_last($set)){
                print("$value,");
            }else {
                print("$value");
            }
        }
        print("}$sufix");
    }

    public function printUniversalRelationship($universalRelationship=null){
        if($universalRelationship==null){
            $universalRelationship=$this->universalRelationship;
        }
        $this->printSet($this->universalRelationship,"R");
    }

    public function printDecompositions($decompositions=null){

        if($decompositions==null){
           $decompositions=$this->decompositionsByTable;
        }

        //print("\n");
        print("D={\n\n");

        foreach ($decompositions as $decompositionKey => $decomposition) {
            $this->printSet($decomposition,"$decompositionKey");
            print("\n");

        }

        print("}");
        print("\n");

    }

    public function printScheme($tableName,$attributes){


        print("{$tableName}(");

        foreach ($attributes as $attributeKey => $attribute) {
            if($attributeKey!==array_key_last($attributes)){
                print("$attribute, ");
            }else {
                print("$attribute)");
            }

        }

        print("\n");

    }

    

    public function printFunctionalDependencies($functionalDependencies=null){

        if($functionalDependencies==null){
            $functionalDependencies=$this->functionalDependencies;
        }

        //print("\n");
        print("F={\n\n");

        foreach ($functionalDependencies as $dependencieKey => $dependencie) {
            $this->printSet($dependencie['x'],null,null);
            print("=>");
            $this->printSet($dependencie['y'],null,null);
            print("\n");
            print("\n");
        }

        print("}");
        print("\n");

    }

    public static function areEqualFunctionalDependenciesSet($set1,$set2){

        return self::isAFunctionalDependeciesSubset($set1,$set2) && self::isAFunctionalDependeciesSubset($set2,$set1);
 
    }

    public static function isAFunctionalDependeciesSubset($subSet,$set){
        foreach($subSet as $fd){
            if(self::functionalDependencyInSet($fd,$set)){
                return true;
            }
        }
        return false;
    }

    public static function functionalDependencyInSet($fd,$set){

        foreach($set as $x){
            if(self::areEqualFunctionalDependencies($fd,$x)){
                return true;
            }
        }
        return false;
 
    }

    public static function areEqualFunctionalDependencies($fd1,$fd2){

        if($fd1['x']==$fd2['x']&& self::areEqual($fd1['y'],$fd2['y'])){
            return true;
        }else{
            return false;
        }
    }

    public static function areEqual($set1,$set2){
        return self::isASubset($set1,$set2) && self::isASubset($set2,$set1);
    }

    public static function isASubset($subSet,$set){
        foreach($subSet as $x){
            if(!in_array($x,$set)){
                return false;
            }
        }
        return true;
    }

    public static function union($set,$addSet): Array{
        foreach($addSet as $x){
            if(!in_array($x,$set)){
                $set[]=$x;
            }
        }
        return $set;
    }

    public static function difference($set,$subtractSet){
        $result=[];
        foreach($set as $x){
            if(!in_array($x, $subtractSet)){
                $result[]=$x;
            }
        }
        return $result;
    }


}
