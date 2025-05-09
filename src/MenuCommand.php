<?php

namespace Israeldavidvm\DatabaseAuditor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

class MenuCommand extends Command
{

    protected $databaseAuditor;

    public static function generateCover(){
        return ''.
        '    / __ \  ____ _  / /_  ____ _          /   |  __  __  ____/ /   (_)  / /_  ____    _____ '.PHP_EOL.
        '   / / / / / __ `/ / __/ / __ `/ ______  / /| | / / / / / __  /   / /  / __/ / __ \  / ___/ '.PHP_EOL.
        '  / /_/ / / /_/ / / /_  / /_/ / /_____/ / ___ |/ /_/ / / /_/ /   / /  / /_  / /_/ / / /     '.PHP_EOL.
        ' /_____/  \__,_/  \__/  \__,_/         /_/  |_|\__,_/  \__,_/   /_/   \__/  \____/ /_/      '.PHP_EOL.PHP_EOL.PHP_EOL.
        '                         Creado por israeldavidvm'.PHP_EOL.PHP_EOL.        
        '¡Optimiza tu diseño de bases de datos con data-auditor!'.PHP_EOL.PHP_EOL.
        '¿Quieres asegurarte de que tu base de datos esté libre de redundancias, anomalías'.
        ' y problemas de diseño? Con data-auditor, obtén las herramientas necesarias para validar'.
        ' formas normales, analizar dependencias funcionales y garantizar un diseño robusto y eficiente.'.
        PHP_EOL.PHP_EOL. 
        ' ¡Prueba nuestra interfaz de línea de comandos y lleva tu base de datos al siguiente nivel!';  
    }

    protected function configure()
    {
        // Define el nombre del comando
        $this
            ->setName('menu')
            ->setDescription(
                'Muestra un menú de opciones interactivo.' 
            )

            ->setHelp('Este comando muestra un menú interactivo y ejecuta acciones basadas en la selección del usuario.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(self::generateCover().
        'Por favor, selecciona una opción:',
            [
                'Probar Bases de datos personalizada',
                'Probar bases de datos de ejemplo',
                'Salir',
            ],
            0 // Opción por defecto (el índice del array)
        );

        $question->setErrorMessage('Opción %s no es válida.');

        $seleccion = $helper->ask($input, $output, $question);

        $output->writeln('Has seleccionado: ' . $seleccion);

        switch ($seleccion) {
            case 'Probar Bases de datos personalizada':
                $this->selectPersonalizedtDatabases($input,$output);
                break;
            case 'Probar bases de datos de ejemplo':
                $this->selectTestDatabases($input, $output);                 
                break;
            case 'Salir':
                $output->writeln('Saliendo del menú.');
                return Command::SUCCESS; // Indica una salida exitosa
            default:
                $output->writeln('<error>Opción inválida.</error>');
                return Command::FAILURE; // Indica un fallo
        }

        // Si no se seleccionó "Salir", mostramos el menú de nuevo
        return $this->execute($input, $output);
    }

    protected function selectPersonalizedtDatabases($input, $output) 
    {
        // Aquí puedes implementar la lógica para probar bases de datos de ejemplo
        $output->writeln('Analizando bases de datos perzonalizada.');                
        
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Por favor, selecciona un mecanismo(DatabaseSchemaGenerator) para obtener los datos de la base de datos:',
            [
                'Cargar datos de una base de datos usando las configuraciones de'.
                ' el archivo .env (SchemaFromDatabaseUsingName)',
                'Cargar datos de una base de datos usando las configuraciones de'.
                ' un archivo .json (SchemaFromJSON)',
                'Salir',
            ],
            0 // Opción por defecto (el índice del array)
        );

        $question->setErrorMessage('Opción %s no es válida.');

        $seleccion = $helper->ask($input, $output, $question);

        $output->writeln('Has seleccionado: ' . $seleccion);

        $path = '';

