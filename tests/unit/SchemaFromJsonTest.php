<?php

namespace Israeldavidvm\DatabaseAuditor\Tests;

use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\SchemaFromJSON;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing;
use Israeldavidvm\DatabaseAuditor\Schema;
use Israeldavidvm\DatabaseAuditor\Report;


#[CoversClass(SchemaFromJSON::class)]
#[UsesClass(DatabaseAuditor::class)]
#[UsesClass(Schema::class)]
#[UsesClass(Report::class)]
class SchemaFromJSONTest extends TestCase
{

    public function testGenerate()
    {
        $databaseAuditor = new DatabaseAuditor;

        $pathJson = __DIR__ . '/../../jsonFilesDBExamples/BCNFExampleDB.json';
        $schemaFromJson = new SchemaFromJSON($databaseAuditor, $pathJson);
        $schemaFromJson->generate();

        $baseSchema=$databaseAuditor->baseSchema;

        $this->assertNotEmpty($baseSchema->universalRelationship);
        $this->assertNotEmpty($baseSchema->decompositionsByTable);
        $this->assertNotEmpty($baseSchema->functionalDependencies);
    }
}