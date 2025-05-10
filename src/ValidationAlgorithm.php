<?php

namespace Israeldavidvm\DatabaseAuditor;

abstract class ValidationAlgorithm
{
    public $databaseAuditor;

    public static $posibleResults=[];

    public function __construct($databaseAuditor) {
        $this->databaseAuditor = $databaseAuditor;
    }

    public static function explainPossibleResults(): string
    {
        
        $str='';

        foreach(static::class::$posibleResults as $result){
            $str.=$result.':'.static::class::explainResult($result);
        }

        return $str;

    }

    abstract public static function explainResult($result): string;

    abstract static function isGoodResult($result);

    abstract public function execute();
}

?>