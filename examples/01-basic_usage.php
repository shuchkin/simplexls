<?php /** @noinspection ForgottenDebugOutputInspection */
require_once __DIR__ . '/../src/SimpleXLS.php';

use Shuchkin\SimpleXLS;

echo '<h1>Parse books.xls</h1><pre>';
if ($xls = SimpleXLS::parse('books.xls')) {
    #print_r($xls->rows());
    for ($sheet = 0; $sheet < count($xls->sheetNames()); $sheet++) {
		print "<h3>";
		print_r($xls->boundsheets[$sheet]['name']);
		print "</h3>";
		print_r($xls->rows($sheet));
	}
} else {
    echo SimpleXLS::parseError();
}
echo '<pre>';
