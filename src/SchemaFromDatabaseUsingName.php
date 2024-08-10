<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;

class SchemaFromDatabaseUsingName extends DatabaseSchemaGenerator
{
    // public function generate(){

    //     // Obtener las claves foráneas
    //     $foreignKeysQuery = $pdo->prepare("
    //         SELECT
    //             tc.constraint_name, kcu.column_name
    //         FROM 
    //             information_schema.table_constraints tc
    //         JOIN
    //             information_schema.key_column_usage kcu
    //                 ON tc.constraint_name = kcu.constraint_name
    //         WHERE 
    //             tc.constraint_type = 'FOREIGN KEY' 
    //             AND tc.table_name = :table
    //     ");
        
    //     $foreignKeysQuery->execute(['table' => $table]);
    //     $foreignKeys = $foreignKeysQuery->fetchAll(\PDO::FETCH_ASSOC);

    // }

    public $metaInfoClusterTables;
    public $dataBaseConfig;

    /**
     * Constructor de la clase
     *
     * Establece la conexión a la base de datos y carga la configuración necesaria.
     *
     * @param object $databaseAuditor que apunta a una referencia de 
     * una instancia de clase DatabaseAuditor
     * @param array $metaInfoEnvFile Arreglo asociativo que 
     * contiene la ruta (en la clave pathEnvFolder) y 
     * el nombre del archivo de configuración del entorno(en la clave name).
     * @param array $metaInfoClustersTables arreglo asociativo que 
     * contiene el modo de cluster(mode) y tablas de clústeres (tipo desconocido).
     */
    public function __construct($databaseAuditor,$metaInfoEnvFile,$metaInfoClusterTables) {
        $this->databaseAuditor = $databaseAuditor;
        
        $this->databaseAuditor->initDatabaseConnection($metaInfoEnvFile);

        $this->metaInfoClusterTables=$metaInfoClusterTables;
    }
    

