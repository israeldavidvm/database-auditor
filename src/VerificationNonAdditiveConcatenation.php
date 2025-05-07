<?php

namespace Israeldavidvm\DatabaseAuditor;

use  Israeldavidvm\DatabaseAuditor\ValidationAlgorithm;

class VerificationNonAdditiveConcatenation extends ValidationAlgorithm {

    public function execute(){
        
        // print("Creando agrupaciones de tablas para aplicarles a cada una el Algoritmo 11.1 de Verificación  de la propiedad de concatenación no aditiva propuesto por RAMEZ ELMASRI  y SHAMKANT B. NAVATHE\n\n");

        foreach($this->databaseAuditor->joinsClusters as $cluster){

            $this->verificationNonAdditiveConcatenation($cluster);

        }
    }


    public function verificationNonAdditiveConcatenation($schema){

        // loadInputFromDatabase($dataBaseconfig, $universalRelationship,$decompositionsByTableByTable);

        $explain='';

        $explain.=Schema::universalRelationshipToString($schema->universalRelationship);
        $explain.=Schema::decompositionsToString($schema->decompositionsByTable);
        $explain.=Schema::functionalDependenciesToString($schema->functionalDependencies);

        // Inicializar la matriz S
        $matrix = [];

        $explain.="\nCree una matriz inicial S con una fila i por cada relación Ri en D, y una columna j por cada atributo Aj en R.\n";

        // Asignar b_ij a la matriz S

        for ($i=0,$maxRows=count($schema->decompositionsByTable); $i < $maxRows; $i++) { 

            $row = [];
            foreach ($schema->universalRelationship as $j => $attribute) {
                // Asignar b_ij como un símbolo distinto
                $row[] = "b_{$i}_{$j}";
            }
            $matrix[$i] = $row;
        }

        $explain.="\nAsigne S(i, j):= bij en todas las entradas de la matriz. (∗ cada bij es un símbolo distinto asociado a índices (i, j) ∗)\n";
        
        $explain.=Self::tableToSTring($matrix);

        // print_r($matrix);
        // print_r($universalRelationship);
        // print_r($decompositionsByTable);

        //Asignar a_j si la relación incluye el atributo
        $i=0;
        foreach ($schema->decompositionsByTable as $key => $decomposition) {
            foreach ($decomposition as $atribute) {
                    $j=array_search($atribute,$schema->universalRelationship);
                    if ($j!==false) {
                        $matrix[$i][$j] = "a_{$j}"; // Asignar a_j
                    }
            }
            $i++;
        }

        $explain.="Por cada fila i que representa un esquema de relación Ri 
    {por cada columna j que representa un atributo Aj
        {si la (relación Ri incluye un atributo Aj) entonces asignar S(i, j):⫽ aj;};};
            (∗ cada aj es un símbolo distinto asociado a un índice (j) ∗)\n";

        $explain.=Self::tableToSTring($matrix);
        // print_r($matrix);

        $explain.="Repetir el siguiente bucle hasta que una ejecución completa del mismo no genere cambios en S{por cada dependencia funcional X → Y en F{ para todas las filas de S que tengan los mismos símbolos en las columnas correspondientes a  los atributos de X{ hacer que los símbolos de cada columna que se corresponden con un atributo de  Y sean los mismos en todas esas filas siguiendo este patrón: si cualquiera  de las filas tiene un símbolo a para la columna, hacer que el resto de filas  tengan el mismo símbolo a en la columna. Si no existe un símbolo a para el  atributo en ninguna de las filas, elegir uno de los símbolos b para el atributo  que aparezcan en una de las filas y ajustar el resto de filas a ese valor } } }\n";

        // Bucle hasta que no haya cambios
        do {
            $oldMatrix = $matrix;

            $explain.=Self::tableToSTring($matrix);

            foreach ($schema->functionalDependencies as $i => $dependency) {

                //echo "Dependencia $i \n";

                $X = $dependency['x'];
                $Y = $dependency['y'];

                $rowsWithAntecedentIndexes=$this->searchRowsWithAntecedentIndexes(
                    $X, 
                    $schema->decompositionsByTable
                );
                $columnsIndexes = array_keys(array_intersect(
                    $schema->universalRelationship, 
                    $Y)
                );

                // print_r($rowsWithAntecedentIndexes);
                // print_r($columnsIndexes);


                // Actualizar símbolos en las columnas de Y
                foreach ($columnsIndexes as $columnIndex) {
                    $symbol=$this->chooseSymbol(
                        $matrix, 
                        $rowsWithAntecedentIndexes,
                        $columnIndex
                    );

                    //print("$symbol\n");

                    $this->changeSymbolColumn($matrix,
                        $rowsWithAntecedentIndexes,
                        $columnIndex,
                        $symbol
                    );
                }

            }

            $explain.=Self::tableToSTring($matrix);


        } while ($this->hasChanges($oldMatrix, $matrix));


        if($this->hasThePropertyOfNonAdditiveConcatenation($matrix)){

            $this->databaseAuditor->report->addVerification(
                $schema->getGroupName(),
                'NAC',
                $explain.self::explainResult('NAC')
            );    

        }else {
            
            $this->databaseAuditor->report->addVerification(
                $schema->getGroupName(),
                'NotNAC',
                $explain.self::explainResult('NotNAC')
            );  
        
        }

        // Mostrar la matriz final"La descomposición D={R1, R2, . . . , Rm} de R No tiene la propiedad de concatenación sin pérdida (no aditiva) respecto al conjunto de dependencias F en R dado que  no existe una fila que este compuesta enteramente por símbolos a\n");
        
        // print_r($matrix);

        //print_r(searchRowsWithAntecedentIndexes(['x'], [['x'],['y'],['x']]));
    }

