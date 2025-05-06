<?php

namespace Israeldavidvm\DatabaseAuditor\Tests;

use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\SchemaFromJSON;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversClass(SchemaFromJSON::class)]
#[UsesClass(DatabaseAuditor::class)]
class SchemaFromJSONTest extends TestCase
{

    public function testGenerate()
    {
        $databaseAuditor = new DatabaseAuditor;

        $pathJson = __DIR__ . '/../../BCNFExampleDB.json';
        $schemaFromJson = new SchemaFromJSON($databaseAuditor, $pathJson);
        $schemaFromJson->generate();

        $this->assertNotEmpty($databaseAuditor->universalRelationship);
        $this->assertNotEmpty($databaseAuditor->decompositionsByTable);
        $this->assertNotEmpty($databaseAuditor->functionalDependencies);
    }
}