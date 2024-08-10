<?php

namespace Israeldavidvm\DatabaseAuditor;

abstract class DatabaseSchemaGenerator
{
    public DatabaseAuditor $databaseAuditor;

    public function __construct(DatabaseAuditor $databaseAuditor) {
        $this->databaseAuditor = $databaseAuditor;
    }

    abstract public function generate();
}

?>