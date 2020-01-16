# SimpleXLS class 0.9.5
[<img src="https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fshieldsio-patreon.herokuapp.com%2Fshuchkin" />](https://www.patreon.com/shuchkin)

Parse and retrieve data from old Excel XLS files. MS Excel 97 workbooks PHP reader. PHP BIFF reader. No addiditional extensions need (internal olereader). XLS only, MS Excel 2003+ php reader [here](https://github.com/shuchkin/simplexlsx)  

**Sergey Shuchkin** <sergey.shuchkin@gmail.com> 2016-2020<br/>

## Basic Usage
```php
if ( $xls = SimpleXLS::parse('book.xls') ) {
	print_r( $xls->rows() );
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

### Debug
```php
ini_set('error_reporting', E_ALL );
ini_set('display_errors', 1 );

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
0.9.5 (2020-01-16) fixed negative number values and datetime values
0.9.4 (2019-03-14) Added git Tag for prevent composer warning 
0.9.3 (2019-02-19) Fixed datetime detection
0.9.2 (2018-11-15) GitHub realese, composer
```
