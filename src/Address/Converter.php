<?php
namespace Address;

use Address\Output\Output;

/**
 * Обработка XML базы ФИАС, экспорт в MySQL и другие форматы.
 * http://fias.nalog.ru/
 *
 * Использование: см. README.md
 *
 * @package Address
 */
class Converter extends Console
{	
    /**
     * @var string
     */
    private $importPath = '';
    
    /**
     * @var string
     */
    private $outputPath = '';
	/**
	 * @var string
	 */
	private $schemaExtension = 'xsd';
	
	/**
	 * @var string
	 */
	private $dataExtension = 'xml';
	
    /**
     * @var array
     */
    private $tableList = array(
        'ACTSTAT',
        /*'ADDROBJ',
        'CENTERST',
        'CURENTST',
        'ESTSTAT',
        'HOUSE',
        'HOUSEINT',
        'HSTSTAT',
        'INTVSTAT',
        'LANDMARK',
        'NDOCTYPE',
        'NORMDOC',
        'OPERSTAT',
        'SOCRBASE',
        'STRSTAT'*/
    );

    public function __construct($importPath, $outputPath)
    {
        // todo check em
        $this->importPath = $importPath;
        $this->outputPath = $outputPath;
    }

	/**
	 * Выполнить конвертацию в заданный формат.
	 * 
	 */
    public function convert(Formatter\FormatterInterface $formatter)
    {
        $this->output('Get latest release @ https://github.com/shadz3rg/address-db-converter', false);

        foreach ($this->tableList as $tableName) {
            
            $this->output('+ Table Schema ' . $tableName);

            $schemaFile = $this->importPath . '/' . $tableName . '.' . $this->schemaExtension;
            $schema = new Loader\TableSchema($tableName, $schemaFile);

            $outputFile = $this->outputPath . '/' . $tableName . '.sql';
            $schema->convertAndDump($formatter, $outputFile);

            $this->output('...Done. ' , false);

            //
            $this->output('+ Table Data ' . $tableName);
            $tableFields = $schema->getTableFields();

            $dataFile = $this->importPath . '/' . $tableName . '.' . $this->dataExtension;
            $tableData = new Loader\TableData($tableName, $tableFields, $dataFile);

            $outputFile = $this->outputPath . '/' . $tableName . '.data.sql';
            $tableData->convertAndDump($formatter, $outputFile);
            $this->output('...Done. ' , false);
/*
            $tableFields = $schema->getTableFields();
            $schema->applyKeys();
            $schema->convertAndDump($schemaFIleOutput);

            $dataFile = self::$importFiles . '/' . $tableName . '.' . self::$dataExtension;
            $tableData = new TableData($tableName, $tableFields, $dataFile);
            $schema->convertAndDump($schemaFIleOutput);*/
        }
    }

    public static function prepareFilenames($importPath)
    {
        /** @var $schemaFile \DirectoryIterator */
        foreach (new \DirectoryIterator($importPath) as $currentFile) {
            if ($currentFile->isDot() === true) {
                continue;
            }
        // todo test
            $fileName = $currentFile->getFilename();
            $tableName = self::extractTableName($fileName);

            if ($tableName !== null) {
                $oldname = $currentFile->getPathname();
                $newname = $currentFile->getPath() . '/' . $tableName . '.' . $currentFile->getExtension();
                rename($oldname, $newname);
            }
        }
    }
	
	/**
	 * Обработка файлов схемы.
	 *
	 */
    public static function convertSchema(Output $output)
    {
        /** @var $schemaFile \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $schemaFile) {
            if ($schemaFile->isDot() === true || mb_strtolower($schemaFile->getExtension()) !== self::$schemaExtension) {
                continue;
            }

            // Имя таблички
            $baseName = $schemaFile->getBasename('.' . self::$schemaExtension);
            $tableName = self::extractTableName($baseName);

            // Подготовим исходный файл
            $schemaDocument = new \DOMDocument('1.0', 'UTF-8');
            $schemaDocument->load($schemaFile->getPathname());

            $output->handleSchemaFile($tableName, $schemaDocument);
        }
    }

	/**
	 * Обработка файлов данных.
	 *
	 */
    private static function convertData(Output $output)
    {
        /** @var $dataFile \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $dataFile) {
            if ($dataFile->isDot() === true || mb_strtolower($dataFile->getExtension()) !== self::$dataExtension) {
                continue;
            }

            // Имя таблички
            $baseName = $dataFile->getBasename('.' . self::$dataExtension);
            $tableName = self::extractTableName($baseName);

            // Счетчики
            $timerState = microtime(true);
            $parsingCurrent = 0;
            $depth = 0;

            // Настройка парсинга
            $xmlParser = xml_parser_create();
            xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, false);
            xml_set_element_handler(
                $xmlParser,
                function ($parser, $tagName, $fields) use ($tableName, $output, &$parsingCurrent, &$depth, &$timerState) {

                    // Пропускаем родительский элемент
                    if ($depth === 0) {
                        return $depth += 1; // псевдо-дерево
                    }

                    $depth += 1;
                    $parsingCurrent += 1;

                    // Форматируем под нужный синтаксис
                    $output->handleData($tableName, $fields);

                    // Вывод технической информации
                    $packet = 10000;
                    $averageAmount = 10;

                    if (($parsingCurrent % $packet) === 0) {
                        echo $parsingCurrent;

                        if ($parsingCurrent % ($packet * $averageAmount) === 0) {
                            echo ' AVG: ' . round((microtime(true) - $timerState) / $averageAmount, 2) . 's';
                            $timerState = microtime(true);
                        }

                        echo PHP_EOL;
                    }
                },
                function ($parser, $tagName) use (&$depth) {
                    $depth -= 1; // псевдо-дерево
                }
            );

            // Парсинг напрямую из файла
            $sourceFile = fopen($dataFile->getPathname(), 'r');
            while ($data = fread($sourceFile, 4096)) {
                xml_parse($xmlParser, $data, feof($sourceFile));
            }
            fclose($sourceFile);

            xml_parser_free($xmlParser);
        }
    }

    public static function convertIndex(Output $output)
    {
        $output->handleIndex();
    }

    /**
     * Получаем название таблички из имени файла, магия.
     * пример: AS_ACTSTAT_2_250_08_04_01_01
     * ожидаем результат: ACTSTAT
     *
     * @param $baseName
     * @return mixed
     */
    private static function extractTableName($baseName)
    {
        $baseNameParts = explode('_', $baseName);

        if (isset($baseNameParts[1]) === true) {
            return $baseNameParts[1];
        }

        return null;
    }
}