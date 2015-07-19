<?php
namespace Address\Formatter;

class MySQLFormatter extends Formatter
{
    private $resourcesPath = '';
    private $xslt;

    public function __construct($resourcesPath)
    {
        $this->resourcesPath = $resourcesPath;

        // Шаблон конвертации
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($this->resourcesPath . '/mysql-schema.xsl');
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);
    }

    public function handleSchemaFile($tableName, \DOMDocument $schemaDocument)
    {
        $this->xslt->setParameter('', 'tableName', $tableName);
        $sqlDefinition = $this->xslt->transformToXml($schemaDocument);

        return $sqlDefinition;
    }

    public function handleDataFile($tableName, array $fields)
    {
        $queryTemplate = "INSERT INTO `%s` (`%s`) VALUES ('%s');" . PHP_EOL;
        return sprintf($queryTemplate, $tableName, implode("`, `", array_keys($fields)), implode("', '", $fields));
    }
}