<?php
namespace Address\Loader;

class TableSchema
{
	private $tableFields;
	private $schemaDocument;

	public function __construct($tableName, $schemaFile)
	{
		if (file_exists($schemaFile) === false) {
			throw new \Exception('Not found: ' . $schemaFile);
		}

		$this->tableName = $tableName;
		$this->schemaDocument = new \DOMDocument('1.0', 'UTF-8');
        $this->schemaDocument->load($schemaFile);
        $this->tableFields = $this->queryTableFields($this->schemaDocument);
	}

	private function queryTableFields(\DOMDocument $schemaDocument)
	{
		$tableFields = array();

		$xpath = new \DOMXpath($schemaDocument);
		$nodeList = $xpath->query('//xs:attribute');
		foreach ($nodeList as $node) {
			$tableFields[] = $node->attributes->getNamedItem('name')->nodeValue;
		}

		return $tableFields;
	}

	private function convert($tableName, \DOMDocument $schemaDocument, $resourcesPath)
	{
		$template = new \DOMDocument('1.0', 'UTF-8');
        $template->load($resourcesPath . '/mysql-schema.xsl');

		$xslt = new \XSLTProcessor();
        $xslt->importStylesheet($template);
		$xslt->setParameter('', 'tableName', $tableName);
        $conversionResult = $this->xslt->transformToXml($schemaDocument);

        return $conversionResult;
	}

	public function getTableFields()
	{
		return $this->tableFields;
	}

	public function applyKeys()
	{

	}

	public function convertAndDump(\Address\Formatter\Formatter $formatter, $dumpFile)
	{
        $conversionResult = $formatter->handleSchemaFile($this->tableName, $this->schemaDocument);
        file_put_contents($dumpFile, $conversionResult);
	}
}