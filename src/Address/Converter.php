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
class Converter
{
	/**
	 * @var string
	 */
    public static $sourcePath = '';
	
	/**
	 * @var string
	 */
	public static $schemaExtension = 'xsd';
	
	/**
	 * @var string
	 */
	public static $dataExtension = 'xml';
	
	/**
	 * Выполнить конвертацию в заданный формат.
	 * 
	 */
    public static function convert(Output $output)
    {
        self::convertSchema($output);
        self::convertData($output);
    }
	
	/**
	 * Обработка файлов схемы.
	 *
	 */
    private static function convertSchema(Output $output)
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
     *
     * @param $baseName
     * @return mixed
     */
    private static function extractTableName($baseName)
    {
        $baseNameParts = explode('_', $baseName);
        return $baseNameParts[1];
    }
}