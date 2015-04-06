<?php
namespace Address;

use Address\Formatter\FormatterInterface;
use Address\Loader\LoaderInterface;
use Address\Output\OutputInterface;

/**
 * Usage
 *
 *
 *
 * @package Address
 */
class Converter
{
    public static function convert(LoaderInterface $loader, FormatterInterface $formatter, OutputInterface $output)
    {
        /*foreach ($loader->getSchemaFiles('xsd') as $schemaPathname) {
            $conversionResult = $formatter->handleSchemaFile($schemaPathname);
            $output->recieveSchemaResult($conversionResult);
        }*/

        self::convertSchema($loader, $formatter, $output);
        self::convertData($loader, $formatter, $output);
    }

    private static function convertSchema(LoaderInterface $loader, FormatterInterface $formatter, OutputInterface $output)
    {
        $extension = 'xsd';

        /** @var $schemaFile \DirectoryIterator */
        foreach (new \DirectoryIterator($loader::$sourcePath) as $schemaFile) {
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
            $conversionResult = $formatter->handleSchemaFile($tableName, $schemaDocument);
            $output->receiveSchemaResult($conversionResult);
        }
    }

    private static function convertData(LoaderInterface $loader, FormatterInterface $formatter, OutputInterface $output)
    {
        $extension = 'XML';

        /** @var $dataFile \DirectoryIterator */
        foreach (new \DirectoryIterator($loader::$sourcePath) as $dataFile) {
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
                function ($parser, $tagName, $fields) use ($tableName, $formatter, $output, &$parsingCurrent, &$depth, &$timerState) {

                    // Пропускаем родительский элемент
                    if ($depth === 0) {
                        return $depth += 1; // псевдо-дерево
                    }

                    $depth += 1;
                    $parsingCurrent += 1;

                    // Форматируем под нужный синтаксис
                    $sqlQuery = $formatter->handleDataFile($tableName, $fields);
                    $output->receiveDataResult($sqlQuery);

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

    public static function _convertSchema()
    {
        $schemaFilename = self::$outputPath . '/mysql-schema.sql';
        $schemaFile = fopen($schemaFilename, 'c');

        // Шаблон конвертации
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load(self::$resourcesPath . '/mysql-schema.xsl');
        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet($template);

        $extension = 'xsd';

        /** @var $file \DirectoryIterator */
        foreach (new \DirectoryIterator(self::$sourcePath) as $file) {
            if ($file->isDot() === true || $file->getExtension() !== $extension) {
                continue;
            }

            $xsdDefinition = new \DOMDocument('1.0', 'UTF-8');
            $xsdDefinition->load($file->getPathname());

            // Table name
            $baseName = $file->getBasename('.' . $extension);
            $tableName = self::extractTableName($baseName);
            $xslt->setParameter('', 'tableName', $tableName);

            $sqlDefinition = $xslt->transformToXml($xsdDefinition);
            fwrite($schemaFile, $sqlDefinition);
        }

        fclose($schemaFile);
    }

    public static function _convertData()
    {
        $dumpFilepath = self::$outputPath . '/mysql-dump.sql';
        $dumpFile = fopen($dumpFilepath, 'c');

        $tableName = 'ADDROBJ';
        $sourceFilepath = self::$sourcePath . '/AS_ADDROBJ_20150329_442f999e-f4f0-4968-97f7-2365699fa64a.XML';

        // Счетчики
        $timerState = microtime(true);
        $parsingCurrent = 0;
        $depth = 0;

        $xmlParser = xml_parser_create();
        xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, false);

        xml_set_element_handler(
            $xmlParser,
            function ($parser, $tagName, $fields) use ($tableName, &$dumpFile, &$parsingCurrent, &$depth, &$timerState) {

                // Пропускаем родительский элемент
                if ($depth === 0) {
                    return $depth += 1;
                }

                $depth += 1;

                // Форматируем под нужный синтаксис
                $queryTemplate = "INSERT INTO `%s` (`%s`) VALUES ('%s');" . PHP_EOL;
                $sqlQuery = sprintf($queryTemplate, $tableName, implode("`, `", array_keys($fields)), implode("', '", $fields));
                fwrite($dumpFile, $sqlQuery);

                // Вывод технической информации
                $parsingCurrent += 1;

                $packet = 10000;
                $averageAmount = 10;

                if (($parsingCurrent % $packet) === 0) {
                    echo $parsingCurrent;

                    if (($parsingCurrent % $packet * $averageAmount) === 0) {
                        echo ' AVG: ' . round((microtime(true) - $timerState) / $averageAmount, 2) . 's';
                        $timerState = microtime(true);
                    }

                    echo PHP_EOL;
                }
            },
            function ($parser, $tagName) use (&$depth) {
                $depth -= 1;
            }
        );

        // Сам процесс
        $sourceFile = fopen($sourceFilepath, 'r');
        while ($data = fread($sourceFile, 4096)) {
            xml_parse($xmlParser, $data, feof($sourceFile));
        }

        xml_parser_free($xmlParser);
        fclose($sourceFile);
        fclose($dumpFile);
    }

    public static function convertData2()
    {
        $dumpFilename = self::$outputPath . '/mysql-dump.sql';
        $dumpFile = fopen($dumpFilename, 'c');

        $tableName = 'ADDROBJ';

        $file = self::$sourcePath . '/AS_ADDROBJ_20150329_442f999e-f4f0-4968-97f7-2365699fa64a.XML';
        $reader = new \XMLReader();
        $reader->open($file, 'UTF-8');

        // Входим в documentElement
        $reader->next();

        $parsingCurrent = 0;

        while ($reader->read()) {
            $fields = array();

            $attrCount = $reader->attributeCount;
            for ($i = 0; $i < $attrCount; $i++) {
                $reader->moveToAttributeNo($i);
                $fields[$reader->name] = $reader->value;
            }

            $queryTemplate = "INSERT INTO `%s` (`%s`) VALUES ('%s');" . PHP_EOL;
            $sqlQuery = sprintf($queryTemplate, $tableName, implode("`, `", array_keys($fields)), implode("', '", $fields));

            fwrite($dumpFile, $sqlQuery);

            $parsingCurrent += 1;

            if (($parsingCurrent % 10000) === 0) {
                echo $parsingCurrent . PHP_EOL;
            }
        }

        fclose($dumpFile);
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