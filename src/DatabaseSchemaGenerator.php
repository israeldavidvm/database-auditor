<?php

namespace Israeldavidvm\DatabaseAuditor;

abstract class DatabaseSchemaGenerator
{
    public $databaseAuditor;

    public function __construct($databaseAuditor) {
        $this->databaseAuditor = $databaseAuditor;
    }

    abstract public function generate();
}

?>