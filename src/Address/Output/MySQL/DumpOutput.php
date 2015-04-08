<?php
namespace Address\Output\MySQL;

use Address\Output\Output;

class DumpOutput extends Output
{
    private $schemaFile;
    private $dumpFile;

    public function __construct()
    {
        // Шаблон конвертации Схемы
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load(self::$resourcesPath . '/mysql-schema.xsl');
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);

        // Схема и дамп в виде файлов
        $schemaPathname = self::$outputPath . '/mysql-schema.sql';
        $this->schemaFile = fopen($schemaPathname, 'c');
        $dumpPathname = self::$outputPath . '/mysql-dump.sql';
        $this->dumpFile = fopen($dumpPathname, 'c');
    }

    public function handleSchemaFile($tableName, \DOMDocument $schemaDocument)
    {
        $this->xslt->setParameter('', 'tableName', $tableName);
        $sqlDefinition = $this->xslt->transformToXml($schemaDocument);
        $this->receiveSchemaResult($sqlDefinition);
    }

    public function handleData($tableName, array $fields)
    {
        $queryTemplate = "INSERT INTO `%s` (`%s`) VALUES ('%s');" . PHP_EOL;
        $sqlQuery = sprintf($queryTemplate, $tableName, implode("`, `", array_keys($fields)), implode("', '", $fields));
        $this->receiveDataResult($sqlQuery);
    }

    private function receiveSchemaResult($conversionResult)
    {
        fwrite($this->schemaFile, $conversionResult);
    }

    private function receiveDataResult($conversionResult)
    {
        fwrite($this->dumpFile, $conversionResult);
    }
}