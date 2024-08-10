<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;
use Dotenv\Dotenv;


class DatabaseAuditor {

    public $dataBaseConfig;
    public $pdo;
    public $metaInfoEnvFile;

    public $universalRelationship;

    public $decompositionsByTable;
    public $primaryKeysByTable;
    public $foreignKeysByTable;

    public $functionalDependencies;

    public function __construct($metaInfoEnvFile=null) {

        $this->universalRelationship=[];
        $this->decompositionsByTable=[];
        $this->functionalDependencies=[];

        if($metaInfoEnvFile){
            $this->metaInfoEnvFile=$metaInfoEnvFile;
            $this->initDatabaseConnection($metaInfoEnvFile);
        }

    }

    public function initDatabaseConnection($metaInfoEnvFile){

        $this->metaInfoEnvFile=$metaInfoEnvFile;

        $dotenv = Dotenv::createImmutable(
            $this->metaInfoEnvFile['pathEnvFolder'],
            $this->metaInfoEnvFile['name'],
        );
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD']);
        
        // Configuración de la conexión a la base de datos
        $this->dataBaseConfig=[
            'host' => $_ENV['DB_HOST'], // Cambia esto si tu servidor es diferente
            'dbname' => $_ENV['DB_DATABASE'], // Nombre de tu base de datos
            'user' => $_ENV['DB_USERNAME'], // Tu usuario de la base de datos
            'password' => $_ENV['DB_PASSWORD'], // Tu contraseña de la base de datos
        ];

        try {
            // Crear una conexión PDO
            $this->pdo = new \PDO("pgsql:host={$this->dataBaseConfig['host']};dbname={$this->dataBaseConfig['dbname']}", $this->dataBaseConfig['user'], $this->dataBaseConfig['password']);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }


    }

    public function reset() {

        $this->universalRelationship=[];
        $this->decompositionsByTable=[];
        $this->functionalDependencies=[];

    }

    public function generateDatabaseSchema($databaseSchemaGenerators){ 

        foreach ($databaseSchemaGenerators as $key => $databaseSchemaGenerator) {
            $databaseSchemaGenerator->generate();
        } 
        

    }

    public function loadDatabaseSchema($universalRelationship,$decompositionsByTable,$functionalDependencies){

        $this->universalRelationship=$universalRelationship;
        $this->decompositionsByTable=$decompositionsByTable;
        $this->functionalDependencies=$functionalDependencies;

    }


    public function executeValidationAlgorithm($validationAlgorithms){

        foreach ($validationAlgorithms as $key => $validationAlgorithm) {
            $validationAlgorithm->execute();
        } 

    }

    public function getfunctionalDependenciesProyectionInTable($tableColumns){



    }

    public function isFunctionalDependencyInTable($functionalDependency,$tableColumns){
        foreach($functionalDependency['x'] as $x){
            if(!in_array($x,$tableColumns)){
                return false;
            }
        }

        foreach($functionalDependency['y'] as $y){
            if(!in_array($y,$tableColumns)){
                return false;
            }
        }

        return true;
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

        print("\n");
        print("D={\n\n");

        foreach ($decompositions as $decompositionKey => $decomposition) {
            $this->printSet($decomposition,"$decompositionKey");
            print("\n");

        }

        print("}");
        print("\n");

    }

    public function printfunctionalDependencies($functionalDependencies=null){

        if($functionalDependencies==null){
            $functionalDependencies=$this->functionalDependencies;
        }

        print("\n");
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

}
