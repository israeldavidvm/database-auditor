<?php

namespace Israeldavidvm\DatabaseAuditor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

use Symfony\Component\Console\Helper\ProgressBar;


class MenuCommand extends Command
{

    protected $databaseAuditor;
    protected $helper;
    public static function generateCover(){
        return ''.
        '      ____            __             __                                   ___                __    _                       '.PHP_EOL.    
        '     / __ \  ____ _  / /_  ____ _   / /_   ____ _   _____  ___           /   |  __  __  ____/ /   (_)  / /_  ____    _____ '.PHP_EOL.                                                              
        '    / / / / / __ `/ / __/ / __ `/  / __ \ / __ `/  / ___/ / _ \ ______  / /| | / / / / / __  /   / /  / __/ / __ \  / ___/ '.PHP_EOL.                                                              
        '   / /_/ / / /_/ / / /_  / /_/ /  / /_/ // /_/ /  (__  ) /  __//_____/ / ___ |/ /_/ / / /_/ /   / /  / /_  / /_/ / / /     '.PHP_EOL.                                                              
        '  /_____/  \__,_/  \__/  \__,_/  /_.___/ \__,_/  /____/  \___/        /_/  |_|\__,_/  \__,_/   /_/   \__/  \____/ /_/      '.PHP_EOL.PHP_EOL.PHP_EOL.
        '                       Creado por israeldavidvm'.PHP_EOL.PHP_EOL.        
        '¿Quieres asegurarte de la calidad de tu base de datos?'.PHP_EOL.PHP_EOL.
        ' database-auditor te ayudara asegurar la calidad de tu base de datos'.
        ' de forma automática, cumplir con las mejores practicas, asegurar'.
        ' la integridad estructural, evitar redundancias y anomalías, validar'.
        ' formas normales, verificar la propiedad de concatenación no aditiva,'.
        ' analiza dependencias funcionales, etc.'
        .PHP_EOL.PHP_EOL.
        '¡Prueba database-auditor y asegura la calidad de tus bases de datos! '.PHP_EOL;
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
        /** @var QuestionHelper $this->helper */
        $this->helper = $this->getHelper('question');

        $question = new ChoiceQuestion(self::generateCover().
        'Por favor, selecciona una opción:',
            [
                'Analizar Bases de datos personalizada',
                'Analizar Bases de datos de ejemplo',
                'Salir',
            ],
            0 // Opción por defecto (el índice del array)
        );

        $question->setErrorMessage('Opción %s no es válida.');

        $seleccion = $this->helper->ask($input, $output, $question);

        $output->writeln('Has seleccionado: ' . $seleccion);

        switch ($seleccion) {
            case 'Analizar Bases de datos personalizada':
                $this->selectPersonalizedtDatabases($input,$output);
                break;
            case 'Analizar Bases de datos de ejemplo':
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
        
        /** @var QuestionHelper $this->helper */
        $this->helper = $this->getHelper('question');

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

        $seleccion = $this->helper->ask($input, $output, $question);

        $output->writeln('Has seleccionado: ' . $seleccion);

        $path = '';

        switch ($seleccion) {
            case 'Cargar datos de una base de datos usando las configuraciones de'.
                ' el archivo .env (SchemaFromDatabaseUsingName)':
                $schemaGenerator=SchemaFromDatabaseUsingName::class;
                $questionPath = new Question('Por favor, introduce la ruta al archivo .env: ');
                $path = $this->helper->ask($input, $output, $questionPath);
                if (empty($path)) {
                    $output->writeln('<error>La ruta del archivo no puede estar vacía.</error>');
                    return Command::FAILURE;
                }
                break;
            case 'Cargar datos de una base de datos usando las configuraciones de'.
                ' un archivo .json (SchemaFromJSON)':
                $schemaGenerator=SchemaFromJSON::class;
                $questionPath = new Question('Por favor, introduce la ruta al archivo .json: ');
                $path = $this->helper->ask($input, $output, $questionPath);
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

        $databaseAuditor = new DatabaseAuditor;

        $databaseSchemaGenerators[$schemaGenerator]= new $schemaGenerator(
            $databaseAuditor,
            $path
        );

        $this->appLogic($input,$output,$databaseAuditor,$databaseSchemaGenerators);
    }

    protected function selectTestDatabases($input, $output) 
    {
        // Aquí puedes implementar la lógica para probar bases de datos de ejemplo
        $output->writeln('Probando bases de datos de ejemplo...');                
        
        /** @var QuestionHelper $this->helper */
        $this->helper = $this->getHelper('question');

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

        $seleccion = $this->helper->ask($input, $output, $question);

        $output->writeln('Has seleccionado: ' . $seleccion);

        $path = '';

        switch ($seleccion) {
            case 'Base de datos que esta BCNF':
                $path = './jsonFilesDBExamples/BCNFExampleDB.json';
                break;
            case 'Base de datos que no esta en BCNF':
                $path = './jsonFilesDBExamples/notBCNFExampleDB.json';
                break;
            case 'Base de datos que posee la propiedad de concatenación sin pérdida (no aditiva)':
                $path = './jsonFilesDBExamples/notAditiveExampleDB.json';
                break;
            case 'Base de datos que no posee la propiedad de concatenación sin pérdida (no aditiva)':
                $path = './jsonFilesDBExamples/AditiveExampleDB.json';
                break;
            case 'Base de datos con entidades repetidas':
                $path='./jsonFilesDBExamples/RepeatedReferencedExampleDB.json';
                break;
            case 'Salir':
                $output->writeln('Saliendo del menú.');
                return Command::SUCCESS; // Indica una salida exitosa
            default:
                $output->writeln('<error>Opción inválida.</error>');
                return Command::FAILURE; // Indica un fallo
        }


        $databaseAuditor = new DatabaseAuditor;

        $databaseSchemaGenerators['SchemaFromJSON']= new SchemaFromJSON(
            $databaseAuditor,
            $path
        );

        $this->appLogic($input,$output,$databaseAuditor,$databaseSchemaGenerators);
            
    }


    protected function appLogic($input,$output,$databaseAuditor,$schemaGenerators){

        $databaseAuditor->databaseSchemaGenerators= $schemaGenerators;

        $databaseAuditor->generateDatabaseSchema();

        $databaseAuditor->validationAlgorithms['VerificationBCNF']= new VerificationBCNF($databaseAuditor);

        $databaseAuditor->validationAlgorithms['VerificationNonAdditiveConcatenation']= new VerificationNonAdditiveConcatenation($databaseAuditor);

        $databaseAuditor->executeValidationAlgorithm();

        $this->printResumeReport($output,$databaseAuditor->report);

        $this->createFileReportRequired($input,$output,$databaseAuditor->report);

    }

    protected function printResumeReport($output,$report){

        echo "Elementos totales(ET): ".$report->numScanElements().PHP_EOL;
        echo "Elementos en buen estado(EG): ".$report->numGoodStateElements().PHP_EOL;

        echo "EG/ET ";
        $progressBar = new ProgressBar($output,
            $report->numScanElements()
        );                   

        $progressBar->start(null, $report->numGoodStateElements());
        $output->writeln(PHP_EOL);                        

        $output->writeln(
            $report->reportResumeToString()
        );     

    }

    protected function createFileReportRequired($input,$output,$report){

        $createdFile=$this->createFile($input,$output,$report->reportToString());

        while(!$createdFile){
            $createdFile=$this->createFile($input,$output,$report->reportToString());
        }

    }
    
    protected function createFile($input,$output,$content){

        $questionPath = new Question('Por favor, introduce la ruta al archivo en el que quieres almacenar los resultados de forma detallada ');
        $path = $this->helper->ask($input, $output, $questionPath);
        if (empty($path)) {
            $output->writeln('<error>La ruta del archivo no puede estar vacía.</error>');
            return false;
        }

        $this->printToFile($path, $content);

        return true;
    }

    public function printToFile(string $filePath, string $content): void
{
    $directory = dirname($filePath);

    if (!is_dir($directory)) {
        if (!mkdir($directory, 0777, true)) {
            echo "Error al crear el directorio: " . $directory . PHP_EOL;
            return;
        }
    }

    if (file_put_contents($filePath, $content ) === false) {
        echo "Error al escribir en el archivo: " . $filePath . PHP_EOL;
    } else {
        // Opcional: Puedes agregar un mensaje indicando que se escribió en el archivo
        echo "Información agregada al archivo: " . $filePath . PHP_EOL;
    }
}
}