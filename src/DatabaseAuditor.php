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
        $this->report=new Report($this);

    }

    public function generateDatabaseSchema(){ 

        foreach ($this->databaseSchemaGenerators as $key => $databaseSchemaGenerator) {
            $databaseSchemaGenerator->generate();
        } 
        

    }


    public function executeValidationAlgorithm(){

        foreach ($this->validationAlgorithms as $key => $validationAlgorithm) {
            $validationAlgorithm->execute();
        } 

    }

    public function printReport(){

        echo $this->report->reportToString();
    }


}
