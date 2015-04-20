address-db-converter
=========

Небольшая утилитка, позволяющая сконвертировать базу ФИАС из XML формата в MySQL и (возможно) другие.    

Плюсы
----
  - Простота использования, остается только скачать файлы для импорта.
  - Расширяемость, возможно быстро реализовать новый формат для вывода.
  - Для обработки XML данных используется SAX парсер, фиксированное потребление системной памяти.

Требования
----
PHP 5.3+ c DOM и XSL модулями.  

Использование
----
Скачать XSD схемы и последнюю полную базу ФИАС в формате XML и распаковать их в папку import.
http://fias.nalog.ru/Public/DownloadPage.aspx  
Создать скрипт, подключить библиотеку и запустить.

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
