<?php

namespace Israeldavidvm\DatabaseAuditor;

use  Israeldavidvm\DatabaseAuditor\ValidationAlgorithm;

class VerificationBCNF extends ValidationAlgorithm {

    public function execute(){

        // loadInputFromDatabase($dataBaseconfig, $universalRelationship,$decompositionsByTable);

        print("Para el siguiente programa se utilizara la definicion de BCNF propuesta por RAMEZ ELMASRI  y SHAMKANT B. NAVATHE\n\n");

        print("Para el algoritmo se utilizar el conjunto de dependencias funcionales". 
        " no triviales en el que tanto el antecedente como el consecuente". 
        " son subconjuntos del conjunto de atributos de la descomposición,". 
        " en lugar de utilizar el conjunto de dependencias no triviales en la". 
        " proyección del conjunto de dependencias funcionales para esa".
        " descomposición esto debido a que para fines del algoritmo para".
        " verificar la BCNF los conjuntos funcionan de forma equivalente.".
        "\n\n".
        "La demostracion formal de dicha afirmacion se encuentra en el README.md".
        " del paquete database-auditor.\n\n");

        foreach ($this->databaseAuditor->decompositionsByTable as $tableName => $tableAtributes) {
            
            $tableIsInBCNF=true;

            print("Para el esquema de relacion\n");

            $this->databaseAuditor->printScheme($tableName, $tableAtributes);
            
            $functionalDescompositions=
                $this->databaseAuditor->getFunctionalDependenciesForBCNFInTable($tableAtributes);

            print("Se tienen las siguientes dependencias funcionales\n");

            $this->databaseAuditor->printFunctionalDependencies($functionalDescompositions);

            foreach ($functionalDescompositions as $functionalDescomposition) {

                $closureOfASetOfAttributes=$this->databaseAuditor->closureOfASetOfAttributes($functionalDescomposition['x'],$functionalDescompositions);

                if(!$this->databaseAuditor->areEqual($closureOfASetOfAttributes,$tableAtributes)){
                    $tableIsInBCNF=false;
                }

            } 

            if($tableIsInBCNF){
                print("Dado que para toda dependencia funcional no trivial en el conjunto de dependencias funcionales F el antecedente es super clave la tabla ".$tableName." cumple con la definicion de BCNF\n");
            }else{
                print("Dado que es falso que para toda dependencia funcional no trivial en el conjunto de dependencias funcionales F el antecedente es super clave la tabla ".$tableName." NO cumple con la definicion de BCNF\n");
            }

        }   

    }


}