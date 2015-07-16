<?php
namespace Loader\MySQL;

class TableSchema
{
	public function __construct($schemaFile)
	{
		if (file_exists($schemaFile) === false) {
			throw new Exception;
		}

		$schemaDocument = new \DOMDocument('1.0', 'UTF-8');
        $schemaDocument->load($schemaFile->getPathname());
        $output->handleSchemaFile($tableName, $schemaDocument);
	}

	public function getTableFields()
	{

	}

	public function applyKeys()
	{

	}

	public function convertAndDump($dumpFile)
	{
		$schemaFile = fopen($dumpFile, 'c');

		$this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet($template);
		$this->xslt->setParameter('', 'tableName', $tableName);
        $conversionResult = $this->xslt->transformToXml($schemaDocument);
        fwrite($this->schemaFile, $conversionResult);
	}
}