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
use Israeldavidvm\DatabaseAuditor\Schema;
use Israeldavidvm\DatabaseAuditor\Report;
use Exception;

#[CoversClass(SchemaFromDatabaseUsingName::class)]
#[UsesClass(DatabaseAuditor::class)]
#[UsesClass(Schema::class)]
#[UsesClass(Report::class)]

class SchemaFromDatabaseUsingNameTest extends TestCase
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

    public static function initDatabaseConnectionProvider(): array
    {
        return [
            'Invalid env file' => [
                './queso',
                [
                    'result' => '',
                    'throwException'=>Exception::class
                ]

            ],
            'Invalid config' => [
                './.envInvalidConfig',
                [
                    'result' => '',
                    'throwException'=>Exception::class
                ]

            ],
            'Incompleted config' => [
                './.envIncompletedConfig',
                [
                    'result' => '',
                    'throwException'=>Exception::class
                ]

            ],

 
        ];
    }

    #[DataProvider('initDatabaseConnectionProvider')]
    public function testInitDatabaseConnection($pathEnvFile,$resultExpected): void{
        
        $databaseAuditor = new DatabaseAuditor();

        $this->expectException($resultExpected['throwException']);

        new SchemaFromDatabaseUsingName(
            $databaseAuditor,
            $pathEnvFile,
        );

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
            ->setConstructorArgs([
                $databaseAuditor, 
                './.envTest',
            ])
            ->onlyMethods(['getTables', 'getFKsByTable', 'getColumnsByTable']);

        $mockSchemaGenerator = $mockSchemaGeneratorBuilder->getMock();

        $tables=array_keys($columnsByTable);

        $mockSchemaGenerator->method('getTables')
            ->willReturn($tables);

        $mapColumnsByTable = [];
        $mapFKsByTable = [];
        $mapPKsByTable=[];

        $databaseAuditor->baseSchema->decompositionsByTable = [];

        foreach ($columnsByTable as $tableName => $columns) {
            $mapFKsByTable[] = [$tableName, $fksByTable[$tableName]];
            $mapPKsByTable[] = [$tableName, true,$pksByTable[$tableName]];
            $mapColumnsByTable[] = [$tableName, true, $columns];

            $databaseAuditor->baseSchema->decompositionsByTable[$tableName]=$columns;

        }

        $mockSchemaGenerator->method('getFKsByTable')
            ->willReturnMap($mapFKsByTable);

        $mockSchemaGenerator->method('getColumnsByTable')
            ->willReturnMap($mapColumnsByTable);

        $result=[];


        foreach ($tables as $tableName) {
            $result=array_merge($result,$mockSchemaGenerator->getUsualFunctionalDependenciesByTable($tableName));
        }
      
        $this->assertTrue(Schema::areEqualFunctionalDependenciesSet($result,$expectedResult));
    }

    public static function generateJoinsClustersDataProvider(): array
    {
        return [
            'recursive without role' => [
                [
                    'persons'=>[
                        'id',
                        'person_id', 
                        'name',                    
                    ], 
                ],
                [
                    'persons'=>[
                 
                        'person_id'

                    ], 
                ],
                [
                    [
                    'persons',
                    'person_referenceds',
                    ]
                ]
            ],
            'recursive' => [
                [
                    'persons'=>[
                        'id',
                        'person_couple_id', 
                        'name',                    
                    ], 
                ],
                [
                    'persons'=>[
                 
                        'person_couple_id'

                    ], 
                ],
                [
                    [
                    'persons',
                    'person_couples',
                    ]
                ]
            ],
            'rnary with mixed' => [
                [
                    'persons'=>[
                        'id',
                        'name',                    
                    ], 
                    'person_person'=>[
                        'id',
                        'person_supervisaddo_id',
                        'person_supervisor_id',
                        'score_id',
                       
                    ],
                    'scores'=>[
                        'id',
                       'name'
                       
                    ]
                ],
                [
                    'persons'=>[
                 
                    ], 
                    'scores'=>[
                 
                    ], 
                    'person_person'=>[
                        'person_supervisaddo_id',
                        'person_supervisor_id',
                        'score_id',
                    ]
                ],
                [
                    [
                    'person_person',
                    'person_supervisors',
                    'person_supervisaddos',
                    'scores'
                    ]
                ]
            ],
            'rnary' => [
                [
                    'taxonomies'=>[
                        'id',
                        'name',                    
                    ], 
                    'taxonomy_taxonomy'=>[
                        'id',
                        'taxonomy_parent_id',
                        'taxonomy_child_id',
                       
                    ]
                ],
                [
                    'taxonomies'=>[
                 
                    ], 
                    'taxonomy_taxonomy'=>[
                        'taxonomy_parent_id',
                        'taxonomy_child_id',
                    ]
                ],
                [
                    [
                    'taxonomy_taxonomy',
                    'taxonomy_parents',
                    'taxonomy_childs',
                    ]
                ]
            ],
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
            ->setConstructorArgs([$databaseAuditor, 
            './.envTest',
            ])
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
        $this->databaseAuditor=new DatabaseAuditor();

        $fileName='envWithoutHelados';

        $this->generateEnvFile(
            $fileName, 
            'exclude', 
            ['helados']
        );

        $this->createFakeDB("./$fileName");

        $schemaFromDatabaseUsingName = new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            "./$fileName",
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
        $this->databaseAuditor=new DatabaseAuditor();

        $fileName='envWithoutHelados';

        $this->generateEnvFile(
            $fileName, 
            'exclude', 
            ['helados']
        );

        $this->createFakeDB("./$fileName");

        $schemaFromDatabaseUsingName = new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            "./$fileName",
        );

        $primaryKey = $schemaFromDatabaseUsingName->getPKByTable($tableName, $fullyQualifiedForm);
        $this->assertEqualsCanonicalizing($expectedPrimaryKey, $primaryKey);
    }

    public static function getReferencedTableFromFkProvider(): array
    {
        return [
            'simple foreign key' => [
                'empleado_id',
                ['empleados'],
                false,
                'empleados',
            ],
            'many-to-many foreign key part 1' => [
                'producto_categoria_id',
                ['producto_categoria'],
                false,
                 'producto_categoria', 
            ],
            'foreign key already plural (should not pluralize again)' => [
                'roles_id', 
                ['roles'],
                false,
                'roles',
            ],
            'foreign key with mixed case table name' => [
                'userRole_id',
                ['userRoles'],
                false,
                'userRoles',
            ],
            'foreign key with ñ' => [
                'araña_id',
                ['arañas'],
                false,
                'arañas',
            ],
            'foreign key with ñ' => [
                'araña_id',
                ['arañas'],
                false,
                'arañas',
            ],
            'foreign key with leading underscore (unlikely but testing robustness)' => [
                '_tabla_id',
                ['tablas'],
                false,
                null,
            ],
            'foreign key with trailing characters after _id (should only remove _id)' => [
                'item_id_extra',
                ['items'],
                false,
                null,
            ],
            'foreign key to inexisting table' => [
                'user_id',
                ['items', 'projects'],
                false,
                null,
            ],
            'foreign key with role to inexisting table' => [
                'user_sup_id',
                ['items', 'projects'],
                true,
                null,
            ],
            'foreign key with role ' => [
                'user_sup_id',
                ['items', 'user'],
                true,
                'user_sups',
            ],
        ];
    }

    #[DataProvider('getReferencedTableFromFkProvider')]
    public function testGetReferencedTableFromFk(
        string $foreignKey,
        array $tables, 
        $withRole,
        ?string $expectedReferencedTable
        ): void
    {
        $this->assertEquals(
            $expectedReferencedTable, 
            SchemaFromDatabaseUsingName::getReferencedTableFromFk($foreignKey,$tables,$withRole)
        );
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

        $this->databaseAuditor=new DatabaseAuditor();

        $fileName='envWithoutHelados';

        $this->generateEnvFile(
            $fileName, 
            'exclude', 
            ['helados']
        );

        $this->createFakeDB("./$fileName");

        $schemaFromDatabaseUsingName = new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            "./$fileName",
        );

        $columns = $schemaFromDatabaseUsingName->getColumnsByTable($tableName,$fullyQualifiedForm);
        $this->assertEqualsCanonicalizing($resultExpected,$columns);
    }

    public static function getTablesProvider():array{

        return [
            'include config'=>
                [
                    'envTestInclude',
                    [
                        'mode' => 'include',
                        'tables' => [
                            'empleados'
                        ],
                    ],
                    ['empleados']
                ],
            'exclude config'=>
            [
                'envTestExclude',
                [
                    'mode' => 'exclude',
                    'tables' => [
                        'helados',
                    ],
                ],
                ['empleados', 'asignaciones']
            ],
        ];

    }

  
    #[DataProvider('getTablesProvider')]
    public function testGetTables($fileName,$metaInfoClusterTables,$resultExpected)
    {

        $this->databaseAuditor=new DatabaseAuditor();

        $this->generateEnvFile(
            $fileName, 
            $metaInfoClusterTables['mode'], 
            $metaInfoClusterTables['tables']
        );

        $this->createFakeDB("./$fileName");

        $schemaFromDatabaseUsingName = new SchemaFromDatabaseUsingName(
            $this->databaseAuditor,
            "./$fileName",
        );


        $tables = $schemaFromDatabaseUsingName->getTables();
        
        $this->assertEqualsCanonicalizing($resultExpected, $tables);
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

    public static function getRepeatedElementsProvider(): array
    {
        return [
            'empty array' => [
                [],
                [],
            ],
            'array with unique integers' => [
                [1, 2, 3, 4, 5],
                [],
            ],
            'array with repeated integers' => [
                [1, 2, 3, 2, 5],
                [2],
            ],
            'array with unique strings' => [
                ['a', 'b', 'c', 'd'],
                [],
            ],
            'array with repeated strings' => [
                ['a', 'b', 'c', 'a', 'd'],
                ['a'],
            ],
            'array with mixed unique types' => [
                [1, 'a', true, 3.14],
                [],
            ],
            'array with mixed repeated types' => [
                [1, 'a', true, 1],
                [1],
            ],
            'array with case-sensitive string repetition' => [
                ['apple', 'Apple'],
                [],
            ],
            'array with null and repetition' => [
                [null, 1, null],
                [null],
            ],
            'array eqivalent types repetition' => [
                [false, 0],
                [],
            ],
        ];
    }

    
    #[DataProvider('getRepeatedElementsProvider')]
    public function testGetRepeatedElements(array $array, array $expected): void
    {
        $this->assertEqualsCanonicalizing($expected, SchemaFromDatabaseUsingName::getRepeatedElements($array));
    }


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