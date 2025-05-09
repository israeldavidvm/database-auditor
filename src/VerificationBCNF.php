<?php

namespace Israeldavidvm\DatabaseAuditor;

use  Israeldavidvm\DatabaseAuditor\ValidationAlgorithm;
use Israeldavidvm\DatabaseAuditor\Schema;

class VerificationBCNF extends ValidationAlgorithm {

    public function execute(){

        // loadInputFromDatabase($dataBaseconfig, $universalRelationship,$decompositionsByTable);

        $baseSchema=$this->databaseAuditor->baseSchema;

        foreach ($baseSchema->decompositionsByTable as $tableName => $tableAtributes) {
            
            $tableIsInBCNF=true;

            $explain='';
            
            $explain.=Schema::schemeToString($tableName, $tableAtributes);
            
            $functionalDescompositions=
                $baseSchema->getFunctionalDependenciesForBCNFInTable($tableAtributes);

            $explain.=Schema::functionalDependenciesToString($functionalDescompositions);

            foreach ($functionalDescompositions as $functionalDescomposition) {

                $closureOfASetOfAttributes=Schema::closureOfASetOfAttributes($functionalDescomposition['x'],$functionalDescompositions);

                if(!$baseSchema->areEqual($closureOfASetOfAttributes,$tableAtributes)){
                    $tableIsInBCNF=false;
                }

            } 

            if($tableIsInBCNF){

                $this->databaseAuditor->report->addVerification(
                    $tableName,
                    'BCNF',
                    $explain.self::explainResult('BCNF')
                );                

            }else{

                $this->databaseAuditor->report->addVerification(
                    $tableName,
                    'NotBCNF',
                    $explain.self::explainResult('NotBCNF')
                );   

            }

        }   

    }

    public static $posibleResults=[
        'BCNF',
        'NotBCNF'
    ];

    public static function explainResult($result): string
    {

        switch ($result) {
            case 'BCNF':
                return  "Dado que para toda dependencia funcional no trivial".
                " en el conjunto de dependencias funcionales F el antecedente".
                " es super clave la tabla".
                " cumple con la definicion de BCNF".PHP_EOL.PHP_EOL;
            case 'NotBCNF':
                return "Dado que es falso que para toda dependencia funcional".
                " no trivial en el conjunto de dependencias funcionales F el".
                " antecedente es super clave la tabla NO cumple con la definicion".
                " de BCNF".PHP_EOL.PHP_EOL;
        }

        return "No se ha podido determinar el resultado";
       

    }

    public static function isGoodResult($result){

        if($result=='BCNF'){
            return true;
        }

        return false;

    }

    public static function explainAlgorithm(): string
    {
        return "Para el algoritmo de verificacion de la BCNF se utilizara la definicion de BCNF propuesta por".
        " RAMEZ ELMASRI  y SHAMKANT B. NAVATHE\n\n".
        "Ademas se utilizara el conjunto de dependencias funcionales". 
        " no triviales en el que tanto el antecedente como el consecuente". 
        " son subconjuntos del conjunto de atributos de la descomposición,". 
        " en lugar de utilizar el conjunto de dependencias no triviales en la". 
        " proyección del conjunto de dependencias funcionales para esa".
        " descomposición esto debido a que para fines del algoritmo para".
        " verificar la BCNF los conjuntos funcionan de forma equivalente.".
        "\n\n".
        "La demostracion formal de dicha afirmacion se encuentra en el README.md".
        " del paquete database-auditor.\n\n";

    }


}