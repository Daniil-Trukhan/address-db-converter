<?php
namespace Address;

class Console
{
	/**
     * Вывод в консоль.
     *
     * @param $message
     * @param $newLine
     * @return mixed
     */
    protected function output($message, $newLine = true)
    {
        if ($newLine === true) {
            echo PHP_EOL;
        }

        echo $message;
    }

    protected function outputHeader($message)
    {
    	echo PHP_EOL;
    	echo $message;
    	$this->outputSeparator();
    }

    protected function outputSeparator()
    {
    	echo str_repeat('━', 32);
    }
    
}