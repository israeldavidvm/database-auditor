<?php

namespace Israeldavidvm\DatabaseAuditor;

use  Israeldavidvm\DatabaseAuditor\ValidationAlgorithm;

class VerificationBCNF extends ValidationAlgorithm {

    public function execute(){

        // loadInputFromDatabase($dataBaseconfig, $universalRelationship,$decompositionsByTable);

        print("Para el siguiente programa se utilizara la definicion de BCNF propuesta por RAMEZ ELMASRI  y SHAMKANT B. NAVATHE\n\n");

        $this->databaseAuditor->printDecompositions();

        foreach ($this->databaseAuditor->decompositionsByTable as $tableName => $tableAtributes) {
            $functionalDescompositions=
                $this->databaseAuditor->getFunctionalDependenciesProyectionByTable($tableName);
        
            $this->databaseAuditor->printFunctionalDependencies($functionalDescompositions,$tableName);

        }   

    }


}