<?php

namespace Israeldavidvm\DatabaseAuditor;

abstract class ValidationAlgorithm
{
    public $databaseAuditor;

    public function __construct($databaseAuditor) {
        $this->databaseAuditor = $databaseAuditor;
    }

    abstract public function execute();
}

?>