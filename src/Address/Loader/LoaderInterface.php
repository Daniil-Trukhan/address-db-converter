<?php
namespace Address\Loader;

interface LoaderInterface
{
    function getSchemaFiles($extension);
    function getDataFiles($extension);
} 