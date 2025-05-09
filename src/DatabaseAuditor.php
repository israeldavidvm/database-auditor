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

        echo $this->reportToString();
    }


    public function numGoodStateElements(){

        $count=0;

        foreach ($this->report->verificationList as $key => $verification) {
            if($this->isGoodResult($verification['result'])){
                $count+=1;

                // echo "$key tiene un buen resultado".$verification['result'];
            }else {
                // echo "$key tiene un mal resultado ".$verification['result'];

            }

        } 


        return $count;

    }

    public function isGoodResult($result){


        foreach ($this->validationAlgorithms as $algorithm) {
            
            if($algorithm::isGoodResult($result)){
                return true;
            }
        }

        return false;

    }



    public function numScanElements(){

        return count($this->report->verificationList);

    }

    public function reportToString(): string
    {

        $content=$this->reportResumeToString();

        foreach ($this->validationAlgorithms as $validationAlgorithmName => $validationAlgorithm) {
            $content .= $validationAlgorithmName . " : " . $validationAlgorithm->explainAlgorithm() . "\n";
        }

        foreach ($this->report->verificationList as $key => $verification) {
            $content .= $verification['message'] . "\n";
        }

        return $content;

    }

    public function reportResumeToString(){
        $content="Elementos totales(ET): ".$this->numScanElements().PHP_EOL;
        $content.="Elementos en buen estado(EG): ".$this->numGoodStateElements().PHP_EOL;

        $content = "Elementos Revisados : Resultados Algoritmos aplicados" . PHP_EOL;
        $content .= "------------------------------------------------" . PHP_EOL;

        foreach ($this->report->verificationList as $key => $verification) {
        
            if($this->isGoodResult($verification['result'])){
                $content .= $key . " : " . $verification['result'] . PHP_EOL;
            }else{
                $content .= $key . " : " . $verification['result'] .' <------REVISAR'. PHP_EOL;

            }
        
        }

        $content .= PHP_EOL . 'Significado de los resultados:' . PHP_EOL . PHP_EOL;

        foreach ($this->validationAlgorithms as $validationAlgorithmName => $validationAlgorithm) {
            $content .= $validationAlgorithm::explainPossibleResults();
        }

        return $content;
    }

}
