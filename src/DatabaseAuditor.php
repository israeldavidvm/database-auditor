<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;

use Israeldavidvm\DatabaseAuditor\Report;
use Israeldavidvm\DatabaseAuditor\Schema;

class DatabaseAuditor {


    public $baseSchema;
    public $joinsClusters;

    public $report;

    
    public $databaseSchemaGenerators;
    public $validationAlgorithms;

    public function __construct() {

        $this->reset();

        $this->databaseSchemaGenerators=[];

    }

    public function reset() {

        $this->baseSchema = new Schema();
        $this->joinsClusters=[];
        $this->report=new Report();

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


    public function executeValidationAlgorithm(){

        foreach ($this->validationAlgorithms as $key => $validationAlgorithm) {
            $validationAlgorithm->execute();
        } 

    }

    public function printReport(){

        echo "Elemento Revisado : Resultado Algoritmo aplicados".PHP_EOL;
        echo "------------------------------------------------".PHP_EOL;

        foreach ($this->report->verificationList as $key => $verification) {
            echo $key." : ".$verification['result'].PHP_EOL;
        }

        echo PHP_EOL.'Significado de los resultados:'.PHP_EOL.PHP_EOL;

        foreach ($this->validationAlgorithms as $validationAlgorithmName => $validationAlgorithm) {
            echo $validationAlgorithm::explainPossibleResults();
        }

        foreach ($this->validationAlgorithms as $validationAlgorithmName => $validationAlgorithm) {
            echo $validationAlgorithmName." : ".$validationAlgorithm->explainAlgorithm()."\n";
        }

        foreach ($this->report->verificationList as $key => $verification) {
            echo $verification['message']."\n";
        } 
    }

}
