address-db-converter
=========

Небольшая библиотека для экспорта базы ФИАС из XML формата в MySQL и (возможно в будущем) другие.    

Плюсы
----
  - Расширяемость, новые форматы вывода легко реализуются через интерфейс.
  - Фиксированное потребление системной памяти (используется SAX парсер).

Требования
----
PHP 5.3+ c DOM и XSL модулями.  

Использование
----
Скачать XSD схемы и последнюю полную базу ФИАС в формате XML.  
http://fias.nalog.ru/Public/DownloadPage.aspx  
Распаковать оба архива в указанную папку, создать скрипт, подключить библиотеку и запустить.

Пример **import.php**  
```php
<?php
require 'src/loader.php';

ini_set('memory_limit', '256M');

// Корректные пути
Address\Converter::$sourcePath = __DIR__ . '/import';
Address\Output\Output::$resourcesPath = __DIR__ . '/resources';
Address\Output\Output::$outputPath = __DIR__ . '/output';

// Для загрузки напрямую в базу
$dsn = 'mysql:host=localhost;dbname=database';
$dbUser = 'root';
$dbPass = '';
$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
$conn = new PDO($dsn, $dbUser, $dbPass, $options);
$output = new Address\Output\MySQL\ConnectionOutput($conn);

// Для дампа в файл
$output = new Address\Output\MySQL\DumpOutput();

Address\Converter::convert($output);


```sh
C:\php\php.exe C:\address-db-converter\import.php