    public static $posibleResults=[
        'NAC',
        'NotNAC'
    ];

    public static function explainResult($result): string
    {

        switch ($result) {
            case 'NAC':
                return "La descomposición D={R1, R2, . . . , Rm} de R Si tiene la propiedad".
                " de concatenación sin pérdida (no aditiva) respecto al conjunto de dependencias F".
                " en R dado que una fila  está compuesta enteramente por símbolos a".PHP_EOL.PHP_EOL
                ;
            case 'NotNAC':
                return "La descomposición D={R1, R2, . . . , Rm} de R No tiene la propiedad".
                " de concatenación sin pérdida (no aditiva) respecto al conjunto de dependencias F".
                " en R dado que  no existe una fila que este compuesta enteramente por símbolos a".PHP_EOL.PHP_EOL;
        }

        return "No se ha podido determinar el resultado";
       

    }

    public static function explainAlgorithm(): string
    {
        return "El Algoritmo utilizado para la".
        " Verificación  de la propiedad de concatenación no aditiva sera el propuesto".
        " por RAMEZ ELMASRI  y SHAMKANT B. NAVATHE\n\n";
    }

    function searchRowsWithAntecedentIndexes($antecedent, $rows){
        
        $rowsWithAntecedent=[];

        $i=0;
        foreach ($rows as $key => $row) {

            if($this->antecedentIsPresentInTheRow($antecedent,$row)){
                $rowsWithAntecedent[]=$i;
            }

            $i++;
        }

        return $rowsWithAntecedent;

    }

    function antecedentIsPresentInTheRow($antecedent,$row){
        foreach ($antecedent as $atribute) {
        if(!in_array($atribute,$row)){
                return false;
        }
        }
        return true;
    }

    function chooseSymbol($matrix,$rowsIndexes,$columnIndex){

        $symbol=null;

        foreach ($rowsIndexes as $key => $rowIndex) {
            $symbol=$matrix[$rowIndex][$columnIndex];
            if($symbol=="a_$columnIndex" || $key==array_key_last($rowsIndexes)){
                return $symbol;  
            }
        }

    }

    function changeSymbolColumn(&$matrix,$rowsIndexes,$columnIndex, $symbol){

        //print_r($matrix);

        foreach ($rowsIndexes as $key => $rowIndex) {
            $matrix[$rowIndex][$columnIndex]=$symbol;
        }

        //print_r($matrix);
    }

    // Función para verificar si hay cambios
    function hasChanges($oldMatrix, $newMatrix) {
        foreach ($oldMatrix as $rowIndex => $row) {
            foreach ($row as $columnIndex => $column) {
                if ($newMatrix[$rowIndex][$columnIndex] !== $column) {
                    return true;
                }
            }
        } 

        return false;
    }


    function rowIsOnlyMadeUpOfA($array){

        foreach ($array as $key => $element) {
            if(!str_contains($element, 'a')){
                return false;
            }
        }    

        return true;
    }

    function hasThePropertyOfNonAdditiveConcatenation($matrix){

        // Verificar la propiedad de concatenación no aditiva
        foreach ($matrix as $rowIndex => $row) {
            if ($this->rowIsOnlyMadeUpOfA($row)) {
            return true;
            } 
        }

        return false;

    }

    public static function tableToString($matrix): string {
        $columnSize = 0;
        $string = "\n";
    
        foreach ($matrix as $row) {
            foreach ($row as $column) {
                if (mb_strlen($column) > $columnSize) {
                    $columnSize = mb_strlen($column);
                }
            }
        }
    
        foreach ($matrix as $row) {
            $string .= self::rowToString($row, $columnSize) . "\n";
        }
    
        $string .= "\n";
        return $string;
    }

    public static function rowToString($set, $columnSize): string {
        $str = "|";
        foreach ($set as $value) {
            $value = str_pad($value, $columnSize, " ", STR_PAD_BOTH);
            $str .= $value . "|";
        }
        return $str;
    }


}