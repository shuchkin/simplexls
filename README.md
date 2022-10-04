# SimpleXLS class 0.10.3
[<img src="https://img.shields.io/packagist/dt/shuchkin/simplexls" />](https://packagist.org/packages/shuchkin/simplexls)

Parse and retrieve data from old Excel .XLS files. MS Excel 97-2003 workbooks PHP reader. PHP BIFF reader. No additional extensions needed (internal olereader).
<br/>Modern .XLSX php reader [here](https://github.com/shuchkin/simplexlsx).

*Hey, bro, please ★ the package for my motivation :) and [donate](https://opencollective.com/simplexlsx) for more motivation!*

[**Sergey Shuchkin**](https://www.patreon.com/shuchkin) <sergey.shuchkin@gmail.com> 2016-2022<br/>

## Basic Usage
```php
if ( $xls = SimpleXLS::parseFile('book.xls') ) {
	print_r( $xls->rows() );
	// echo $xls->toHTML();	
} else {
	echo SimpleXLS::parseError();
}
```
```
Array
(
    [0] => Array
        (
            [0] => ISBN
            [1] => title
            [2] => author
            [3] => publisher
            [4] => ctry
        )

    [1] => Array
        (
            [0] => 618260307
            [1] => The Hobbit
            [2] => J. R. R. Tolkien
            [3] => Houghton Mifflin
            [4] => USA
        )

)
```
## Installation
```
composer require shuchkin/simplexls
```
or download class [here](https://github.com/shuchkin/simplexls/blob/master/src/SimpleXLS.php)

From 0.10 version supports PHP 7.1+, PHP 8+  
[0.9.x](https://github.com/shuchkin/simplexls/tags) supports PHP 5.3+

## Examples
### XLS to html table
```php
echo SimpleXLS::parse('book.xls')->toHTML();
```
or
```php
if ( $xls = SimpleXLS::parse('book.xls') ) {
	echo '<table border="1" cellpadding="3" style="border-collapse: collapse">';
	foreach( $xls->rows() as $r ) {
		echo '<tr><td>'.implode('</td><td>', $r ).'</td></tr>';
	}
	echo '</table>';
} else {
	echo SimpleXLS::parseError();
}
```
### Sheet names
```php
if ( $xls = SimpleXLS::parseFile('book.xls') ) {
  print_r( $xls->sheetNames() );
  print_r( $xls->sheetName( $xls->activeSheet ) );
}
```
```
Array
(
    [0] => Sheet 1
    [1] => Sheet 2
    [2] => Sheet 3
)
Sheet 2
```
### Sheets info
```php
if ( $xls = SimpleXLS::parseFile('book.xls') ) {
  print_r( $xls->boundsheets ); 
}
```
```
Array
(
    [0] => Array
        (
            [name] => Sheet 1
            [offset] => 15870
            [hidden] => 
            [active] => 
        )

    [1] => Array
        (
            [name] => Sheet 2
            [offset] => 16308
            [hidden] => 1
            [active] => 1
        )

    [2] => Array
        (
            [name] => Sheet 3 
            [offset] => 16746
            [hidden] => 
            [active] => 
        )
)
```

### Classic OOP style 
```php
$xls = new SimpleXLS('books.xls');
if ($xls->success()) {
	print_r( $xls->rows() );
} else {
	echo 'xls error: '.$xls->error();
}
```



## Debug
```php
ini_set('error_reporting', E_ALL );
ini_set('display_errors', 1 );

//header('Content-Type: text/html; charset=utf-8');

$xls = SimpleXLSX::parse('books.xls', false, true );
print_r( $xls->rows() );
print_r( $xls->sheets );

```


	
## History
```
0.10.3 (2022-10-04) namespaced examples
0.10.2 (2022-09-01) fixed percent values
0.10.1 (2022-04-04)
  PHP 7.1+, PHP 8.0+
0.9.15 (2021-12-01)
  added $xls->sheetNames(), $xls->sheetName( $index ), $xls->activeSheet
  added $limit in $xls->rows( $sheetIndex, $limit = 0 )
  more examples in README
0.9.14 (2021-11-04) Detect datetime format 
0.9.13 (2021-09-21) Fixed éàù... in sheet names, added flag *hidden* in $xls->boundsheets info
0.9.12 (2021-09-20) Fixed éàù...
0.9.11 (2021-09-02) Added *Rows with header values as keys* example
0.9.10 (2021-05-19) SimpleXLSX to SimpleXLS in example
0.9.9 (2021-03-04) Added $xls->toHTML()
0.9.8 (2021-03-04) Fixed skipping first row & col, fixed datetime format in unicode  
0.9.7 (2021-02-26) Added ::parseFile(), ::parseData()
0.9.6 (2020-12-01) Fixed README
0.9.5 (2020-01-16) Fixed negative number values and datetime values
0.9.4 (2019-03-14) Added git Tag for prevent composer warning 
0.9.3 (2019-02-19) Fixed datetime detection
0.9.2 (2018-11-15) GitHub realese, composer
```
