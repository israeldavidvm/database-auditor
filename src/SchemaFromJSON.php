<?php

namespace Israeldavidvm\DatabaseAuditor;

use Israeldavidvm\DatabaseAuditor\Schema;


class SchemaFromJSON extends DatabaseSchemaGenerator
{

    public $pathJson;

    public function __construct($databaseAuditor,$pathJson) {

        $this->databaseAuditor = $databaseAuditor;

        $this->pathJson=$pathJson;

        if (!file_exists($this->pathJson)) {
            throw new \Exception("El archivo .json en la ruta proporcionada no existe.");
        }   
        
    
    }

    public function generate(){

        $data = file_get_contents($this->pathJson);
        $data = json_decode($data, true);

        $baseSchema=$this->databaseAuditor->baseSchema;

        if(isset($data['universalRelationship'])){
            // echo "universalRelationship";
            $baseSchema->universalRelationship=array_merge(
                $baseSchema->universalRelationship,
                $data['universalRelationship']
            );
        }
        if(isset($data['decompositionsByTable'])){
            // echo "descompositions";
            $baseSchema->decompositionsByTable=array_merge(
                $baseSchema->decompositionsByTable,
                $data['decompositionsByTable']
            );
        }
        if(isset($data['functionalDependencies'])){
            // echo "functionalDependencies";
            $baseSchema->functionalDependencies=array_merge(
                $baseSchema->functionalDependencies,
                $data['functionalDependencies']
            );
        }

        if(isset($data['joinsClusters'])){
            // echo "joinsClusters";
           
            $this->databaseAuditor->joinsClusters = 
                    $baseSchema->generateJoinsSchemas($data['joinsClusters']);

        }
        
    }
}

?>