        switch ($seleccion) {
            case 'Cargar datos de una base de datos usando las configuraciones de'.
                ' el archivo .env (SchemaFromDatabaseUsingName)':
                $schemaGenerator=SchemaFromDatabaseUsingName::class;
                $questionPath = new Question('Por favor, introduce la ruta al archivo .env: ');
                $path = $helper->ask($input, $output, $questionPath);
                if (empty($path)) {
                    $output->writeln('<error>La ruta del archivo no puede estar vacía.</error>');
                    return Command::FAILURE;
                }
                break;
            case 'Cargar datos de una base de datos usando las configuraciones de'.
                ' un archivo .json (SchemaFromJSON)':
                $schemaGenerator=SchemaFromJSON::class;
                $questionPath = new Question('Por favor, introduce la ruta al archivo .json: ');
                $path = $helper->ask($input, $output, $questionPath);
                if (empty($path)) {
                    $output->writeln('<error>La ruta del archivo no puede estar vacía.</error>');
                    return Command::FAILURE;
                }
                break;
            case 'Salir':
                $output->writeln('Saliendo del menú.');
                return Command::SUCCESS; // Indica una salida exitosa
            default:
                $output->writeln('<error>Opción inválida.</error>');
                return Command::FAILURE; // Indica un fallo
        }


        $this->databaseAuditor = new DatabaseAuditor;

        $this->databaseAuditor->databaseSchemaGenerators[$schemaGenerator]= new $schemaGenerator(
            $this->databaseAuditor,
            $path
        );

        $this->databaseAuditor->generateDatabaseSchema();

        $this->databaseAuditor->validationAlgorithms['VerificationBCNF']= new VerificationBCNF($this->databaseAuditor);

        $this->databaseAuditor->validationAlgorithms['VerificationNonAdditiveConcatenation']= new VerificationNonAdditiveConcatenation($this->databaseAuditor);

        $this->databaseAuditor->executeValidationAlgorithm();

        $this->databaseAuditor->printReport();

    }

    protected function selectTestDatabases($input, $output) 
    {
        // Aquí puedes implementar la lógica para probar bases de datos de ejemplo
        $output->writeln('Probando bases de datos de ejemplo...');                
        
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Por favor, selecciona una bases de datos que quieras probar:',
            [
                'Base de datos que esta BCNF',
                'Base de datos que no esta en BCNF',
                'Base de datos que posee la propiedad de concatenación sin pérdida (no aditiva)',
                'Base de datos que no posee la propiedad de concatenación sin pérdida (no aditiva)',
                'Base de datos con entidades repetidas',
                'Salir',
            ],
            0 // Opción por defecto (el índice del array)
        );

        $question->setErrorMessage('Opción %s no es válida.');

        $seleccion = $helper->ask($input, $output, $question);

        $output->writeln('Has seleccionado: ' . $seleccion);

        $path = '';

        switch ($seleccion) {
            case 'Base de datos que esta BCNF':
                $path = './BCNFExampleDB.json';
                break;
            case 'Base de datos que no esta en BCNF':
                $path = './notBCNFExampleDB.json';
                break;
            case 'Base de datos que posee la propiedad de concatenación sin pérdida (no aditiva)':
                $path = './notAditiveExampleDB.json';
                break;
            case 'Base de datos que no posee la propiedad de concatenación sin pérdida (no aditiva)':
                $path = './AditiveExampleDB.json';
                break;
            case 'Base de datos con entidades repetidas':
                $path='./RepeatedReferencedExampleDB.json';
                break;
            case 'Salir':
                $output->writeln('Saliendo del menú.');
                return Command::SUCCESS; // Indica una salida exitosa
            default:
                $output->writeln('<error>Opción inválida.</error>');
                return Command::FAILURE; // Indica un fallo
        }


        $this->databaseAuditor = new DatabaseAuditor;

        $this->databaseAuditor->databaseSchemaGenerators['SchemaFromJSON']= new SchemaFromJSON(
            $this->databaseAuditor,
            $path
        );

        $this->databaseAuditor->generateDatabaseSchema();

        $this->databaseAuditor->validationAlgorithms['VerificationBCNF']= new VerificationBCNF($this->databaseAuditor);

        $this->databaseAuditor->validationAlgorithms['VerificationNonAdditiveConcatenation']= new VerificationNonAdditiveConcatenation($this->databaseAuditor);

        $this->databaseAuditor->executeValidationAlgorithm();

        $this->databaseAuditor->printReport();

    }
}