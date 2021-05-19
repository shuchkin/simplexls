# SimpleXLS class 0.9.10
[<img src="https://img.shields.io/packagist/dt/shuchkin/simplexls" />](https://packagist.org/packages/shuchkin/simplexls)

Parse and retrieve data from old Excel .XLS files. MS Excel 97-2003 workbooks PHP reader. PHP BIFF reader. No additional extensions needed (internal olereader).
<br/>Modern .XLSX php reader [here](https://github.com/shuchkin/simplexlsx).  

[**Sergey Shuchkin**](https://www.patreon.com/shuchkin) <sergey.shuchkin@gmail.com> 2016-2021<br/>

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

### Debug
```php
ini_set('error_reporting', E_ALL );
ini_set('display_errors', 1 );

//header('Content-Type: text/html; charset=utf-8');

$xls = SimpleXLSX::parse('books.xlsx', false, true );
print_r( $xls->rows() );
print_r( $xls->sheets );

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
	
## History
```
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