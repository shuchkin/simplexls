<?php 
require_once __DIR__ . '/../src/SimpleXLS.php';

use Shuchkin\SimpleXLS;

echo '<h1>Parse books.xls</h1><pre>';
$xls = new SimpleXLS('books.xls', 'm-d-Y');
if ($xls->parse('books.xls',)) {
    print_r($xls->rows());
} else {
    echo SimpleXLS::parseError();
}
echo '<pre>';
