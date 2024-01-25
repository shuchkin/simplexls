# SimpleXLS class

Parse and retrieve data from old Excel .XLS files. MS Excel 97-2003 workbooks PHP reader. PHP BIFF reader. No additional extensions needed (internal olereader).

This is a fork of shuchkin/simplexls with the ability to add a date formatting option in the contructor and in the static parse file method. The original package has a setDateTimeFormat function, but it does not [work](https://github.com/shuchkin/simplexls/issues/29). These fixes could not be merged due to [backwards compatibility issues](https://github.com/shuchkin/simplexls/pull/32) so I am forking the library to provide a version with the ability to set the data format for new users of the package who want this functionality. The default date format is 'Y-m-d H:i:s'. 

## Basic Usage
```php
if ( $xls = SimpleXLS::parseFile('book.xls','m-d-Y') ) {
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
$xls = new SimpleXLS('books.xls','Y-m-d');
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

$xls = SimpleXLSX::parse('books.xls', 'Y-m-d H:i:s',false, true );
print_r( $xls->rows() );
print_r( $xls->sheets );

```
