<?php

include __DIR__."/vendor/autoload.php";

use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\VerificationBCNF;
use Israeldavidvm\DatabaseAuditor\VerificationNonAdditiveConcatenation;
use Israeldavidvm\DatabaseAuditor\SchemaFromDatabaseUsingName;
use Israeldavidvm\DatabaseAuditor\SchemaFromJSON;


$metaInfoEnvFile=[
    'pathEnvFolder'=>'.',
    'name'=>'.env'
];

// $metaInfoClustersTables=[
//     [
//         'mode'=>'include',
//         'tables'=> [
//             'taxonomies',
//             'source_taxonomy',
//             'sources',
//         ]
//     ],
//     [
//         'mode'=>'include',
//         'tables'=> [
//             'sources',
//             'source_user',
//             'users'
//         ]
//     ]
//     // [
//     //     'mode'=>'exclude',
//     //     'tables'=> [
//     //         'migrations',
//     //         'taxonomy_taxonomy',
//     //         'experiences',
//     //         'password_resets',
//     //         'failed_jobs',
//     //         'personal_access_tokens'
//     //     ]
//     // ]
// ];

$databaseAuditor = new DatabaseAuditor;

//$databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= new SchemaFromJSON($databaseAuditor,'./databaseInfo.json');

$databaseAuditor->databaseSchemaGenerators['SchemaFromDatabaseUsingName']= new SchemaFromDatabaseUsingName(
    $databaseAuditor,
    $metaInfoEnvFile,
    [
        'mode'=>'exclude',
        'tables'=> [
            'migrations',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
            'taxonomy_taxonomy',
        ]
    ]
);

$databaseAuditor->validationAlgorithms['VerificationBCNF']= new VerificationBCNF($databaseAuditor);;

$databaseAuditor->validationAlgorithms['VerificationNonAdditiveConcatenation']= new VerificationNonAdditiveConcatenation($databaseAuditor);;


// var_dump($metaInfoClustersTables);
$databaseAuditor->generateDatabaseSchema();

$databaseAuditor->executeValidationAlgorithm();