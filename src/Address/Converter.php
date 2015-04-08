<?php
namespace Address;

use Address\Output\Output;

/**
 * Usage
 *
 *
 *
 * @package Address
 */
class Converter
{
    public static $sourcePath;

    public static function convert(Output $output)
    {
        self::convertSchema($output);
        self::convertData($output);
    }

    private static function convertSchema(Output $output)
    {
        $extension = 'xsd';

        /** @var $schemaFile \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $schemaFile) {
            if ($schemaFile->isDot() === true || $schemaFile->getExtension() !== $extension) {
                continue;
            }

            // Table name
            $baseName = $schemaFile->getBasename('.' . $extension);
            $tableName = self::extractTableName($baseName);

            // Prepare
            $schemaDocument = new \DOMDocument('1.0', 'UTF-8');
            $schemaDocument->load($schemaFile->getPathname());

            // Convert
            $output->handleSchemaFile($tableName, $schemaDocument);
        }
    }

    private static function convertData(Output $output)
    {
        $extension = 'XML';

        /** @var $dataFile \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $dataFile) {
            if ($dataFile->isDot() === true || $dataFile->getExtension() !== $extension) {
                continue;
            }

            // Table name
            $baseName = $dataFile->getBasename('.' . $extension);
            $tableName = self::extractTableName($baseName);

            // Счетчики
            $timerState = microtime(true);
            $parsingCurrent = 0;
            $depth = 0;

            // Prepare
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

            // Сам процесс
            $sourceFile = fopen($dataFile->getPathname(), 'r');
            while ($data = fread($sourceFile, 4096)) {
                xml_parse($xmlParser, $data, feof($sourceFile));
            }
            fclose($sourceFile);

            xml_parser_free($xmlParser);
        }
    }

    /**
     * Название файлов пока не понятно.
     * AS_ACTSTAT_2_250_08_04_01_01
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