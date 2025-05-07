<?php

namespace Israeldavidvm\DatabaseAuditor\Tests;

use PDOException;
use Dotenv\Dotenv;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing;

use Israeldavidvm\DatabaseAuditor\SchemaFromJSON;

use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\VerificationBCNF;
use Israeldavidvm\DatabaseAuditor\SchemaFromDatabaseUsingName;
use Israeldavidvm\DatabaseAuditor\Schema;
use Israeldavidvm\DatabaseAuditor\Report;


#[CoversClass(VerificationBCNF::class)]
#[UsesClass(Schema::class)]
#[UsesClass(DatabaseAuditor::class)]
#[UsesClass(SchemaFromJSON::class)]
#[UsesClass(Report::class)]
#[UsesClass(VerificationBCNF::class)]


class VerificationBCNFTest extends TestCase
{
    public $pdo;

    public function testExecuteWithTableInBCNF(): void
    {
        $expectedResult = [
            'asignaciones' => [
                'result' => 'BCNF',
            ],
            'empleados' => [
                'result' => 'BCNF',
            ],
            'proyectos' => [
                'result' => 'BCNF',
            ]
        ];

        $databaseAuditor = new DatabaseAuditor;
    
        $databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= 
            new SchemaFromJSON(
                $databaseAuditor, __DIR__ . '/../../BCNFExampleDB.json'
            );

        $databaseAuditor->validationAlgorithms['VerificationBCNF'] = new VerificationBCNF($databaseAuditor);
        
        $databaseAuditor->generateDatabaseSchema();

        $databaseAuditor->executeValidationAlgorithm();
        
        $result = [];

        foreach ($databaseAuditor->report->verificationList as $key => $value) {
            $result[$key] = ['result' => $value['result']];
        }

        $this->assertEqualsCanonicalizing($expectedResult, $result);
    }

    public function testExecuteWithTableInNotBCNF(): void
    {
        $expectedResult = [
            'asignaciones' => [
                'result' => 'BCNF',
            ],
            'empleados' => [
                'result' => 'BCNF',
            ],
            'proyectos' => [
                'result' => 'NotBCNF',
            ]
        ];

        $databaseAuditor = new DatabaseAuditor;
    
        $databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= 
            new SchemaFromJSON(
                $databaseAuditor, __DIR__ . '/../../notBCNFExampleDB.json'
            );

        $databaseAuditor->validationAlgorithms['VerificationBCNF'] = new VerificationBCNF($databaseAuditor);
        
        $databaseAuditor->generateDatabaseSchema();

        $databaseAuditor->executeValidationAlgorithm();

        $result = [];

        foreach ($databaseAuditor->report->verificationList as $key => $value) {
            $result[$key] = ['result' => $value['result']];
        }

        $this->assertEqualsCanonicalizing($expectedResult, $result);

    }

    protected function innitDBConnection() : void
    {
        if($this->pdo){
            return;
        }
        // Cargar las variables de entorno desde el archivo .env

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

        $this->innitDBConnection();

        try {
             // Definición de la estructura de la primera tabla: empleados
             $tablaHelados = 'CREATE TABLE IF NOT EXISTS "helados" (
                "nombre" VARCHAR(255) NOT NULL,
                "descripcion" VARCHAR(255)
            )';
        
            // Ejecutar la consulta para crear la tabla empleados
            $this->pdo->exec($tablaHelados);

           
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
}