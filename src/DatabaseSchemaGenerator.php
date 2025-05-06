<?php

namespace Israeldavidvm\DatabaseAuditor;

abstract class DatabaseSchemaGenerator
{
    public DatabaseAuditor $databaseAuditor;

    abstract public function generate();
}

?>