<?php

namespace Israeldavidvm\DatabaseAuditor\Tests;

use PDOException;
use Dotenv\Dotenv;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing
;
use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\SchemaFromDatabaseUsingName;

#[CoversClass(SchemaFromDatabaseUsingName::class)]
#[UsesClass(DatabaseAuditor::class)]
class SchemaFromDatabaseUsingNameTest extends TestCase
{
    protected $databaseAuditor;
    protected $schemaGenerator;
    protected $pdo;

    // public function __construct() {
        
    //     $this->databaseAuditor = new DatabaseAuditor;

    // }

    protected function setUp() : void
    {

        $this->databaseAuditor = new DatabaseAuditor;

        $dotenv = Dotenv::createImmutable(
            '.',
            '.env',
        );
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD','DB_PORT']);
        
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
    
    }

    protected function createFakeDB() : void
    {


        try {
             // Definición de la estructura de la primera tabla: empleados
             $tablaErrores = 'CREATE TABLE IF NOT EXISTS "errores" (
                "nombre" VARCHAR(255) NOT NULL,
                "descripcion" VARCHAR(255)
            )';
        
            // Ejecutar la consulta para crear la tabla empleados
            $this->pdo->exec($tablaErrores);

           
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

    public static function getUsualFunctionalDependenciesByTableProvider(): array
    {
        return [
            'not many to many no recursion' => [
                [
                    'empleados'=>[
                        'empleado_id',
                        'empleado_nombreE',                    
                    ], 
                    'asignaciones'=>[
                        'asignacione_id',
                        'empleado_id',
                        'asignacione_numProyecto',
                        'asignacione_horas',
                        'asignacione_nombreProyecto',
                        'asignacione_ubicacionProyecto'
                    ]
                ],
                [
                    'empleados'=>[
                    ], 
                    'asignaciones'=>[
                        'empleado_id',
                    ]
                ],
                [
                    'empleados'=>[
                        'empleado_id'
                    ], 
                    'asignaciones'=>[
                        'asignacione_id',
                    ]
                ],
                [
                    [
                        'x'=>['empleado_id'],
                        'y'=>[
                            'empleado_nombreE',                    
                        ]
                    ],
                    [
                        'x'=>['asignacione_id'],
                        'y'=> [
                            'empleado_id',
                            'asignacione_numProyecto',
                            'asignacione_nombreProyecto',
                            'asignacione_ubicacionProyecto',
                            'asignacione_horas',
                        ]
                        
                    ],

                ]
            ],
        ];
    }

    
    #[DataProvider('getUsualFunctionalDependenciesByTableProvider')]
    public function testGetUsualFunctionalDependenciesByTable(
        array $columnsByTable,
        array $fksByTable,
        array $pksByTable,
        array $expectedResult
    ): void {
        $databaseAuditor = new DatabaseAuditor();
        
        $mockSchemaGeneratorBuilder = $this->getMockBuilder(SchemaFromDatabaseUsingName::class)
            ->setConstructorArgs([$databaseAuditor, 
                [
                    'pathEnvFolder' => '.',
                    'name' => '.env',
                ],
                [
                    'mode' => 'exclude',
                    'tables' => [],
                ] 
            ])
            ->onlyMethods(['getTables', 'getFKsByTable', 'getColumnsByTable']);

        $mockSchemaGenerator = $mockSchemaGeneratorBuilder->getMock();

        $tables=array_keys($columnsByTable);

        $mockSchemaGenerator->method('getTables')
            ->willReturn($tables);

        $mapColumnsByTable = [];
        $mapFKsByTable = [];
        $mapPKsByTable=[];

        $databaseAuditor->decompositionsByTable = [];

        foreach ($columnsByTable as $tableName => $columns) {
            $mapFKsByTable[] = [$tableName, $fksByTable[$tableName]];
            $mapPKsByTable[] = [$tableName, true,$pksByTable[$tableName]];
            $mapColumnsByTable[] = [$tableName, true, $columns];

            $databaseAuditor->decompositionsByTable[$tableName]=$columns;

        }

        $mockSchemaGenerator->method('getFKsByTable')
            ->willReturnMap($mapFKsByTable);

        $mockSchemaGenerator->method('getColumnsByTable')
            ->willReturnMap($mapColumnsByTable);

        $result=[];


        foreach ($tables as $tableName) {
            $result=array_merge($result,$mockSchemaGenerator->getUsualFunctionalDependenciesByTable($tableName));
        }
      
        $this->assertTrue(DatabaseAuditor::areEqualFunctionalDependenciesSet($result,$expectedResult));
    }

    public static function generateJoinsClustersDataProvider(): array
    {
        return [
            'not many to many no recursion' => [
                [
                    'empleados'=>[
                        'id',
                        'nombreE',                    
                    ], 
                    'asignaciones'=>[
                        'id',
                        'empleado_id',
                        'numProyecto',
                        'horas',
                        'nombreProyecto',
                        'ubicacionProyecto'
                    ]
                ],
                [
                    'empleados'=>[
                    ], 
                    'asignaciones'=>[
                        'empleado_id',
                    ]
                ],
                [
                    [
                    'empleados','asignaciones'
                    ]
                ]
            ],
            'many to many not recursion' => [
                [
                    'empleados'=>[
                        'id',
                        'nombreE',                    
                    ], 
                    'empleado_proyecto'=>[
                        'id',
                        'empleado_id',
                        'proyecto_id',
                        'horas',
                        'categoria_id'
                    ],
                    'proyectos'=>[
                        'id',
                        'nombreProyecto',
                        'ubicacionProyecto'
                    ],
                    'categorias'=>[
                        'id',
                        'nombre',
                    ]
                ],
                [
                    'empleados'=>[
                    ], 
                    'empleado_proyecto'=>[
                        'empleado_id',
                        'proyecto_id',
                        'categoria_id'
                    ],
                    'proyectos'=>[
                    ]
                ],
                [
                    [
                    'empleados','proyectos','empleado_proyecto','categorias'
                    ]
                ]
            ],
        ];
    }

    
    #[DataProvider('generateJoinsClustersDataProvider')]
    public function testGenerateJoinsClusters(
        array $columnsByTable,
        array $fksByTable,
        array $expectedResult
    ): void {
        $databaseAuditor = new DatabaseAuditor();

        $mockSchemaGeneratorBuilder = $this->getMockBuilder(SchemaFromDatabaseUsingName::class)
            ->setConstructorArgs([$databaseAuditor, [
                'pathEnvFolder' => '.',
                'name' => '.env',
            ],
            [
                'mode' => 'exclude',
                'tables' => [],
            ] ])
            ->onlyMethods(['getTables', 'getFKsByTable', 'getColumnsByTable']);

        $mockSchemaGenerator = $mockSchemaGeneratorBuilder->getMock();

        $mockSchemaGenerator->method('getTables')
            ->willReturn(array_keys($columnsByTable));

        $mapColumnsByTable = [];
        $mapFKsByTable = [];

        foreach ($columnsByTable as $tableName => $columns) {
            $mapFKsByTable[] = [$tableName, $fksByTable[$tableName] ?? []];
            $mapColumnsByTable[] = [$tableName, false, $columns];
            $mapColumnsByTable[] = [$tableName, true, array_map(
                fn ($column) =>
                    SchemaFromDatabaseUsingName::pluralToSingular($tableName) .
                    '_' . $column,
                $columns
            )];
        }

        $mockSchemaGenerator->method('getFKsByTable')
            ->willReturnMap($mapFKsByTable);

        $mockSchemaGenerator->method('getColumnsByTable')
            ->willReturnMap($mapColumnsByTable);
      
        $this->assertEqualsCanonicalizing($expectedResult, $mockSchemaGenerator->generateJoinsClusters());
    }

    public static function getFKsByTableProvider(): array
    {
        return [
            'single foreign key' => [
                'asignaciones',
                ['empleado_id'],
            ],
            'no foreign keys' => [
                'empleados',
                [],
            ],
            // 'multiple foreign keys' => [
            //     'productos',
            //     ['categoria_id'],
            // ],
        ];
    }

    #[DataProvider('getFKsByTableProvider')]
    public function testGetFKsByTable(string $tableName, array $expectedForeignKeys): void
    {
        $this->createFakeDB();
        $schemaFromDatabaseUsingName = new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            [
                'pathEnvFolder' => '.',
                'name' => '.env',
            ],
            [
                'mode' => 'exclude',
                'tables' => ['errores'],
            ]
        );

        $foreignKeys = $schemaFromDatabaseUsingName->getFKsByTable($tableName);
        $this->assertEqualsCanonicalizing($expectedForeignKeys, $foreignKeys);
    }

