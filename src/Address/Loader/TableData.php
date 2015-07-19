<?php
namespace Address\Loader;

use \Address\Console;

class TableData extends Console
{
	private $tableName;
	private $tableFields;
	private $dataFile;

	private $parser;
	private $currentRow = 0;
	private $treeDepth = 0;
	private $queue = array();

	public static $chunkSize = 4096;

	private $contentLength;
	private $contentLengthStep;
	private $processedLength;

	public function __construct($tableName, $tableFields, $dataFile)
	{
		if (file_exists($dataFile) === false) {
			throw new \Exception('Not found: ' . $dataFile);
		}

		//
		$this->dataFile = $dataFile;
		$this->tableName = $tableName;
		$this->tableFields = $tableFields;
		$this->defaultRow = array_fill_keys($tableFields, null);

		//
		$this->contentLength = filesize($this->dataFile);
    	$this->contentLengthStep = $this->contentLength / 100;
    	$this->processedLength = 0;

		//
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "parserOpenTag", "parserClosetag");
	}

	private function parserOpenTag($parser, $tag, $attributes)
	{
		// Пропускаем родительский элемент
        if ($this->treeDepth === 0) {
            return $this->treeDepth += 1; // псевдо-дерево
        }

        $this->treeDepth += 1;
        $this->currentRow += 1;

        // Поля записи
        $this->queue[] = $attributes;
	}

	private function parserCloseTag($parser, $tag)
	{
        $this->depth -= 1; // псевдо-дерево
	}

	public function convertAndDump(\Address\Formatter\Formatter $formatter, $outputFile) 
    {
    	// TODO WRITE HEADER IN OUTPUT FILE
        $sourceFile = fopen($this->dataFile, 'r');
        
        // Парсим фрагменты файла
        $processedPercent = 0;
        while ($data = fread($sourceFile, self::$chunkSize)) {
            xml_parse($this->parser, $data, feof($sourceFile));

            // Форматирование строки
            $this->queue = array_reverse($this->queue);
            while (count($this->queue) !== 0) {
            	$tableRow = array_pop($this->queue);
            	$tableRow = array_intersect_key($tableRow + $this->defaultRow, $this->defaultRow);

            	$conversionResult = $formatter->handleDataFile($this->tableName, $tableRow);
            	file_put_contents($outputFile, $conversionResult, FILE_APPEND);
            }
            
            // Вывод прогресса в консоль
            $this->processedLength += self::$chunkSize;
            $currentPercent = ($this->processedLength < $this->contentLength) ? ceil($this->processedLength / $this->contentLength * 100) : 100;
            if ($this->contentLength > self::$chunkSize && $currentPercent > $processedPercent) {
            	$this->output('  ' . $currentPercent . '%');
            }
            $processedPercent = $currentPercent;
        }

        fclose($sourceFile);

        xml_parser_free($this->parser);
    }
}