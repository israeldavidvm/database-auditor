<?php

namespace Israeldavidvm\DatabaseAuditor\Tests;

use PDOException;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing
;
use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\SchemaFromDatabaseUsingName;
use Israeldavidvm\DatabaseAuditor\SchemaFromJSON;
use Israeldavidvm\DatabaseAuditor\Schema;
use Israeldavidvm\DatabaseAuditor\Report;
use Exception;

#[CoversClass(Schema::class)]
#[UsesClass(DatabaseAuditor::class)]
#[UsesClass(Schema::class)]
#[UsesClass(SchemaFromJSON::class)]
#[UsesClass(SchemaFromDatabaseUsingName::class)]

#[UsesClass(Report::class)]

class SchemaTest extends TestCase
{
    protected $databaseAuditor;
    protected $schemaGenerator;
    protected $pdo;

    protected function createFakeDB($pathEnvFile) : void
    {

        $dotenv = Dotenv::createImmutable(
            dirname($pathEnvFile),
            basename($pathEnvFile),
        );

        $dotenv->load();
        
        // Configuración de la conexión a la base de datos
        $dataBaseConfig=[
            'host' => $_ENV['DB_HOST'], // Cambia esto si tu servidor es diferente
            'dbname' => $_ENV['DB_DATABASE'], // Nombre de tu base de datos
            'user' => $_ENV['DB_USERNAME'], // Tu usuario de la base de datos
            'password' => $_ENV['DB_PASSWORD'], // Tu contraseña de la base de datos
            'port' => $_ENV['DB_PORT'] // Puerto de la base de datos
        ];

        try {
            // Crear una conexión PDO
            $this->pdo = new \PDO(
                "pgsql:host={$dataBaseConfig['host']};port={$dataBaseConfig['port']};dbname={$dataBaseConfig['dbname']}", // Se añadió ;port=...
                $dataBaseConfig['user'],
                $dataBaseConfig['password']
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Error de conexión: para la configuracion ".json_encode($dataBaseConfig)." ". $e->getMessage();
        }
    
        try {
             // Definición de la estructura de la primera tabla: empleados
             $tablaHelado = 'CREATE TABLE IF NOT EXISTS "helados" (
                "nombre" VARCHAR(255) NOT NULL,
                "descripcion" VARCHAR(255)
            )';
        
            // Ejecutar la consulta para crear la tabla empleados
            $this->pdo->exec($tablaHelado);

           
            // Definición de la estructura de la primera tabla: empleados
            $tablaEmpleados = 'CREATE TABLE IF NOT EXISTS "empleados" (
                "id" INT NOT NULL PRIMARY KEY,
                "nombreE" VARCHAR(255) NOT NULL
            )';
        
            // Ejecutar la consulta para crear la tabla empleados
            $this->pdo->exec($tablaEmpleados);
            // echo "Tabla 'empleados' creada exitosamente.\n";
        
            // Definición de la estructura de la segunda tabla: asignaciones
            $tablaAsignaciones = 'CREATE TABLE IF NOT EXISTS "asignaciones" (
                "id" INT NOT NULL PRIMARY KEY,
                "empleado_id" INT NOT NULL REFERENCES empleados(id),
                "numProyecto" INT,
                "horas" DECIMAL(5, 2),
                "nombreProyecto" VARCHAR(255),
                "ubicacionProyecto" VARCHAR(255)
            )';
        
