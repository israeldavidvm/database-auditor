<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;

class Schema {


    public $universalRelationship;
    public $decompositionsByTable;
    public $functionalDependencies;


    public $primaryKeysByTable;
    public $foreignKeysByTable;


    public $joinsClusters;

    public function __construct(
        $universalRelationship=[],
        $decompositionsByTable=[],
        $functionalDependencies=[],
        $primaryKeysByTable=[],
        $foreignKeysByTable=[]
        ) {

        $this->universalRelationship=$universalRelationship;
        $this->decompositionsByTable=$decompositionsByTable;
        $this->functionalDependencies=$functionalDependencies;
        $this->primaryKeysByTable=$primaryKeysByTable;
        $this->foreignKeysByTable=$foreignKeysByTable;


    }

    public function reset() {

        $this->universalRelationship=[];
        $this->functionalDependencies=[];
        $this->joinsClusters=[];

        $this->decompositionsByTable=[];
        $this->primaryKeysByTable=[];
        $this->foreignKeysByTable=[];

    }

    public function getGroupName(){
        
        $sortTableNames=$this->getTableNames();
        sort($sortTableNames);

        return implode(
            ",",
            $sortTableNames
        );
    
    }

    public function getTableNames(){
        return array_keys($this->decompositionsByTable);
    }

    public function generateJoinsSchemas($joinsClusters){

        // echo "\n\n";
        // echo "json_encode \$this->decompositionsByTable\n";
        // echo json_encode($this->decompositionsByTable);
        // echo "\n\n";
        // echo "json_encode \$this->functionalDependencies\n";
        // echo json_encode($this->functionalDependencies);
        // echo "\n\n";

    
        $joinsSchemas=[];

        foreach ($joinsClusters as $joinsCluster) {
            $joinsSchema=new Schema();

            foreach ($joinsCluster as $tableName) {
          
                $joinsSchema->universalRelationship=Schema::union(
                    $joinsSchema->universalRelationship,
                    $this->decompositionsByTable[$tableName]
                );
    
                $joinsSchema->decompositionsByTable[$tableName]=$this->decompositionsByTable[$tableName];
                // $joinsSchema->primaryKeysByTable[$tableName]=$this->primaryKeysByTable[$tableName];
                // $joinsSchema->foreignKeysByTable[$tableName]=$this->foreignKeysByTable[$tableName];
    
            }
    
            $joinsSchema->functionalDependencies=
                $this->functionalDependencies;

            $joinsSchemas[]=$joinsSchema;
        }

        return $joinsSchemas;

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
                return !self::isTrivialFunctionalDependency($functionalDependency);
            }
        );

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) use($tableColumns) {
                return self::isFunctionalDependencyInTable($functionalDependency, $tableColumns);
            }
        );
        
        return $functionalDependenciesProyection;

    }

    public static function getNotTrivialFunctionalDependenciesProyectionInTable($tableColumns){

        $functionalDependenciesProyection=self::closureOfASetOfFunctionalDependenciesWithoutTrivial($this->functionalDependencies);

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) {
                return !self::isTrivialFunctionalDependency($functionalDependency);
            }
        );

        $functionalDependenciesProyection=array_filter(
            $functionalDependenciesProyection, 
            function($functionalDependency) use($tableColumns) {
                return self::isFunctionalDependencyInTable($functionalDependency, $tableColumns);
            }
        );
        
        return $functionalDependenciesProyection;

    }

    public static function isFunctionalDependencyInTable($functionalDependency,$tableColumns){
       
        $xIsASubsetOfTable=self::isASubset($functionalDependency['x'],$tableColumns);

        $yIsASubsetOfTable=self::isASubset($functionalDependency['y'],$tableColumns);

        return $xIsASubsetOfTable&&$yIsASubsetOfTable;
    }

    public static function closureOfASetOfFunctionalDependenciesWithoutTrivial(array $functionalDependencies): array {
        $closure = &$functionalDependencies;
    
        do {
            $oldClosure = $closure;
            
            //Esto cubre la inferencia transitiva tambien
            $closure=self::pseudotransitiveInference($closure);
            $closure=self::decompositionInference($closure);
            //$closure=self::unionInference($closure);

            $closure=array_filter(
                $closure, 
                function($functionalDependency) {
                    return !self::isTrivialFunctionalDependency($functionalDependency);
                }
            );
    
        } while (!self::areEqual($oldClosure,$closure));
    
        return $closure;
    }

    public static function decompositionInference($functionalDependencies){
        $closure = &$functionalDependencies;

        do {
            $oldClosure = $closure;

            foreach ($functionalDependencies as $dependency) {

                foreach ($dependency['y'] as $y) {

                    $closure = self::union($closure,[[
                        'x'=>$dependency['x'],
                        'y'=>[$y]
                    ]]);
                            
                }
                
            }
        } while (!self::areEqual($oldClosure,$closure));

        return $closure;
    }


    public static function pseudotransitiveInference($functionalDependencies){
        $closure = &$functionalDependencies;

        do {
            $oldClosure = $closure;

            foreach ($functionalDependencies as $exterDependency) {


                foreach ($functionalDependencies as $interDependency) {


                    if(self::isASubset($exterDependency['y'],$interDependency['x'])){
                        
                        $w=self::difference($interDependency['x'],$exterDependency['y']);
                        $wx=self::union($w,$exterDependency['x']);

                        $closure = self::union($closure,[[
                            'x'=>$wx,
                            'y'=>$interDependency['y']
                        ]]);
                            
                    }

                }
                
            }
        } while (!self::areEqual($oldClosure,$closure));

        return $closure;
    }

    public static function isTrivialFunctionalDependency($functionalDependency){
        return self::isASubset($functionalDependency['y'], $functionalDependency['x']);
    }

    public static function closureOfASetOfAttributes(array $atributes, array $functionalDependencies): array {
        $closure = $atributes;
    
        do {
            $oldClosure = $closure;
    
            foreach ($functionalDependencies as $dependency) {
                $x = $dependency['x'];
                $y = $dependency['y'];
    
                if(self::isASubset($x,$closure)){
                    $closure = self::union($closure,$y);
                     
                }
            }
        } while (!self::areEqual($oldClosure,$closure));
    
        return $closure;
    }


    public static function setToString($set, $setLabel = null, $sufix = "\n") {
        $string = $setLabel ? "$setLabel={" : "{";
        $string .= implode(',', $set);
        $string .= "}$sufix";
        return $string;
    }

    public static function universalRelationshipToString($universalRelationship){

        return self::setToString($universalRelationship,"R");
    }

    public static function decompositionsToString($decompositions){

        //print("\n");
        $string="D={\n\n";

        foreach ($decompositions as $decompositionKey => $decomposition) {
            $string.=self::setToString($decomposition,"$decompositionKey");
            $string.="\n";

        }

        $string.="}\n";

        return $string;

    }

    public static function schemeToString($tableName,$attributes){

        return "Para el esquema de relacion\n".
        "{$tableName}(".implode(', ', $attributes) . ')'.
        "\n";

    }

    

    public static function functionalDependenciesToString($functionalDependencies){

        $string="Se tienen las siguientes dependencias funcionales\n";

        //print("\n");
        $string.="F={\n\n";

        foreach ($functionalDependencies as $dependencieKey => $dependencie) {
            $string.=self::setToString($dependencie['x'],null,null);
            $string.="=>";
            $string.=self::setToString($dependencie['y'],null,null);
            $string.="\n\n";
        }

        $string.="}\n";

        return $string;

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