    public static function getPKByTableProvider(): array
    {
        return [
            'single primary key not fully qualified' => [
                'empleados',
                false,
                ['id'],
            ],
            'single primary key fully qualified' => [
                'empleados',
                true,
                ['empleado_id'],
            ],
        ];
    }

    #[DataProvider('getPKByTableProvider')]
    public function testGetPKByTable(string $tableName, bool $fullyQualifiedForm, array $expectedPrimaryKey): void
    {
        $this->createFakeDB();
        $schemaFromDatabaseUsingName = new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            [
                'pathEnvFolder' => '.',
                'name' => '.env',
            ],
            [
                'mode' => 'exclude',
                'tables' => ['errores'],
            ]
        );

        $primaryKey = $schemaFromDatabaseUsingName->getPKByTable($tableName, $fullyQualifiedForm);
        $this->assertEqualsCanonicalizing($expectedPrimaryKey, $primaryKey);
    }

    public static function getReferencedTableFromFkProvider(): array
    {
        return [
            'simple foreign key' => [
                'empleado_id',
                'empleados',
            ],
            'many-to-many foreign key part 1' => [
                'producto_categoria_id', 
                'producto_categoria', 
            ],
            'foreign key already plural (should not pluralize again)' => [
                'roles_id', // Aunque la convención es singular_id
                'roles',
            ],
            'foreign key with mixed case table name' => [
                'userRole_id',
                'userRoles',
            ],
            'foreign key with ñ' => [
                'araña_id',
                'arañas',
            ],
        ];
    }

    #[DataProvider('getReferencedTableFromFkProvider')]
    public function testGetReferencedTableFromFk(string $foreignKey, string $expectedReferencedTable): void
    {
        $this->assertEquals(
            $expectedReferencedTable, 
            SchemaFromDatabaseUsingName::getReferencedTableFromFk($foreignKey)
        );
    }


    public static function getReferencedTableFromFkWithExceptionProvider(): array
    {
        return [
            'foreign key with leading underscore (unlikely but testing robustness)' => [
                '_tabla_id',
                '_tablas',
            ],
            'foreign key with trailing characters after _id (should only remove _id)' => [
                'item_id_extra',
                'items_extra',
            ],
        ];
    }

    #[DataProvider('getReferencedTableFromFkWithExceptionProvider')]
    public function testGetReferencedTableFromFkWithException(string $foreignKey, string $expectedReferencedTable): void
    {

        $this->expectException(\Exception::class);
        SchemaFromDatabaseUsingName::getReferencedTableFromFk($foreignKey);
    }

    public static function getColumnsByTableProvider():array{

        return [
            'fullyQualifiedForm'=>
                [
                    'asignaciones',
                    true,
                    [
                        'asignacione_id',
                        'empleado_id',
                        'asignacione_numProyecto',
                        'asignacione_nombreProyecto',
                        'asignacione_ubicacionProyecto',
                        'asignacione_horas',
                    ]
                ],
            'not fullyQualifiedForm'=>
            [
                'asignaciones',
                    false,
                    [
                        'id',
                        'empleado_id',
                        'numProyecto',
                        'nombreProyecto',
                        'ubicacionProyecto',
                        'horas',
                    ]
            ],
        ];

    }
    #[DataProvider('getColumnsByTableProvider')]
    public function testGetColumnsByTable($tableName,$fullyQualifiedForm=false,$resultExpected){

        $this->createFakeDB();
        $schemaFromDatabaseUsingName=new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            [
                'pathEnvFolder' => '.',
                'name' => '.env',
            ],
            [
                'mode' => 'exclude',
                'tables' => [
                    'errores',
                ],
            ]
        );

        $columns = $schemaFromDatabaseUsingName->getColumnsByTable($tableName,$fullyQualifiedForm);
        $this->assertEqualsCanonicalizing($resultExpected,$columns);
    }

    public static function getTablesProvider():array{

        return [
            'include config'=>
                [
                    [
                        'pathEnvFolder' => '.',
                        'name' => '.env',
                    ],
                    [
                        'mode' => 'include',
                        'tables' => ['table1', 'table2'],
                    ],
                    ['table1', 'table2']
                ],
            'exclude config'=>
            [
                [
                    'pathEnvFolder' => '.',
                    'name' => '.env',
                ],
                [
                    'mode' => 'exclude',
                    'tables' => [
                        'errores',
                    ],
                ],
                ['empleados', 'asignaciones']
            ],
        ];

    }

  
    #[DataProvider('getTablesProvider')]
    public function testGetTablesWithoutException($metaInfoEnvFile,$metaInfoClusterTables,$resultExpected)
    {

        $this->createFakeDB();
        
        $schemaFromDatabaseUsingName=new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            $metaInfoEnvFile,
            $metaInfoClusterTables
        );


        $tables = $schemaFromDatabaseUsingName->getTables();
        $this->assertEqualsCanonicalizing($resultExpected, $tables);
    }


    public static function getTablesExceptionProvider():array{

        return [
            [
                [
                    'pathEnvFolder' => '.',
                    'name' => '.env',
                ],
                [
                    'tables' => ['table1', 'table2'],
                ],
                ['table1', 'table2']
            ],
            [
                [
                    'pathEnvFolder' => '.',
                    'name' => '.env',
                ],
                [
                    'mode' => 'helado',
                ],
                ['table1', 'table2']
            ],
        ];

    }

    #[DataProvider('getTablesExceptionProvider')]
    public function testGetTablesWithException($metaInfoEnvFile,$metaInfoClusterTables,$resultExpected)
    {

        $schemaFromDatabaseUsingName=new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            $metaInfoEnvFile,
            $metaInfoClusterTables
        );

        $this->expectException(\Exception::class);
        $tables = $schemaFromDatabaseUsingName->getTables();
    }

   
    public static function getTablesManyToManyRelationshipProvider(): array
    {
        return [
            'valid many-to-many table name' => [
                'table1_table2',
                ['table1s', 'table2s'],
            ],
            'valid many-to-many table name with multiple parts' => [
                'order_product_details',
                ['orders', 'products', 'details'],
            ],
            'valid many-to-many table name with mixed case' => [
                'User_Role',
                ['Users', 'Roles'],
            ],
            'valid many-to-many table name with numbers' => [
                'item1_category2',
                ['item1s', 'category2s'],
            ],
            'invalid table name - single part' => [
                'users',
                [],
            ],
            'invalid table name - no underscore' => [
                'usertable',
                [],
            ],
            'invalid table name - empty' => [
                '',
                [],
            ],
            'invalid table name - starts with underscore' => [
                '_table1_table2',
                [],
            ],
            'invalid table name - ends with underscore' => [
                'table1_table2_',
                [],
            ],
            'invalid table name - consecutive underscores' => [
                'table1__table2',
                [],
            ],
        ];
    }

    #[DataProvider('getTablesManyToManyRelationshipProvider')]
    public function testGetTablesManyToManyRelationship(string $tableName, array $expectedResult): void
    {
        $this->assertEquals($expectedResult, SchemaFromDatabaseUsingName::getTablesManyToManyRelationship($tableName));
    }


    public static function isTablesManyToManyRelationshipProvider(): array
    {
        return [
            'valid_table_name' => ['table1_table2', true],
            'valid_table_name_with_numbers' => ['table1_table2_123', true],
            'valid_table_name_with_n' => ['tabla_tabla2_ñandu', true],
            'invalid_table_name_single_word' => ['table1', false],
            'invalid_table_name_starts_with_underscore' => ['_table1_table2', false],
            'invalid_table_name_ends_with_underscore' => ['table1_table2_', false],
            'invalid_table_name_consecutive_underscores' => ['table1__table2', false],
            'invalid_table_name_empty' => ['', false],
        ];
    }

    #[DataProvider('isTablesManyToManyRelationshipProvider')]
    public function testIsTablesManyToManyRelationship($tableName, $resultExpected)
    {

        $this->assertEquals(
            $resultExpected, 
            SchemaFromDatabaseUsingName::isTablesManyToManyRelationship($tableName)
        );

    }

    public function testListTableNamesToMetaInfoClusterTables(){
    
        $listTableNames = [
            'table1',
            'table2',
            'table3',
        ];

        $this->assertEquals(
            [
                'mode'=>'include',
                'tables'=>$listTableNames
            ],
            schemaFromDatabaseUsingName::listTableNamesToMetaInfoClusterTables($listTableNames)
        );

    }


    public static function pluralToSingularProvider(): array
    {
        return [
            'word is singular' => [
                'city',
                'city',
            ],
            'word ends in consonant + y' => [
                'cities',
                'city',
            ],
            'word ends in vowel + y' => [
                'boys',
                'boy',
            ],
            'word ends in simple consonant' => [
                'cats',
                'cat',
            ],
            'word ends in vowel' => [
                'cars',
                'car',
            ],
            'word is empty' => [
                's', 
                '',
            ],
             'word with ñ' => [
                'arañays',
                'arañay',
            ],
            'word with uppercase' => [
                'Parties',
                'Party',
            ],
            'word with numbers' => [
                'baby1s',
                'baby1',
            ],
        ];
    }

    #[DataProvider('pluralToSingularProvider')]
    public function testPluralToSingular(string $plural, string $expectedSingular): void
    {
        $this->assertEquals($expectedSingular, SchemaFromDatabaseUsingName::pluralToSingular($plural));
    }

    public static function singularToPluralProvider(): array
    {
        return [
            'word is plural' => [
                'cities',
                'cities',
            ],
            'word ends in consonant + y' => [
                'city',
                'cities',
            ],
            'word ends in vowel + y' => [
                'boy',
                'boys',
            ],
            'word ends in simple consonant' => [
                'cat',
                'cats',
            ],
            'word ends in vowel' => [
                'car',
                'cars',
            ],
            'word is empty' => [
                '',
                's', // Or should this be empty? Depends on requirement.
            ],
            'word with ñ' => [
                'arañay',
                'arañays',
            ],
            'word with uppercase' => [
                'Party',
                'Parties',
            ],
            'word with numbers' => [
                'baby1',
                'baby1s',
            ],
        ];
    }


    #[DataProvider('singularToPluralProvider')]
    public function testSingularToPlural(string $singular, string $expectedPlural): void
    {
        $this->assertEquals($expectedPlural, SchemaFromDatabaseUsingName::singularToPlural($singular));
    }

    public static function hasRepeatedElementsProvider(): array
    {
        return [
            'empty array' => [
                [],
                false,
            ],
            'array with unique integers' => [
                [1, 2, 3, 4, 5],
                false,
            ],
            'array with repeated integers' => [
                [1, 2, 3, 2, 5],
                true,
            ],
            'array with unique strings' => [
                ['a', 'b', 'c', 'd'],
                false,
            ],
            'array with repeated strings' => [
                ['a', 'b', 'c', 'a', 'd'],
                true,
            ],
            'array with mixed unique types' => [
                [1, 'a', true, 3.14],
                false,
            ],
            'array with mixed repeated types' => [
                [1, 'a', true, 1],
                true,
            ],
            'array with case-sensitive string repetition' => [
                ['apple', 'Apple'],
                false,
            ],
            'array with null and repetition' => [
                [null, 1, null],
                true,
            ],
            'array eqivalent types repetition' => [
                [false, 0],
                false,
            ],
        ];
    }

    
    #[DataProvider('hasRepeatedElementsProvider')]
    public function testHasRepeatedElements(array $array, bool $expected): void
    {
        $this->assertSame($expected, SchemaFromDatabaseUsingName::hasRepeatedElements($array));
    }
}