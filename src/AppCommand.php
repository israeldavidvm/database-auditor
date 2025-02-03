<?php

namespace Israeldavidvm\DatabaseAuditor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument; // Para agregar un argumento

use Israeldavidvm\DatabaseAuditor\DatabaseAuditor;
use Israeldavidvm\DatabaseAuditor\VerificationBCNF;
use Israeldavidvm\DatabaseAuditor\VerificationNonAdditiveConcatenation;
use Israeldavidvm\DatabaseAuditor\SchemaFromDatabaseUsingName;
use Israeldavidvm\DatabaseAuditor\SchemaFromJSON;

class AppCommand extends Command
{

    protected $databaseAuditor;

    protected function configure()
    {
        // Define el nombre del comando
        $this->setName('app:audit-database')
            ->setDescription(
                'Este comando te permite generar una documentacion ' .
                'de tu base de datos postgresql en un archivo llamado ' .
                'db-documentation.md'
            )
            ->setHelp('Este comando te permite generar una documentacion ' .
                'de tu base de datos postgresql en un archivo llamado ' .
                'db-documentation.md'
            )
            ->addArgument(
                'validationAlgorithms', 
                InputArgument::OPTIONAL, 
                'Valor de los tipos de algoritmo de validacion a aplicar separados por coma (,) Ejemplo VerificationBCNF,VerificationNonAdditiveConcatenation', 
                'VerificationBCNF,VerificationNonAdditiveConcatenation'
            ) 
            ->addArgument(
                'databaseSchemaGeneratorConfig', 
                InputArgument::OPTIONAL, 
                'Cadena que especifica el databaseSchemaGenerator y su configuracion'.
                'Donde la cadena tiene un formato como el siguiente'.
                '<databaseSchemaGenerator>|<config>'.
                'Donde <databaseSchemaGenerator> '.
                'es el Valor del tipo de generador de esquema de base de datos'.
                'como por ejemplo SchemaFromDatabaseUsingName'. 
                'y <config> es la configuracion del generador'.
                ' de esquema de base de datos que depemndera'.
                ' del tipo para el caso de SchemaFromDatabaseUsingName'.
                ' tiene el formato <mode>|<tables>|<path>',
                'SchemaFromDatabaseUsingName'.
                '|exclude'.
                '|users,migrations,password_resets,failed_jobs,personal_access_tokens,'
                .'taxonomy_taxonomy'.
                '|./.env'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $this->databaseAuditor = new DatabaseAuditor;

        $this->selectDatabaseSchemaGenerator($input);

        $this->selectValidationAlgorithms($input);

        // var_dump($metaInfoClustersTables);
        $this->databaseAuditor->generateDatabaseSchema();

        $this->databaseAuditor->executeValidationAlgorithm();

        // Mostrar mensaje de éxito
        // $output->writeln('Se ha ejecutado los algoritmos de validacion');

        // Devolver un código de estado (éxito)
        return Command::SUCCESS;
    }

    public function selectDatabaseSchemaGenerator(InputInterface $input){

        $databaseSchemaGeneratorConfig= explode(
            '|', 
            $input->getArgument('databaseSchemaGeneratorConfig'),
            2
        );
        
        $databaseSchemaGenerator = $databaseSchemaGeneratorConfig[0];
        $config = $databaseSchemaGeneratorConfig[1];

        if($databaseSchemaGenerator=='SchemaFromDatabaseUsingName'){
            $config = explode('|', $config);

            // Obtener la ruta proporcionada por el usuario
            $path = $config[2];
            
            if (!file_exists($path)) {
                $output->writeln("<error>El archivo .env en la ruta proporcionada no existe.</error>\n");
                return Command::FAILURE;
            }

            // Obtener la ruta del directorio que contiene el archivo
            $directoryPath = dirname($path);

            // Obtener el nombre del archivo
            $fileName = basename($path);

            // Crear la configuración para DatabaseKnowledgeable
            $metaInfoEnvFile = [
                'pathEnvFolder' => $directoryPath,
                'name' => $fileName,
            ];

            $tables= explode(',', $config[1]);

            $this->databaseAuditor->databaseSchemaGenerators['SchemaFromDatabaseUsingName']= new SchemaFromDatabaseUsingName(
                $this->databaseAuditor,
                $metaInfoEnvFile,
                [
                    'mode'=>$config[0],
                    'tables'=> $tables
                ]
            );

            //tables => 'migrations,password_resets,failed_jobs,personal_access_tokens,taxonomy_taxonomy',

       
        }


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


    //$databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= new SchemaFromJSON($databaseAuditor,'./databaseInfo.json');

    
    }

    public function selectValidationAlgorithms(InputInterface $input){

        $validationAlgorithmNames = explode(',', $input->getArgument('validationAlgorithms'));

        foreach($validationAlgorithmNames as $validationAlgorithmName){
            
            $this->addValidationAlgorithm($validationAlgorithmName);

        }


    
    }

    public function addValidationAlgorithm($validationAlgorithmName){
        if($validationAlgorithmName=='VerificationBCNF'){
            $this->databaseAuditor->validationAlgorithms[$validationAlgorithmName]= new VerificationBCNF($this->databaseAuditor);
        }elseif ($validationAlgorithmName=='VerificationNonAdditiveConcatenation') {
            $this->databaseAuditor->validationAlgorithms[$validationAlgorithmName]= new VerificationNonAdditiveConcatenation($this->databaseAuditor);
        }
    }
}