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
        $this->setName('audit-database')
            ->setDescription(
                'Este comando te permite realizar una serie de validaciones' .
                'en tu base de datos redirige la salida para pasar la informacion a un archivo ' 
            )
            ->setHelp(
                'Este comando te permite realizar una serie de validaciones' .
                'en tu base de datos redirige la salida para pasar la informacion a un archivo ' 
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
                '<databaseSchemaGenerator>|<path>'.
                'Donde'.
                '<databaseSchemaGenerator>::=SchemaFromDatabaseUsingName|SchemaFromJSON '.
                'Es decir el Valor del tipo de generador de esquema de base de datos'.
                '<path>'.
                'Es la ruta al archivo .json en caso de SchemeFromJson'.
                ' o la ruta al archivo .env en el caso de SchemaFromDatabaseUsingName'
                ,
                'SchemaFromDatabaseUsingName'.
                '|./.env'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $this->databaseAuditor = new DatabaseAuditor;

        $this->selectDatabaseSchemaGenerator($input,$output);

        $this->selectValidationAlgorithms($input);

        $this->databaseAuditor->generateDatabaseSchema();

        $this->databaseAuditor->executeValidationAlgorithm();

        $this->databaseAuditor->printReport();

        return Command::SUCCESS;
    }

    public function selectDatabaseSchemaGenerator(InputInterface $input, OutputInterface $output){

        $databaseSchemaGeneratorConfig= explode(
            '|', 
            $input->getArgument('databaseSchemaGeneratorConfig'),
            2
        );
        
        $databaseSchemaGenerator = $databaseSchemaGeneratorConfig[0];
        $path = $databaseSchemaGeneratorConfig[1];

        try {
            if($databaseSchemaGenerator=='SchemaFromDatabaseUsingName'){
               
                $this->databaseAuditor->databaseSchemaGenerators['SchemaFromDatabaseUsingName']= new SchemaFromDatabaseUsingName(
                    $this->databaseAuditor,
                    $path
                );    
           
            }else {
    
                $this->databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= new SchemaFromJSON(
                    $this->databaseAuditor,
                    $path
                );
            }        
        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>\n");
            return Command::FAILURE;
        }

    
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