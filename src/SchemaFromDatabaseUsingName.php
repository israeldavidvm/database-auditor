<?php

namespace Israeldavidvm\DatabaseAuditor;

use Dom\Element;
use Exception;
use Dotenv\Dotenv;

use Israeldavidvm\DatabaseAuditor\Schema;
use Israeldavidvm\DatabaseAuditor\DatabaseSchemaGenerator;
use PDO;

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
    public $pdo;
    public $pathEnvFile;
    public $pathJson;

    /**
     * Constructor de la clase
     *
     * Establece la conexión a la base de datos y carga la configuración necesaria.
     *
     * @param object $databaseAuditor que apunta a una referencia de 
     * una instancia de clase DatabaseAuditor
     * @param array $pathEnvFile ruta al archivo .env
     */
    public function __construct($databaseAuditor,$pathEnvFile) {

        // echo "Se ha creado una instancia de ".get_called_class()."\n";
        // echo "Configuracion de la base de datos:\n";
        // echo json_encode($metaInfoEnvFile)."\n";
        // echo json_encode($metaInfoClusterTables)."\n";

        $this->databaseAuditor = $databaseAuditor;

        if($pathEnvFile){
            $this->pathEnvFile=$pathEnvFile;
            $this->initDatabaseConfig($pathEnvFile);
            $this->initDatabaseConnection();
        }
    
    }

    public function initDatabaseConfig($pathEnvFile){


        // echo "Israel   Genesissssss".$pathEnvFile;

        if(!file_exists($pathEnvFile)){
            throw new Exception("El archivo .env no existe en la ruta especificada: $pathEnvFile");
        }

/*         echo $pathEnvFile;
 */
        $dotenv = Dotenv::createMutable(
            dirname($pathEnvFile),
            basename($pathEnvFile),
        );

        $dotenv->load();
        
        $dotenv->required([
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'DB_PORT',
            'DATA_AUDITOR_FILTER',
            'DATA_AUDITOR_ELEMENTS',
            'DATA_AUDITOR_PATH_FUNCTIONAL_DEPENDENCIES_JSON_FILE'

        ]);

        // echo  json_encode($_ENV);

        // echo $_ENV['DATA_AUDITOR_FILTER'];
        // echo $_ENV['DATA_AUDITOR_ELEMENTS']; 

        // Configuración de la conexión a la base de datos
        $this->dataBaseConfig=[
            'host' => $_ENV['DB_HOST'], // Cambia esto si tu servidor es diferente
            'dbname' => $_ENV['DB_DATABASE'], // Nombre de tu base de datos
            'user' => $_ENV['DB_USERNAME'], // Tu usuario de la base de datos
            'password' => $_ENV['DB_PASSWORD'], // Tu contraseña de la base de datos
            'port' => $_ENV['DB_PORT'] // Puerto de la base de datos
        ];

        if(
            !isset($_ENV['DATA_AUDITOR_FILTER']) 
            || 
            !isset($_ENV['DATA_AUDITOR_ELEMENTS'])
        ){
            throw new Exception("Es necesario que establezcas las variables".
            "DATA_AUDITOR_ELEMENTS DATA_AUDITOR_FILTER");
        }

        if(
            $_ENV['DATA_AUDITOR_FILTER']!="include" 
            && 
            $_ENV['DATA_AUDITOR_FILTER']!="exclude"
        ){
            throw new Exception("Los valores posibles para 'DATA_AUDITOR_FILTER' son include o exclude");
        }

        $this->pathJson=$_ENV['DATA_AUDITOR_PATH_FUNCTIONAL_DEPENDENCIES_JSON_FILE'];

        $this->metaInfoClusterTables=[
            'mode'=>$_ENV['DATA_AUDITOR_FILTER'],
            'tables'=>explode(',',$_ENV['DATA_AUDITOR_ELEMENTS'])
        ];


    }

    public function initDatabaseConnection(){

        try {
            // Crear una conexión PDO
            $this->pdo = new \PDO(
                "pgsql:host={$this->dataBaseConfig['host']};port={$this->dataBaseConfig['port']};dbname={$this->dataBaseConfig['dbname']}", // Se añadió ;port=...
                $this->dataBaseConfig['user'],
                $this->dataBaseConfig['password']
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Error de conexión: para la configuracion ".json_encode($this->dataBaseConfig)." ". $e->getMessage();
        }


    }
    

    public function generate(){

        try {

            $schema=$this->databaseAuditor->baseSchema;
        
            $tables=$this->getTables();
        
            // echo "Tablas en la base de datos:\n";
            foreach ($tables as $tableKey => $table) {
                // echo "- $table\n";

                $schema->decompositionsByTable[$table]=$this->getColumnsByTable($table,true);
     
                // echo "  Columnas:\n";
                foreach ($schema->decompositionsByTable[$table] as $column) {

                    if(!in_array($column, $schema->universalRelationship)){
                        $schema->universalRelationship[]=$column;
                    }

                    // echo "  - $column\n";
                }
        
                $schema->primaryKeysByTable[$table]=$this->getPKByTable($table,true);
                

                // echo "  Claves primarias:\n";
                // echo 'Genesis'.json_encode($schema->primaryKeysByTable[$table]);

                foreach ($schema->primaryKeysByTable[$table] as $primaryKey) {
                    // echo "  - $primaryKey\n";
                    // echo "  - {$primaryKey['column_name']}\n";
                    // // $this->decompositionsByTable[count($this->decompositions)-1];
                }

                $schema->foreignKeysByTable[$table]=$this->getFKsByTable($table);

                
                // echo "  Claves foráneas:\n";
                // echo 'Genesis'.json_encode($schema->primaryKeysByTable[$table]);

                foreach ($schema->foreignKeysByTable[$table] as $foreignKey) {

                    // echo "  - $foreignKey\n";
                    // echo "  - {$foreignKey['column_name']} -> {$foreignKey['foreign_table_name']}.{$foreignKey['foreign_column_name']}\n";
                
                }
        
                $usualFunctionalDependenciesByTable=$this->getUsualFunctionalDependenciesByTable($table);

                foreach ($usualFunctionalDependenciesByTable as $functionalDependency) {
                    $schema->functionalDependencies[]=$functionalDependency;
                }
               
            }

            $this->loadFunctionDepedenciesFromJsonFile();

            // echo "Esquema de la base de datos:\n";
                // echo json_encode($this->generateJoinsClusters())."\n";
               
            $this->databaseAuditor->joinsClusters = 
                $this->databaseAuditor->baseSchema
                ->generateJoinsSchemas($this->generateJoinsClusters());

        } catch (\PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        
    }

    public function loadFunctionDepedenciesFromJsonFile(){

        $data = file_get_contents($this->pathJson);
        $data = json_decode($data, true);

        if(isset($data['functionalDependencies'])){
            // echo "functionalDependencies";
            $this->databaseAuditor->baseSchema->functionalDependencies=array_merge(
                $this->databaseAuditor->baseSchema->functionalDependencies,
                $data['functionalDependencies']
            );
        }

    }
        

    public function getTables(){

        // echo json_encode($this->metaInfoClusterTables);

        if($this->metaInfoClusterTables['mode']=="include"){

            $tablesQuery = $this->pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
            $tables = $tablesQuery->fetchAll(\PDO::FETCH_COLUMN);
        
            $tables = array_filter($tables, function($value) {
                return in_array($value, $this->metaInfoClusterTables['tables']);
            });        
            
        }elseif($this->metaInfoClusterTables['mode']=="exclude"){
            
            $tablesQuery = $this->pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
            $tables = $tablesQuery->fetchAll(\PDO::FETCH_COLUMN);
        
            $tables = array_filter($tables, function($value) {
                return !in_array($value, $this->metaInfoClusterTables['tables']);
            });
        
        }

        return $tables;
    }

    public function getColumnsByTable($tableName,$fullyQualifiedForm=false){
        
        $columnsQuery = $this->pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = :table");
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
        $query = $this->pdo->prepare("
        SELECT *
            FROM 
                information_schema.columns cl
            WHERE 
                cl.table_name = :table
                ");
        $query->execute(['table' => $tableName]);

        $foreignKeys = $query->fetchAll(\PDO::FETCH_ASSOC);

        $foreignKeys=array_map(function($foreignKey){
            return $foreignKey['column_name'];
        }, $foreignKeys);

        $foreignKeys=array_filter($foreignKeys,function($foreignKey){
            return self::isFk(
                $foreignKey,
                $this->getTables()
            );
        });

        return $foreignKeys;
    }

    public static function isFk($expresion,$tables){

        return self::getReferencedTableFromFk($expresion,$tables)==null?false:true;

    }

    public static function splitByLastUnderscore(string $word): array
    {
        $lastPosition = strrpos($word, "_");
        if ($lastPosition === false) {
            throw new Exception("Underscore character not found in the string.");
        }
        return [substr($word, 0, $lastPosition), substr($word, $lastPosition + 1)];
    }

    public static function splitByFirstUnderscore(string $word): array
    {
        $firstPosition = strpos($word, "_");
        if ($firstPosition === false) {
            throw new Exception("Underscore character not found in the string.");
        }
        return [substr($word, 0, $firstPosition), substr($word, $firstPosition + 1)];
    }

    public function getPKByTable($tableName,$fullyQualifiedForm=false){
        $query = $this->pdo->prepare("
            SELECT *
            FROM 
                information_schema.columns cl
            WHERE 
                cl.column_name ~ '^id$'
                AND
                cl.table_name = :table
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

    public function getUsualFunctionalDependenciesByTable($tableName){
        $primaryKeys=$this->getPKByTable($tableName,true);
    
        $usualFunctionalDependencies=[];

        // echo json_encode($this->databaseAuditor->decompositionsByTable)."Genesisiiiiii lo mejor esta aqui calidad y mucho mas";

        foreach ($primaryKeys as $primaryKey) {
            $usualFunctionalDependencies[]=[
                'x'=>[$primaryKey],
                'y'=>Schema::difference(
                    $this->databaseAuditor->baseSchema->decompositionsByTable[$tableName],
                    [$primaryKey]
                )
            ];
        }

        return $usualFunctionalDependencies;
    }

    public function generateJoinsClusters(){

        $tables=$this->getTables();

        $joinsClusters=[];

        foreach ($tables as $table) {

            $fks=$this->getFKsByTable($table);

            $referencedTables=$this->getReferencedTablesByTable($table);

            // echo 'ISRAEL'.
            // json_encode($referencedTables).
            // 'fIN ISRAEL';

            if(count($fks)>0){

                $joinsClusters[]=[];

                $joinsClusters[count($joinsClusters)-1][]=$table;

                foreach($fks as $fk){
                    if(self::fkGenerateCollision(
                        $fk,
                        $table,
                        $referencedTables,
                        $tables
                    )){

                        $joinsClusters[count($joinsClusters)-1][]=
                            self::getReferencedTableFromFk($fk,$tables,true);

                    }else {
                        $joinsClusters[count($joinsClusters)-1][]=
                            self::getReferencedTableFromFk($fk,$tables);
                    }

                }

              
            }

            // echo 'DAVID'.
            // json_encode($joinsClusters[count($joinsClusters)-1]).
            // 'fIN DAVID';

            
        }
    
    return $joinsClusters;

    }

    public static function fkGenerateCollision(
        $fk,
        $table,
        $referencedTables,
        $tables
    ){

        $referencedTable=self::getReferencedTableFromFk($fk,$tables);

        return self::referencedGenerateCollision(
            $referencedTable,
            $table,
            $referencedTables,
        );
    }

    public static function referencedGenerateCollision(
        $referencedTable,
        $table,
        $referencedTables,
    ){

        #1. Si e pertenece a Entidades y existe un fk pertenece
        # foreignKeysDeEntidad(e)  
        #tal que entidadReferenciadaPorFK(fk)==e        
        if($referencedTable==$table){
            return true;
        }
        #2. Si n pertenece a EntidadesNary, existen fk1 y fk2 que pertenecen a
        # foreignKeysDeEntidad(e) tal que 
        #entidadReferenciadaPorFK(fk1)==entidadReferenciadaPorFK(fk2)
        if(
            in_array($referencedTable,
            self::getRepeatedElements($referencedTables)
            )
        ){
            return true;
        }

        return false;
    }

    public static function listTableNamesToMetaInfoClusterTables($listTableNames){
        return [
                'mode'=>'include',
                'tables'=>$listTableNames
        ];
    }

    public function getReferencedTablesByTable($table){

        $referencedTables=[];

        $fks=$this->getFKsByTable($table);

        foreach($fks as $fk){

            $referencedTable=self::getReferencedTableFromFk(
                $fk,
                $this->getTables()
            );

            if($referencedTable){

                $referencedTables[]=$referencedTable;

            }
        
        }
        
        return $referencedTables;            
        
    }

    public static function getReferencedTableFromFk($expresion,$tables,$withRole=false){

        $role=null;

        $pattern = '/^[a-zA-Z0-9ñ]+(?:_[a-zA-Z0-9ñ]+)*_id$/';
        if(!(bool) preg_match($pattern, $expresion)){
            return null;
        }

        $foundFk=false;

        while(!$foundFk){

            try {

                if(self::splitByLastUnderscore($expresion)[1]!=='id'){
                    $role=self::splitByLastUnderscore($expresion)[1];
                }

                $expresion=self::splitByLastUnderscore($expresion)[0];

                if(in_array(
                    self::singularToPlural($expresion),
                    $tables
                )){

                    #expresion apunta auna relacion
                    $foundFk=true;
                    $expresion=self::singularToPlural($expresion);

                } else if(in_array(

                    #expresion apunta a una relacion naria
                    $expresion,
                    $tables

                )){
                    $foundFk=true;

                }
            
            } catch (Exception $e) {
                break;
            }

        }

        if($withRole===true){

            if($role===null){
                $role='referenced';
            }

            $expresion=self::pluralToSingular($expresion);

            $expresion.="_$role";

            $expresion=self::singularToPlural($expresion);
        }

        if($foundFk){
            return $expresion;
        }else{
            return null;
        }
        
    }

    public static function getTablesManyToManyRelationship($tableName) {
        
        if (self::isTablesManyToManyRelationship($tableName)) {
            $matches=explode("_",$tableName);
            $matches=array_map(fn ($table)=>self::singularToPlural($table), $matches);
            return $matches; 
        }

        return []; // Retorna null si no coincide
    }

    public static function isTablesManyToManyRelationship($tableName){
        
        // Definir la expresión regular
        $pattern = '/^([a-zA-Z0-9ñ]+)(?:_([a-zA-Z0-9ñ]+))+$/';
        // Verificar si la tabla coincide con la expresión regular, 
        //notese que no s4e usan los grupos de captura        
        if (preg_match($pattern, $tableName)){
            return true;
        }else {
            return false;
        }
    }

    public static function pluralToSingular($word) {
        // Reglas básicas para convertir plural a singular
        if (substr($word, -3) === 'ies') {
            
            // Palabras que terminan en 'ies' se convierten a 'y'
            return substr($word, 0, -3) . 'y';

        } elseif (substr($word, -1) === 's') {

            // Palabras que terminan en 's' se les quita la 's'
            return substr($word, 0, -1);

        } else {

            // Si no se aplica ninguna regla, devolver el plural original porque esta en singular
            return $word;
        
        }
    }

    public static function singularToPlural($word) {
       
        if (substr($word, -1) === 's'){
            return $word; // Si ya termina en 's', devolver la palabra porque esta en plural
        }
        else if (preg_match('/(y)$/', $word) && !preg_match('/(a|e|i|o|u)y$/', $word)) {

            return preg_replace('/y$/', 'ies', $word); // Cambiar 'y' por 'ies'
        
        }else {
            return $word . 's'; // Agregar 's' por defecto
        }
    }

    public static  function getRepeatedElements(array $array): array
    {
        $repeatedEle=[];

        foreach ($array as $element) {
            if(!in_array($element,$repeatedEle) && self::countElementRepetitions($element,$array)>1){

                $repeatedEle[]=$element;

            }
        }

        return $repeatedEle;
    }

    public static function countElementRepetitions($element,array $array): int
    {
       
        $counter=0;
       
        foreach ($array as $i) {
            if($i===$element){
                $counter++;
            }
        }   
       
        return $counter;
       
    }
}

?>