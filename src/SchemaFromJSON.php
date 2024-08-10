<?php

namespace Israeldavidvm\DatabaseAuditor;

class SchemaFromJSON extends DatabaseSchemaGenerator
{

    public $pathJson;

    public function __construct($databaseAuditor,$pathJson) {

        $this->databaseAuditor = $databaseAuditor;
        $this->pathJson=$pathJson;
    }

    public function generate(){

        $data = file_get_contents($this->pathJson);
        $data = json_decode($data, true);

        if(isset($data['universalRelationship'])){
            echo "universalRelationship";
            $this->databaseAuditor->universalRelationship=array_merge(
                $this->databaseAuditor->universalRelationship,
                $data['universalRelationship']
            );
        }
        if(isset($data['decompositionsByTable'])){
            echo "descompositions";
            $this->databaseAuditor->decompositionsByTable=array_merge(
                $this->databaseAuditor->decompositionsByTable,
                $data['decompositionsByTable']
            );
        }
        if(isset($data['functionalDependencies'])){
            echo "functionalDependencies";
            $this->databaseAuditor->functionalDependencies=array_merge(
                $this->databaseAuditor->functionalDependencies,
                $data['functionalDependencies']
            );
        }
        
    }
}

?>