<?php
namespace Address\Output\MySQL;

use Address\Output\Output;

class DumpOutput extends Output
{
	/**
	 * Ресурс под дамп схемы.
	 * @var resource
	 */
    private $schemaFile;
	
	/**
	 * Ресурс под дамп данных.
	 * @var resource
	 */
    private $dumpFile;
	
	/**
	 * Ресурс под дамп данных.
	 * @var resource
	 */
    private $indexFile;


	/**
	 * Ресурс под дамп схемы.
	 * @var resource
	 */
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

        $indexPathname = self::$outputPath . '/mysql-index.sql';
        $this->indexFile = fopen($indexPathname, 'c');
		
		$header = $this->composeHeader();
		fwrite($this->schemaFile, $header);
		fwrite($this->dumpFile, $header);
		fwrite($this->indexFile, $header);
    }

	/**
	 * @inheritdoc
	 */
    public function handleSchemaFile($tableName, \DOMDocument $schemaDocument)
    {
        $this->xslt->setParameter('', 'tableName', $tableName);
        $conversionResult = $this->xslt->transformToXml($schemaDocument);
        fwrite($this->schemaFile, $conversionResult);
    }

	/**
	 * @inheritdoc
	 */
    public function handleDataRow($tableName, array $fields)
    {
        $queryTemplate = "INSERT INTO `%s` (`%s`) VALUES ('%s');" . PHP_EOL;
        $conversionResult = sprintf($queryTemplate, $tableName, implode("`, `", array_keys($fields)), implode("', '", $fields));
        fwrite($this->dumpFile, $conversionResult);
    }

    public function handleIndex()
    {
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load(self::$resourcesPath . '/mysql-index.xsl');
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);

		$indexDocument = new \DOMDocument('1.0', 'UTF-8');
		$indexDocument->preserveWhiteSpace = false;
        $indexDocument->load(self::$resourcesPath . '/mysql-index.xml');

        $conversionResult = $this->xslt->transformToXml($indexDocument);
        fwrite($this->indexFile, $conversionResult);
    }
	
	public function composeHeader()
    {
        $template = new \DOMDocument('1.0', 'UTF-8');
        $template->load(self::$resourcesPath . '/mysql-system.xsl');
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);

		$indexDocument = new \DOMDocument('1.0', 'UTF-8');

		
		$headerNode = $indexDocument->createElement('header');
		$headerNode->appendChild($indexDocument->createElement('generated-date', date('Y-m-d H:i:s')));

		$indexDocument->appendChild($headerNode);
		
        $conversionResult = $this->xslt->transformToXml($indexDocument);
        
		return $conversionResult;
    }
	
	
}