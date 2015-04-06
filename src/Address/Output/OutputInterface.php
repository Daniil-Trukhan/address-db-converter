<?php
namespace Address\Output;

interface OutputInterface
{
    function receiveSchemaResult($conversionResult);
    function receiveDataResult($conversionResult);
} 