    public function generate(){

        try {
        
            $tables=$this->getTables($this->metaInfoClusterTables);
        
            echo "Tablas en la base de datos:\n";
            foreach ($tables as $tableKey => $table) {
                echo "- $table\n";

                $this->databaseAuditor->decompositionsByTable[$table]=$this->getColumnsByTable($table,true);
                echo "  Columnas:\n";
                foreach ($this->databaseAuditor->decompositionsByTable[$table] as $column) {

                    if(!in_array($column, $this->databaseAuditor->universalRelationship)){
                        $this->databaseAuditor->universalRelationship[]=$column;
                    }

                    echo "  - $column\n";
                }
        
                $this->databaseAuditor->primaryKeysByTable[$table]=$this->getPKByTable($table,true);
                
                echo "  Claves primarias:\n";
                foreach ($this->databaseAuditor->primaryKeysByTable[$table] as $primaryKey) {
                    echo "  - $primaryKey\n";
                    // echo "  - {$primaryKey['column_name']}\n";
                    // $this->decompositionsByTable[count($this->decompositions)-1];
                }

                $this->databaseAuditor->foreignKeysByTable[$table]=$this->getFKsByTable($table);
                echo "  Claves foráneas:\n";
                foreach ($this->databaseAuditor->foreignKeysByTable[$table] as $foreignKey) {
                    echo "  - $foreignKey\n";
                    //var_dump($foreignKey);
                    //echo "  - {$foreignKey['column_name']} -> {$foreignKey['foreign_table_name']}.{$foreignKey['foreign_column_name']}\n";
                }
        
                $trivialFunctionalDependenciesByTable=$this->getTrivialFunctionalDependenciesByTable($table);

                foreach ($trivialFunctionalDependenciesByTable as $functionalDependency) {
                    $this->databaseAuditor->functionalDependencies[]=$functionalDependency;
                }

                $this->generateJoinsClusters();

               
        
            }
        } catch (\PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        
    }

    public function regenerateFromListTableNames($listTableNames){
        $this->metaInfoClusterTables=$this
            ->listTableNamesToMetaInfoClusterTables($listTableNames);

        $this->generate();
    }
        

    public function getTables($metaInfoClusterTables){

        if(
            isset($metaInfoClusterTables['mode']) 
            && 
            isset($metaInfoClusterTables['tables'])
        ){
            if($metaInfoClusterTables['mode']=="include"){

                $tables=$metaInfoClusterTables['tables'];

            }elseif($metaInfoClusterTables['mode']=="exclude"){
                
                $tablesQuery = $this->databaseAuditor->pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                $tables = $tablesQuery->fetchAll(\PDO::FETCH_COLUMN);
            
                $tables = array_filter($tables, function($value) use($metaInfoClusterTables) {
                    return !in_array($value, $metaInfoClusterTables['tables']);
                });
            
            }

        }

        return $tables;
    }

    public function getColumnsByTable($tableName,$fullyQualifiedForm=false){
        
        $columnsQuery = $this->databaseAuditor->pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = :table");
        $columnsQuery->execute(['table' => $tableName]);
        $columns = $columnsQuery->fetchAll(\PDO::FETCH_COLUMN);


        if($fullyQualifiedForm){

            $fks=$this->getFKsByTable($tableName);

            $columns=array_map(
                function($column) use($tableName,$fks){
                    if(in_array($column, $fks)){
                        return $column;
                    }else{
                        return "{$this->pluralToSingular($tableName)}_$column";
                    }
                }
            , $columns);
        }

        return $columns;
    }
   
    public function getFKsByTable($tableName){
        $query = $this->databaseAuditor->pdo->prepare("
                    SELECT kcu.column_name
                    FROM 
                        information_schema.key_column_usage kcu
                    WHERE 
                        kcu.column_name ~ '^[a-zA-Z0-9ñ]+_?[a-zA-Z0-9ñ]*_id$'
                        AND
                        kcu.table_name = :table
                ");
        $query->execute(['table' => $tableName]);

        $foreignKeys = $query->fetchAll(\PDO::FETCH_ASSOC);
        $foreignKeys=array_map(function($foreignKey){
            return $foreignKey['column_name'];
        }, $foreignKeys);

        return $foreignKeys;
    }

    public function getPKByTable($tableName,$fullyQualifiedForm=false){
        $query = $this->databaseAuditor->pdo->prepare("
            SELECT kcu.column_name
            FROM 
                information_schema.key_column_usage kcu
            WHERE 
                kcu.column_name ~ '^id$'
                AND
                kcu.table_name = :table
        ");
        $query->execute(['table' => $tableName]);

        $primaryKey = $query->fetchAll(\PDO::FETCH_ASSOC);
        $primaryKey=array_map(function($pk){
            return $pk['column_name'];
        }, $primaryKey);

        if($fullyQualifiedForm){
            $primaryKey=array_map(
            fn($pk)=>"{$this->pluralToSingular($tableName)}_$pk"
            , $primaryKey);
        }

        return $primaryKey;

    }

    public function getTrivialFunctionalDependenciesByTable($tableName){
        $primaryKeys=$this->getPKByTable($tableName,true);
    
        $trivialFunctionalDependencies=[];

        foreach ($primaryKeys as $primaryKey) {
            $trivialFunctionalDependencies[]=[
                'x'=>[$primaryKey],
                'y'=>$this->databaseAuditor->decompositionsByTable[$tableName]
            ];
        }

        return $trivialFunctionalDependencies;
    }

    public function pluralToSingular($word) {
        // Reglas básicas para convertir plural a singular
        if (substr($word, -3) === 'ies') {
            
            // Palabras que terminan en 'ies' se convierten a 'y'
            return substr($word, 0, -3) . 'y';

        } elseif (substr($word, -1) === 's') {

            // Palabras que terminan en 's' se convierten a 's'
            return substr($word, 0, -1);

        } else {

            // Si no se aplica ninguna regla, devolver el plural original
            return $word;
        
        }
    }

    public function singularToPlural($word) {
       
        if (preg_match('/(y)$/', $word) && !preg_match('/(a|e|i|o|u)y$/', $word)) {
            return preg_replace('/y$/', 'ies', $word); // Cambiar 'y' por 'ies'
        } else {
            return $word . 's'; // Agregar 's' por defecto
        }
    }

    public function generateJoinsClusters($initialMetaInfoClusterTables=null){

        if ($initialMetaInfoClusterTables==null) {
            $initialMetaInfoClusterTables=$this->metaInfoClusterTables;
        }

        $tables=$this->getTables(
            $initialMetaInfoClusterTables
        );

        $joinsClusters=[];

        foreach ($tables as $table) {

            $tablesManyToMany=$this->getTablesManyToManyRelationship($table);

            if($tablesManyToMany){

                if($tablesManyToMany[0]==$tablesManyToMany[1]){
                    continue;
                }

                $joinsClusters[]=[];

                $joinsClusters[count($joinsClusters)-1][]=$table;

                foreach($tablesManyToMany as $tableManyToMany){
                    $joinsClusters[count($joinsClusters)-1][]=$tableManyToMany;
                }

            }

            $fks=$this->getFKsByTable($table);

            //evita que se haga el analisis para las fk que ya se evaluaron en la relacion 
            //many to many

            $fksManyToMany=array_map(fn ($table)=>"{$this->pluralToSingular($table)}_id", $tablesManyToMany);
            
            $fks=array_filter($fks, fn ($fk) => !in_array($fk,$fksManyToMany));

            if($fks!=null){

                $joinsClusters[]=[];

                $joinsClusters[count($joinsClusters)-1][]=$table;

                foreach($fks as $fk){

                    $referencedTable=$this->singularToPlural(
                        preg_replace('/_id$/', '', $fk)
                    );

                    $joinsClusters[count($joinsClusters)-1][]=$referencedTable;
                }
            }
            

        }

    $this->databaseAuditor->joinsClusters=$joinsClusters;

    return $joinsClusters;

    }

    public function getTablesManyToManyRelationship($table) {
        // Definir la expresión regular
        $pattern = '/^([a-zA-Z0-9ñ]+)_([a-zA-Z0-9ñ]+)$/';
        // Verificar si la tabla coincide con la expresión regular
        if (preg_match($pattern, $table, $matches)) {
            array_shift($matches);
            $matches=array_map(fn ($table)=>$this->singularToPlural($table), $matches);
            return $matches; // Retorna los grupos de captura
        }

        return []; // Retorna null si no coincide
    }

    public function listTableNamesToMetaInfoClusterTables($listTableNames){
        return [
                'mode'=>'include',
                'tables'=>$listTableNames
        ];
    }
}

?>