            // Ejecutar la consulta para crear la tabla asignaciones
            $this->pdo->exec($tablaAsignaciones);
            // echo "Tabla 'asignaciones' creada exitosamente.\n";
        
        } catch (PDOException $e) {
            echo "Error al crear las tablas: " . $e->getMessage() . "\n";
        }
    }


    public function testGenerateJoinsClustersFromJson(
    ): void {

        $path='./RepeatedReferencedExampleDB.json';

        $this->databaseAuditor = new DatabaseAuditor;

        $this->databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= new SchemaFromJSON(
            $this->databaseAuditor,
            $path
        );

        $this->databaseAuditor->generateDatabaseSchema();

        $joinsCluster=$this->databaseAuditor->joinsClusters;
        
        $expectedResult=(object)[
        "universalRelationship"=>[
                "person_person_id",
                "person_supervisor_id",
                "person_supervisaddo_id",
                "score_id",
                "person_supervisor_name",
                "person_supervisaddo_name",
                "score_name",
            ],
        "decompositionsByTable"=>
            [
                "person_person" => [
                    "person_person_id",
                    "person_supervisor_id",
                    "person_supervisaddo_id",
                    "score_id"
                ],
                "person_supervisors" => [
                    "person_supervisor_id",
                    "person_supervisor_name"
                ],
                "person_supervisaddos" => [
                    "person_supervisaddo_id",
                    "person_supervisaddo_name"
                ],
                "scores" => [
                    "score_id",
                    "score_name"
                ],
            ],
            "functionalDependencies"=>[
                [
                    'x' => ['score_id'],
                    'y' => ['score_name'],
                ],
                [
                    'x' => ['person_supervisaddo_id'],
                    'y' => ['person_supervisaddo_name'],
                ],
                [
                    'x' => ['person_supervisor_id'],
                    'y' => ['person_supervisor_name'],
                ],
                [
                    'x' => ['person_id'],
                    'y' => ['person_name'],
                ],
                [
                    'x' => ['person_person_id'],
                    'y' => ['person_supervisor_id', 'person_supervisaddo_id', 'score_id'],
                ],
            ],
        ];

        // echo json_encode($joinsCluster[0]->functionalDependencies);

        $this->assertEqualsCanonicalizing(
            $expectedResult->universalRelationship,
            $joinsCluster[0]->universalRelationship
        );

        $this->assertEqualsCanonicalizing(
            $expectedResult->decompositionsByTable,
            $joinsCluster[0]->decompositionsByTable
        );

        $this->assertTrue(
            Schema::areEqualFunctionalDependenciesSet(
                $expectedResult->functionalDependencies,
                $joinsCluster[0]->functionalDependencies
            )
        );

        // $this->assertTrue(
        //     $expectedResult->universalRelationship==$joinsCluster[0]->universalRelationship
        //     &&
        //     $expectedResult->decompositionsByTable==$joinsCluster[0]->decompositionsByTable
        //     &&
        //     $expectedResult->functionalDependencies==$joinsCluster[0]->functionalDependencies
        // ) ;
    }

    public static function changeAttributeTableDataProvider()
    {
        return array(
            array('user_id', 'users', 'users', 'user_id'),
            array('user_name', 'users', 'people', 'people_name'),
            array('name', 'users', 'people', 'name'),
            array('_leadingAttribute', 'users', 'people', '_leadingAttribute'), #creo deberias cambiar
            array('person_id', 'persons', 'person_supervisors', 'person_supervisor_id'),
            array('person_person_id', 'person_person', 'helado', 'helado_id'),
            array('person_person_id', 'persons', 'helado', 'person_person_id'),
            array('person_supervisor_id', 'persons', 'helado', 'person_supervisor_id'),
            // array('singleword', 'users', 'people', 'person'),
        );
    }
    
    #[DataProvider('changeAttributeTableDataProvider')]
    public function testChangeAttributeTable($attribute, $oldTable, $newTable, $expected)
    {
        $this->assertEquals($expected, Schema::changeAttributeTable($attribute, $oldTable, $newTable));
    }

    public static function changeFunctionalDependencyProvider()
    {
        return array(
            [
                [
                    "x"=>['person_id'],
                    "y"=>['person_name']
                ]
                ,'persons', 'person_supervisors', 
                [
                    "x"=>['person_supervisor_id'],
                    "y"=>['person_supervisor_name']
                ]
            ],
            [
                [
                    "x"=>['person_person_id'],
                    "y"=>['person_person_name']
                ]
                ,'persons', 'person_supervisors', 
                [
                    "x"=>['person_person_id'],
                    "y"=>['person_person_name']
                ]
            ],
        );
    }
    
    #[DataProvider('changeFunctionalDependencyProvider')]
    public function testChangeFunctionalDependencyProvider($fd, $oldTable, $newTable, $expected)
    {
        $this->assertEquals($expected, Schema::changeFunctionalDependency($fd, $oldTable, $newTable));
    }

    // public static function generateJoinsClustersDataProvider(): array
    // {
    //     return [
    //         'recursive without role' => [
    //             [
    //                 'persons'=>[
    //                     'id',
    //                     'person_id', 
    //                     'name',                    
    //                 ], 
    //             ],
    //             [
    //                 'persons'=>[
                 
    //                     'person_id'

    //                 ], 
    //             ],
    //             [
    //                 [
    //                 'persons',
    //                 'person_referenceds',
    //                 ]
    //             ]
    //         ],
    //         'recursive' => [
    //             [
    //                 'persons'=>[
    //                     'id',
    //                     'person_couple_id', 
    //                     'name',                    
    //                 ], 
    //             ],
    //             [
    //                 'persons'=>[
                 
    //                     'person_couple_id'

    //                 ], 
    //             ],
    //             [
    //                 [
    //                 'persons',
    //                 'person_couples',
    //                 ]
    //             ]
    //         ],
    //         'rnary with mixed' => [
    //             [
    //                 'persons'=>[
    //                     'id',
    //                     'name',                    
    //                 ], 
    //                 'person_person'=>[
    //                     'id',
    //                     'person_supervisaddo_id',
    //                     'person_supervisor_id',
    //                     'score_id',
                       
    //                 ],
    //                 'scores'=>[
    //                     'id',
    //                    'name'
                       
    //                 ]
    //             ],
    //             [
    //                 'persons'=>[
                 
    //                 ], 
    //                 'scores'=>[
                 
    //                 ], 
    //                 'person_person'=>[
    //                     'person_supervisaddo_id',
    //                     'person_supervisor_id',
    //                     'score_id',
    //                 ]
    //             ],
    //             [
    //                 [
    //                 'person_person',
    //                 'person_supervisors',
    //                 'person_supervisaddos',
    //                 'scores'
    //                 ]
    //             ]
    //         ],
    //         'rnary' => [
    //             [
    //                 'taxonomies'=>[
    //                     'id',
    //                     'name',                    
    //                 ], 
    //                 'taxonomy_taxonomy'=>[
    //                     'id',
    //                     'taxonomy_parent_id',
    //                     'taxonomy_child_id',
                       
    //                 ]
    //             ],
    //             [
    //                 'taxonomies'=>[
                 
    //                 ], 
    //                 'taxonomy_taxonomy'=>[
    //                     'taxonomy_parent_id',
    //                     'taxonomy_child_id',
    //                 ]
    //             ],
    //             [
    //                 [
    //                 'taxonomy_taxonomy',
    //                 'taxonomy_parents',
    //                 'taxonomy_childs',
    //                 ]
    //             ]
    //         ],
    //         'not many to many no recursion' => [
    //             [
    //                 'empleados'=>[
    //                     'id',
    //                     'nombreE',                    
    //                 ], 
    //                 'asignaciones'=>[
    //                     'id',
    //                     'empleado_id',
    //                     'numProyecto',
    //                     'horas',
    //                     'nombreProyecto',
    //                     'ubicacionProyecto'
    //                 ]
    //             ],
    //             [
    //                 'empleados'=>[
    //                 ], 
    //                 'asignaciones'=>[
    //                     'empleado_id',
    //                 ]
    //             ],
    //             [
    //                 [
    //                 'empleados','asignaciones'
    //                 ]
    //             ]
    //         ],
    //         'many to many not recursion' => [
    //             [
    //                 'empleados'=>[
    //                     'id',
    //                     'nombreE',                    
    //                 ], 
    //                 'empleado_proyecto'=>[
    //                     'id',
    //                     'empleado_id',
    //                     'proyecto_id',
    //                     'horas',
    //                     'categoria_id'
    //                 ],
    //                 'proyectos'=>[
    //                     'id',
    //                     'nombreProyecto',
    //                     'ubicacionProyecto'
    //                 ],
    //                 'categorias'=>[
    //                     'id',
    //                     'nombre',
    //                 ]
    //             ],
    //             [
    //                 'empleados'=>[
    //                 ], 
    //                 'empleado_proyecto'=>[
    //                     'empleado_id',
    //                     'proyecto_id',
    //                     'categoria_id'
    //                 ],
    //                 'proyectos'=>[
    //                 ]
    //             ],
    //             [
    //                 [
    //                 'empleados','proyectos','empleado_proyecto','categorias'
    //                 ]
    //             ]
    //         ],
    //     ];
    // }

    
    // #[DataProvider('generateJoinsClustersDataProvider')]
    // public function testGenerateJoinsClusters(
    //     array $columnsByTable,
    //     array $fksByTable,
    //     array $expectedResult
    // ): void {

    //     $databaseAuditor = new DatabaseAuditor();

    //     $mockSchemaGeneratorBuilder = $this->getMockBuilder(SchemaFromDatabaseUsingName::class)
    //         ->setConstructorArgs([$databaseAuditor, 
    //         './.envTest',
    //         ])
    //         ->onlyMethods(['getTables', 'getFKsByTable', 'getColumnsByTable']);

    //     $mockSchemaGenerator = $mockSchemaGeneratorBuilder->getMock();

    //     $mockSchemaGenerator->method('getTables')
    //         ->willReturn(array_keys($columnsByTable));

    //     $mapColumnsByTable = [];
    //     $mapFKsByTable = [];

    //     foreach ($columnsByTable as $tableName => $columns) {
    //         $mapFKsByTable[] = [$tableName, $fksByTable[$tableName] ?? []];
    //         $mapColumnsByTable[] = [$tableName, false, $columns];
    //         $mapColumnsByTable[] = [$tableName, true, array_map(
    //             fn ($column) =>
    //                 SchemaFromDatabaseUsingName::pluralToSingular($tableName) .
    //                 '_' . $column,
    //             $columns
    //         )];
    //     }

    //     $mockSchemaGenerator->method('getFKsByTable')
    //         ->willReturnMap($mapFKsByTable);

    //     $mockSchemaGenerator->method('getColumnsByTable')
    //         ->willReturnMap($mapColumnsByTable);
      
    //     $schema=new Schema();

    //     $joinsCluster=$mockSchemaGenerator->generateJoinsClusters();

    //     // $this->assertEqualsCanonicalizing($expectedResult, ;
    // }

    public function generateEnvFile(
        string $filename, 
        string $dataAuditorFiler, 
        array $dataAuditorElements
        ): bool
    {
        $elementsString = implode(',', $dataAuditorElements);
    
        $content = <<<EOT
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    
    DB_PORT=7777
    DB_DATABASE=test
    DB_USERNAME=test
    DB_PASSWORD=test
    
    DATA_AUDITOR_FILTER={$dataAuditorFiler}
    DATA_AUDITOR_ELEMENTS={$elementsString}
    DATA_AUDITOR_PATH_FUNCTIONAL_DEPENDENCIES_JSON_FILE=./functionDepedenciesTest.json
    EOT;
    
        try {
            $result = file_put_contents($filename, $content);
            return $result !== false;
        } catch (\Exception $e) {
            // Handle any file writing errors
            echo("Error al crear el archivo {$filename}: " . $e->getMessage());
            return false;
        }
    }
    
}