<?php
namespace Address\Loader;

use \Address\Console;

class TableData extends Console
{
	private $tableName;
	private $tableFields;
	private $dataFile;

	private $xmlParser;
	private $currentRow = 0;
	private $treeDepth = 0;

	public static $chunkSize = 4096;

	private $dataFileSize;

	public function __construct($tableName, $tableFields, $dataFile)
	{
		if (file_exists($dataFile) === false) {
			throw new \Exception('Not found: ' . $dataFile);
		}

		//
		$this->dataFile = $dataFile;
		$this->tableName = $tableName;
		$this->tableFields = $tableFields;

		//
		$dataFileSize = filesize($this->dataFile);
    	$sourcePercent = $sourceFileSize / 100;
    	$sourceProgress = 0;
    	$sourcePercentProgress = 0;

		$this->xmlParser = xml_parser_create();
		xml_set_object($this->xmlParser, $this);
        xml_set_element_handler($this->xmlParser, "parserOpenTag", "parserClosetag");
	}

	private function parserOpenTag($parser, $tag, $attributes)
	{
		// Пропускаем родительский элемент
        if ($this->treeDepth === 0) {
            return $this->treeDepth += 1; // псевдо-дерево
        }

        $this->treeDepth += 1;
        $this->currentRow += 1;

        // Форматируем под нужный синтаксис
        //$output->handleData($tableName, $attributes);

        // Вывод технической информации

        /*if (($this->currentRow % self::$packet) === 0) {
            $this->output($this->currentRow);

            if ($this->currentRow % (self::$packet * self::$averageAmount) === 0) {
                $this->output(' AVG: ' . round((microtime(true) - $this->timerState) / self::$averageAmount, 2) . 's');
                $this->timerState = microtime(true);
            }
        }*/
	}

	private function parserCloseTag($parser, $tag)
	{
        $this->depth -= 1; // псевдо-дерево
	}

	public function convertAndDump($outputFile) 
    {
    	$sourceFileSize = filesize($this->dataFile);
    	$sourcePercent = $sourceFileSize / 100;
    	$sourceProgress = 0;
    	$sourcePercentProgress = 0;

        $sourceFile = fopen($this->dataFile, 'r');
            
        while ($data = fread($sourceFile, self::$chunkSize)) {
            xml_parse($this->xmlParser, $data, feof($sourceFile));

            $sourceProgress += self::$chunkSize;

            if (floor($sourceProgress / $sourcePercent) > $sourcePercentProgress) {
            	$sourcePercentProgress = floor($sourceProgress / $sourcePercent);
            	$this->output($sourcePercentProgress . '%');
            }


        }

        fclose($sourceFile);

        xml_parser_free($this->xmlParser);
    }
}