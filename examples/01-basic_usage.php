<?php /** @noinspection ForgottenDebugOutputInspection */
echo '<h1>Parse books.xsl</h1><pre>';
if ( $xls = SimpleXLS::parse('books.xls') ) {
	print_r( $xls->rows() );
} else {
	echo SimpleXLS::parseError();
}
echo '<pre>';
