<?php /** @noinspection ForgottenDebugOutputInspection */
require_once('../src/SimpleXLS.php');
echo '<h1>Parse books.xls</h1><pre>';
if ( $xls = SimpleXLS::parse('books.xls') ) {
	print_r( $xls->rows() );
} else {
	echo SimpleXLS::parseError();
}
echo '<pre>';

echo '<hr>';


$columns = array(
	'isbn',
	'title',
	'author',
	'publisher',
	'ctry'
);
echo '<h1>cRows (column) Parse books.xls</h1><pre>';
if ( $xls = SimpleXLS::parse('books.xls') ) {
	print_r( $xls->cRows($columns) );
} else {
	echo SimpleXLS::parseError();
}
echo '<pre>';
