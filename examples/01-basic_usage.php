<?php /** @noinspection ForgottenDebugOutputInspection */
require_once __DIR__ . '/../src/SimpleXLS.php';

echo '<h1>Parse books.xsl</h1><pre>';
if ( $xls = SimpleXLS::parse('books.xls') ) {
	print_r( $xls->rows() );
} else {
	echo SimpleXLS::parseError();
}
echo '<pre>';
