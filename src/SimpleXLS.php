<?php
/*
 * Example

echo '<pre>';

if ( $xls = SimpleXLS::parse('excel5book.xls')) {
	print_r( $xls->rows() ); // dump first sheet
	print_r( $xls->rows(1)); /// dump second sheet
} else {
	echo SimpleXLS::parseError();
}

echo '</pre>';
 */

/**
 * A class for reading Microsoft Excel Spreadsheets.
 *
 * SimpleXLS version 2016-2020 packaged by Sergey Shuchkin <sergey.shuchkin@gmail.com> from
 * Spreadsheet_Excel_Reader class developed by Vadim Tkachenko <vt@phpapache.com>
 */
class SimpleXLS {
	const BIFF8 = 0x600;
	const BIFF7 = 0x500;
	const WORKBOOKGLOBALS = 0x5;
	const WORKSHEET = 0x10;

	//const TYPE_BOF = 0x809;
	const TYPE_EOF = 0x0a;
	const TYPE_BOUNDSHEET = 0x85;
	const TYPE_DIMENSION = 0x200;
	const TYPE_ROW = 0x208;
	const TYPE_DBCELL = 0xd7;
	const TYPE_FILEPASS = 0x2f;
	//const TYPE_NOTE = 0x1c;
	//const TYPE_TXO = 0x1b6;
	const TYPE_RK = 0x7e;
	const TYPE_RK2 = 0x27e;
	const TYPE_MULRK = 0xbd;
	const TYPE_MULBLANK = 0xbe;
	//const TYPE_INDEX = 0x20b;
	const TYPE_SST = 0xfc;
	//const TYPE_EXTSST = 0xff;
	//const TYPE_CONTINUE = 0x3c;
	const TYPE_LABEL = 0x204;
	const TYPE_LABELSST = 0xfd;
	const TYPE_NUMBER = 0x203;
	const TYPE_NAME = 0x18;
	//const TYPE_ARRAY = 0x221;
	//const TYPE_STRING = 0x207;
	const TYPE_FORMULA = 0x406;
	const TYPE_FORMULA2 = 0x6;
	const TYPE_FORMAT = 0x41e;
	const TYPE_XF = 0xe0;
	const TYPE_BOOLERR = 0x205;
	//const TYPE_UNKNOWN = 0xffff;
	const TYPE_NINETEENFOUR = 0x22;
	const TYPE_MERGEDCELLS = 0xE5;
	const TYPE_WINDOW1 = 0x3D;

	//const DEF_NUM_FORMAT = "%.2f";
	const DEF_NUM_FORMAT = '%s';

	// OLE
	const NUM_BIG_BLOCK_DEPOT_BLOCKS_POS = 0x2c;
	const SMALL_BLOCK_DEPOT_BLOCK_POS = 0x3c;
	const ROOT_START_BLOCK_POS = 0x30;
	const BIG_BLOCK_SIZE = 0x200;
	const SMALL_BLOCK_SIZE = 0x40;
	const EXTENSION_BLOCK_POS = 0x44;
	const NUM_EXTENSION_BLOCK_POS = 0x48;
	const PROPERTY_STORAGE_BLOCK_SIZE = 0x80;
	const BIG_BLOCK_DEPOT_BLOCKS_POS = 0x4c;
	const SMALL_BLOCK_THRESHOLD = 0x1000;
	// property storage offsets
	const SIZE_OF_NAME_POS = 0x40;
	const TYPE_POS = 0x42;
	const START_BLOCK_POS = 0x74;
	const SIZE_POS = 0x78;
	/**
	 * Array of worksheets found
	 *
	 * @var array
	 * @access public
	 */
	public $boundsheets = array();
	public $activeSheet = 0;

	/**
	 * Array of format records found
	 *
	 * @var array
	 * @access public
	 */
	public $formatRecords = array();

	/**
	 *
	 * @var array
	 * @access public
	 */
	public $sst = array();

	/**
	 * Array of worksheets
	 *
	 * The data is stored in 'cells' and the meta-data is stored in an array
	 * called 'cellsInfo'
	 *
	 * Example:
	 *
	 * $sheets  -->  'cells'  -->  row --> column --> Interpreted value
	 *          -->  'cellsInfo' --> row --> column --> 'type' - Can be 'date', 'number', or 'unknown'
	 *                                            --> 'raw' - The raw data that Excel stores for that data cell
	 *
	 * @var array
	 * @access public
	 */
	public $sheets = array();
	/**
	 * List of default date formats used by Excel
	 *
	 * @var array
	 * @access public
	 */
	public $dateFormats = array(
		0xe  => 'd/m/Y',
		0xf  => 'd-M-Y',
		0x10 => 'd-M',
		0x11 => 'M-Y',
		0x12 => 'h:i a',
		0x13 => 'h:i:s a',
		0x14 => 'H:i',
		0x15 => 'H:i:s',
		0x16 => 'd/m/Y H:i',
		0x2d => 'i:s',
		0x2e => 'H:i:s',
		0x2f => 'i:s.S'
	);
/**
	 * Default number formats used by Excel
	 *
	 * @var array
	 * @access public
	 */
	public $numberFormats = array(
		0x1  => '%1.0f',     // "0"
		0x2  => '%1.2f',     // "0.00",
		0x3  => '%1.0f',     //"#,##0",
		0x4  => '%1.2f',     //"#,##0.00",
		0x5  => '%1.0f',     /*"$#,##0;($#,##0)",*/
		0x6  => '$%1.0f',    /*"$#,##0;($#,##0)",*/
		0x7  => '$%1.2f',    //"$#,##0.00;($#,##0.00)",
		0x8  => '$%1.2f',    //"$#,##0.00;($#,##0.00)",
		0x9  => '%1.0f%%',   // "0%"
		0xa  => '%1.2f%%',   // "0.00%"
		0xb  => '%1.2f',     // 0.00E00",
		0x25 => '%1.0f',    // "#,##0;(#,##0)",
		0x26 => '%1.0f',    //"#,##0;(#,##0)",
		0x27 => '%1.2f',    //"#,##0.00;(#,##0.00)",
		0x28 => '%1.2f',    //"#,##0.00;(#,##0.00)",
		0x29 => '%1.0f',    //"#,##0;(#,##0)",
		0x2a => '$%1.0f',   //"$#,##0;($#,##0)",
		0x2b => '%1.2f',    //"#,##0.00;(#,##0.00)",
		0x2c => '$%1.2f',   //"$#,##0.00;($#,##0.00)",
		0x30 => '%1.0f'
	);
	protected $datetimeFormat = 'Y-m-d H:i:s';
	/**
	 * Default encoding
	 *
	 * @var string
	 * @access private
	 */
	protected $defaultEncoding = 'UTF-8';
	/**
	 * Default number format
	 *
	 * @var integer
	 * @access private
	 */
	protected $defaultFormat = self::DEF_NUM_FORMAT;
	/**
	 * List of formats to use for each column
	 *
	 * @var array
	 * @access private
	 */
	protected $columnsFormat = array();

	protected $recType;
	protected $nineteenFour;
	protected $multiplier;
	protected $sn;
	protected $curFormat;

	// OLERead
	protected $data;
    protected $bigBlockChain;
	protected $smallBlockChain;
	protected $rootEntry;
	protected $entry;
	protected $props;

	// sergey.shuchkin@gmail.com
	protected $wrkbook; // false - to use excel format
	protected $error = false;
	protected $debug;

	// {{{ Spreadsheet_Excel_Reader()

	/**
	 * Constructor
	 *
	 * @param string $filename XLS Filename or xls contents
	 * @param bool $isData If True then $filename is contents
	 * @param bool $debug Trigger PHP errors?
	 */
	public function __construct( $filename, $isData = false, $debug = false ) {
		$this->debug = $debug;
		$this->_oleread( $filename, $isData );
		$this->_parse();
	}
	public static function parseFile( $filename, $debug = false ) {
		return self::parse( $filename, false, $debug );
	}

	public static function parseData( $data, $debug = false ) {
		return self::parse( $data, true, $debug );
	}
	public static function parse( $filename, $isData = false, $debug = false ) {
		$xls = new self( $filename, $isData, $debug );
		if ( $xls->success() ) {
			return $xls;
		}
		self::parseError( $xls->error() );

		return false;
	}
	public static function parseError( $set = false ) {
		static $error = false;
		return $set ? $error = $set : $error;
	}
	public function error( $set = false ) {
		if ( $set ) {
			$this->error = $set;
			if ( $this->debug ) {
				trigger_error( $set );
			}
		}

		return $this->error;
	}
	public function success() {
		return ! $this->error;
	}
	public function rows( $sheetNum = 0, $limit = 0 ) {
		if ( $this->sheets[ $sheetNum ] ) {
			$s      = $this->sheets[ $sheetNum ];
			$result = array();
			for ( $i = 0; $i < $s['numRows']; $i ++ ) {
				$r = array();
				for ( $j = 0; $j < $s['numCols']; $j ++ ) {
					$r[ $j ] = isset( $s['cells'][ $i ][ $j ] ) ? $s['cells'][ $i ][ $j ] : '';
				}
				$result[] = $r;
				$limit--;
				if ( $limit === 0 ) {
					break;
				}
			}

			return $result;
		}

		return false;
	}
	public function toHTML( $worksheetIndex = 0 ) {
		$s = '<table class=excel>';
		foreach ( $this->rows( $worksheetIndex ) as $r ) {
			$s .= '<tr>';
			foreach ( $r as $c ) {
				$s .= '<td nowrap>' . ( $c === '' ? '&nbsp' : htmlspecialchars( $c, ENT_QUOTES ) ) . '</td>';
			}
			$s .= "</tr>\r\n";
		}
		$s .= '</table>';

		return $s;
	}
	public function setDateTimeFormat( $value ) {
		$this->datetimeFormat = is_string( $value) ? $value : false;
	}
	public function sheetNames() {
		$result = array();
		foreach( $this->boundsheets as $k => $v ) {
			$result[ $k ] = $v['name'];
		}
		return $result;
	}
	public function sheetName( $index ) {
		return isset( $this->boundsheets[ $index ] )  ? $this->boundsheets[ $index ]['name'] : null;
	}

	// }}}

	protected function _oleread( $sFileName, $isData = false ) {
		if ( $isData ) {
			$this->data = $sFileName;
		} else {
			// check if file exist and is readable (Darko Miljanovic)
			if ( ! is_readable( $sFileName ) ) {
				$this->error( 'File not is readable ' . $sFileName );
				return false;
			}

			$this->data = file_get_contents( $sFileName );
			if ( ! $this->data ) {
				$this->error( 'File reading error ' . $sFileName );
				return false;
			}
		}
		//echo IDENTIFIER_OLE;
		//echo 'start';
		if ( $this->_strpos( $this->data, pack( 'CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1 ) ) !== 0 ) {
			$this->error( 'File is not XLS' );

			return false;
		}

		$numBigBlockDepotBlocks = $this->_GetInt4d( self::NUM_BIG_BLOCK_DEPOT_BLOCKS_POS );
		$sbdStartBlock = $this->_GetInt4d( self::SMALL_BLOCK_DEPOT_BLOCK_POS );
		$rootStartBlock = $this->_GetInt4d( self::ROOT_START_BLOCK_POS );
		$extensionBlock = $this->_GetInt4d( self::EXTENSION_BLOCK_POS );
		$numExtensionBlocks = $this->_GetInt4d( self::NUM_EXTENSION_BLOCK_POS );

/*
			echo $this->numBigBlockDepotBlocks." ";
			echo $this->sbdStartBlock." ";
			echo $this->rootStartBlock." ";
			echo $this->extensionBlock." ";
			echo $this->numExtensionBlocks." ";

*/
		//echo "sbdStartBlock = $this->sbdStartBlock\n";
		$bigBlockDepotBlocks = array();
		$pos                 = self::BIG_BLOCK_DEPOT_BLOCKS_POS;
		// echo "pos = $pos";
		$bbdBlocks = $numBigBlockDepotBlocks;

		if ( $numExtensionBlocks !== 0 ) {
			$bbdBlocks = ( self::BIG_BLOCK_SIZE - self::BIG_BLOCK_DEPOT_BLOCKS_POS ) / 4;
		}

		for ( $i = 0; $i < $bbdBlocks; $i ++ ) {
			$bigBlockDepotBlocks[ $i ] = $this->_GetInt4d( $pos );
			$pos                       += 4;
		}


		for ($j = 0; $j < $numExtensionBlocks; $j ++ ) {
			$pos          = ( $extensionBlock + 1 ) * self::BIG_BLOCK_SIZE;
			$blocksToRead = min( $numBigBlockDepotBlocks - $bbdBlocks, self::BIG_BLOCK_SIZE / 4 - 1 );

			for ( $i = $bbdBlocks; $i < $bbdBlocks + $blocksToRead; $i ++ ) {
				$bigBlockDepotBlocks[ $i ] = $this->_GetInt4d( $pos );
				$pos                       += 4;
			}

			$bbdBlocks += $blocksToRead;
			if ( $bbdBlocks < $numBigBlockDepotBlocks) {
				$extensionBlock = $this->_GetInt4d( $pos );
			}
		}

		// var_dump($bigBlockDepotBlocks);

		// readBigBlockDepot

		$index               = 0;
		$this->bigBlockChain = array();

		for ($i = 0; $i < $numBigBlockDepotBlocks; $i ++ ) {
			$pos = ( $bigBlockDepotBlocks[ $i ] + 1 ) * self::BIG_BLOCK_SIZE;
			//echo "pos = $pos";
			for ( $j = 0; $j < self::BIG_BLOCK_SIZE / 4; $j ++ ) {
				$this->bigBlockChain[ $index ] = $this->_GetInt4d( $pos );
				$pos                           += 4;
				$index ++;
			}
		}

		//var_dump($this->bigBlockChain);
		//echo '=====2';
		// readSmallBlockDepot();

		$index                 = 0;
		$sbdBlock              = $sbdStartBlock;
		$this->smallBlockChain = array();

		while ( $sbdBlock !== - 2 ) {

			$pos = ( $sbdBlock + 1 ) * self::BIG_BLOCK_SIZE;

			for ( $j = 0; $j < self::BIG_BLOCK_SIZE / 4; $j ++ ) {
				$this->smallBlockChain[ $index ] = $this->_GetInt4d( $pos );
				$pos                             += 4;
				$index ++;
			}

			$sbdBlock = $this->bigBlockChain[ $sbdBlock ];
		}


		// readData(rootStartBlock)
		$block = $rootStartBlock;

		$this->entry = $this->_readData( $block );

		/*
		while ($block != -2)  {
			$pos = ($block + 1) * self::BIG_BLOCK_SIZE;
			$this->entry = $this->entry.$this->_substr($this->_data, $pos, self::BIG_BLOCK_SIZE);
			$block = $this->bigBlockChain[$block];
		}
		*/
		//echo '==='.$this->entry."===";
		$this->_readPropertySets();
		$this->data = $this->_readWorkBook();

		return true;
	}

	// {{{ setOutputEncoding()
	protected function _GetInt2d( $pos ) {
		return ord( $this->data[ $pos ] ) | ord( $this->data[ $pos + 1 ] ) << 8;
//		return ($value > 0x7FFFFFFF) ? $value - 0x100000000 : $value;
	}
	protected function _GetInt4d( $pos ) {
		$value = ord( $this->data[ $pos ] ) | ( ord( $this->data[ $pos + 1 ] ) << 8 ) | ( ord( $this->data[ $pos + 2 ] ) << 16 ) | ( ord( $this->data[ $pos + 3 ] ) << 24 );
		return ($value > 0x7FFFFFFF) ? $value - 0x100000000 : $value;
	}

	// }}}

	// {{{ setRowColOffset()

	protected function _readData( $bl ) {
		$block = $bl;

		$data = '';

		while ( $block !== - 2 ) {
			$pos  = ( $block + 1 ) * self::BIG_BLOCK_SIZE;
			$data .= $this->_substr( $this->data, $pos, self::BIG_BLOCK_SIZE );
			//echo "pos = $pos data=$data\n";
			$block = $this->bigBlockChain[ $block ];
		}

		return $data;
	}

	// }}}
	// {{{ setDefaultFormat()

	protected function _readPropertySets() {
		$offset = 0;
		//var_dump($this->entry);
		while ( $offset < $this->_strlen( $this->entry ) ) {
			$d = $this->_substr( $this->entry, $offset, self::PROPERTY_STORAGE_BLOCK_SIZE );

			$nameSize = ord( $d[ self::SIZE_OF_NAME_POS ] ) | ( ord( $d[ self::SIZE_OF_NAME_POS + 1 ] ) << 8 );

			$type = ord( $d[ self::TYPE_POS ] );
			//$maxBlock = $this->_strlen($d) / self::BIG_BLOCK_SIZE - 1;

			$startBlock = ord( $d[ self::START_BLOCK_POS] ) | ( ord( $d[ self::START_BLOCK_POS + 1 ] ) << 8 ) | ( ord( $d[ self::START_BLOCK_POS + 2 ] ) << 16 ) | ( ord( $d[ self::START_BLOCK_POS + 3 ] ) << 24 );
			$size       = ord( $d[ self::SIZE_POS] ) | ( ord( $d[ self::SIZE_POS + 1 ] ) << 8 ) | ( ord( $d[ self::SIZE_POS + 2 ] ) << 16 ) | ( ord( $d[ self::SIZE_POS + 3 ] ) << 24 );

			$name = '';
			for ( $i = 0; $i < $nameSize; $i ++ ) {
				$name .= $d[ $i ];
			}

			$name = str_replace( "\x00", '', $name );

			$this->props[] = array(
				'name'       => $name,
				'type'       => $type,
				'startBlock' => $startBlock,
				'size'       => $size
			);

			if ( ( $name === 'Workbook' ) || ( $name === 'Book' ) ) {
				$this->wrkbook = count( $this->props ) - 1;
			}

			if ( $name === 'Root Entry' ) {
				$this->rootEntry = count( $this->props ) - 1;
			}

			//echo "name ==$name=\n";


			$offset += self::PROPERTY_STORAGE_BLOCK_SIZE;
		}

	}

	// }}}
	// {{{ setColumnFormat()

	protected function _readWorkBook() {
		if ( $this->props[ $this->wrkbook ]['size'] < self::SMALL_BLOCK_THRESHOLD ) {
//    	  getSmallBlockStream(PropertyStorage ps)

			$rootdata = $this->_readData( $this->props[ $this->rootEntry ]['startBlock'] );

			$streamData = '';
			$block      = (int) $this->props[ $this->wrkbook ]['startBlock'];
			//$count = 0;
			while ( $block !== - 2 ) {
				$pos        = $block * self::SMALL_BLOCK_SIZE;
				$streamData .= $this->_substr( $rootdata, $pos, self::SMALL_BLOCK_SIZE );

				$block = $this->smallBlockChain[ $block ];
			}

			return $streamData;


		}

		$numBlocks = $this->props[ $this->wrkbook ]['size'] / self::BIG_BLOCK_SIZE;
		if ( $this->props[ $this->wrkbook ]['size'] % self::BIG_BLOCK_SIZE !== 0 ) {
			$numBlocks ++;
		}

		if ( $numBlocks === 0 ) {
			return '';
		}

		//echo "numBlocks = $numBlocks\n";
		//byte[] streamData = new byte[numBlocks * self::BIG_BLOCK_SIZE];
		//print_r($this->wrkbook);
		$streamData = '';
		$block      = $this->props[ $this->wrkbook ]['startBlock'];

		//echo "block = $block";
		while ( $block !== - 2 ) {
			$pos        = ( $block + 1 ) * self::BIG_BLOCK_SIZE;
			$streamData .= $this->_substr( $this->data, $pos, self::BIG_BLOCK_SIZE );
			$block      = $this->bigBlockChain[ $block ];
		}

		//echo 'stream'.$streamData;
		return $streamData;
	}


	// }}}
	protected function parseSubstreamHeader( $pos )
    {
		$length = $this->_GetInt2d( $pos + 2 );

		$version       = $this->_GetInt2d( $pos + 4 );
		$substreamType = $this->_GetInt2d( $pos + 6 );
		return array( $length, $version, $substreamType );
    }
	// {{{ _parse()

	/**
	 * Parse a workbook
	 *
	 * @access private
	 * @return bool
	 */
	protected function _parse() {
		$pos = 0;

//        $code = ord($this->data[$pos]) | ord($this->data[$pos+1])<<8;
        list( $length, $version, $substreamType ) = $this->parseSubstreamHeader( $pos );
//		echo "Start parse code=".base_convert($code,10,16)." version=".base_convert($version,10,16)." substreamType=".base_convert($substreamType,10,16).""."\n";

//		die();

		if ( ( $version !== self::BIFF8 ) &&
		     ( $version !== self::BIFF7 )
		) {
			return false;
		}

		if ( $substreamType !== self::WORKBOOKGLOBALS ) {
			return false;
		}

		//print_r($rec);
		$pos += $length + 4;

		$code   = ord( $this->data[ $pos ] ) | ord( $this->data[ $pos + 1 ] ) << 8;
		$length = ord( $this->data[ $pos + 2 ] ) | ord( $this->data[ $pos + 3 ] ) << 8;

		while ( $code !== self::TYPE_EOF ) {
			switch ( $code ) {
				case self::TYPE_SST:
					//echo "Type_SST\n";
					$formattingRuns    = 0;
					$extendedRunLength = 0;
					$spos              = $pos + 4;
					$limitpos          = $spos + $length;
					$uniqueStrings     = $this->_GetInt4d( $spos + 4 );
					$spos              += 8;
					for ( $i = 0; $i < $uniqueStrings; $i ++ ) {
						// Read in the number of characters
						if ( $spos === $limitpos ) {
							$opcode    = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
							$conlength = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
							if ( $opcode !== 0x3c ) {
								return - 1;
							}
							$spos     += 4;
							$limitpos = $spos + $conlength;
						}
						$numChars = ord( $this->data[ $spos ] ) | ( ord( $this->data[ $spos + 1 ] ) << 8 );
						//echo "i = $i pos = $pos numChars = $numChars ";
						$spos        += 2;
						$optionFlags = ord( $this->data[ $spos ] );
						$spos ++;
						$asciiEncoding  = ( ( $optionFlags & 0x01 ) === 0 );
						$extendedString = ( ( $optionFlags & 0x04 ) !== 0 );

						// See if string contains formatting information
						$richString = ( ( $optionFlags & 0x08 ) !== 0 );

						if ( $richString ) {
							// Read in the crun
							$formattingRuns = $this->_GetInt2d( $spos );
							$spos           += 2;
						}

						if ( $extendedString ) {
							// Read in cchExtRst
							$extendedRunLength = $this->_GetInt4d( $spos );
							$spos              += 4;
						}

						$len = $asciiEncoding ? $numChars : $numChars * 2;
						if ( $spos + $len < $limitpos ) {
							$retstr = $this->_substr( $this->data, $spos, $len );
							$spos   += $len;
						} else {
							// found countinue
							$retstr    = $this->_substr( $this->data, $spos, $limitpos - $spos );
							$bytesRead = $limitpos - $spos;
							$charsLeft = $numChars - ( $asciiEncoding ? $bytesRead : ( $bytesRead / 2 ) );
							$spos      = $limitpos;

							while ( $charsLeft > 0 ) {
								$opcode    = $this->_GetInt2d( $spos );
								$conlength = $this->_GetInt2d( $spos + 2 );
								if ( $opcode !== 0x3c ) {
									return - 1;
								}
								$spos     += 4;
								$limitpos = $spos + $conlength;
								$option   = ord( $this->data[ $spos ] );
								$spos ++;
								if ( $asciiEncoding && ( $option === 0 ) ) {
									$len           = min( $charsLeft, $limitpos - $spos ); // min($charsLeft, $conlength);
									$retstr        .= $this->_substr( $this->data, $spos, $len );
									$charsLeft     -= $len;
									$asciiEncoding = true;
								} elseif ( ! $asciiEncoding && ( $option !== 0 ) ) {
									$len           = min( $charsLeft * 2, $limitpos - $spos ); // min($charsLeft, $conlength);
									$retstr        .= $this->_substr( $this->data, $spos, $len );
									$charsLeft     -= $len / 2;
									$asciiEncoding = false;
								} elseif ( ! $asciiEncoding && ( $option === 0 ) ) {
									// Bummer - the string starts off as Unicode, but after the
									// continuation it is in straightforward ASCII encoding
									$len = min( $charsLeft, $limitpos - $spos ); // min($charsLeft, $conlength);
									for ( $j = 0; $j < $len; $j ++ ) {
										$retstr .= $this->data[ $spos + $j ] . chr( 0 );
									}
									$charsLeft     -= $len;
									$asciiEncoding = false;
								} else {
									$newstr = '';
									for ( $j = 0, $len_retstr = $this->_strlen( $retstr ); $j < $len_retstr; $j ++ ) {
										$newstr = $retstr[ $j ] . chr( 0 );
									}
									$retstr        = $newstr;
									$len           = min( $charsLeft * 2, $limitpos - $spos ); // min($charsLeft, $conlength);
									$retstr        .= $this->_substr( $this->data, $spos, $len );
									$charsLeft     -= $len / 2;
									$asciiEncoding = false;
									//echo "Izavrat\n";
								}
								$spos += $len;

							}
						}
						$retstr = $asciiEncoding ? $this->_Latin1toDef( $retstr ) : $this->_UTF16toDef( $retstr );
//                                              echo "Str $i = $retstr\n";
						if ( $richString ) {
							$spos += 4 * $formattingRuns;
						}

						// For extended strings, skip over the extended string data
						if ( $extendedString ) {
							$spos += $extendedRunLength;
						}
						//if ($retstr == 'Derby'){
						//      echo "bb\n";
						//}
						$this->sst[] = $retstr;
					}
					/*$continueRecords = array();
					while ($this->getNextCode() == Type_CONTINUE) {
						$continueRecords[] = &$this->nextRecord();
					}
					//echo " 1 Type_SST\n";
					$this->shareStrings = new SSTRecord($r, $continueRecords);
					//print_r($this->shareStrings->strings);
					 */
					// echo 'SST read: '.($time_end-$time_start)."\n";
					break;

				case self::TYPE_FILEPASS:
					return false;
				case self::TYPE_NAME:
					//echo "Type_NAME\n";
					break;
				case self::TYPE_FORMAT:
					$indexCode = $this->_GetInt2d( $pos + 4 );

					if ( $version === self::BIFF8 ) {
						$numchars = $this->_GetInt2d( $pos + 6 );
						if ( ord( $this->data[ $pos + 8 ] ) === 0 ) { // ascii
							$formatString = $this->_substr( $this->data, $pos + 9, $numchars );
							$formatString = $this->_Latin1toDef( $formatString );
						} else {
							$formatString = $this->_substr( $this->data, $pos + 9, $numchars * 2 );
							$formatString = $this->_UTF16toDef( $formatString );
						}
					} else {
						$numchars     = ord( $this->data[ $pos + 6 ] );
						$formatString = $this->_substr( $this->data, $pos + 7, $numchars * 2 );
						$formatString = $this->_Latin1toDef( $formatString );
					}

					$this->formatRecords[ $indexCode ] = $formatString;
//					echo "Type.FORMAT[$indexCode]=$formatString\n";
					break;
				case self::TYPE_XF:
					$formatstr = '';
					$indexCode = $this->_GetInt2d( $pos + 6 );
//					echo "\nType.XF code=".$indexCode." dateFormat=".$this->dateFormats[ $indexCode ]." numberFormats=".$this->numberFormats[ $indexCode ].PHP_EOL;
					if ( array_key_exists( $indexCode, $this->dateFormats ) ) {
						//echo "isdate ".$dateFormats[$indexCode];
						$this->formatRecords['xfrecords'][] = array(
							'type'   => 'date',
							'format' => $this->dateFormats[ $indexCode ]
						);
					} elseif ( array_key_exists( $indexCode, $this->numberFormats ) ) {
						//echo "isnumber ".$this->numberFormats[$indexCode];
						$this->formatRecords['xfrecords'][] = array(
							'type'   => 'number',
							'format' => $this->numberFormats[ $indexCode ]
						);
					} else {
						$isdate = false;
						if ( $indexCode > 0 ) {
							if ( isset( $this->formatRecords[ $indexCode ] ) ) {
//							    die( 'L:'.__LINE__ );
								$formatstr = $this->formatRecords[ $indexCode ];
							}
							//echo '.other.';
//							echo "\nfl=".strlen( $formatstr)." fs=$formatstr=\n";
//							echo "\ncode=".$indexCode." fl=".strlen( $formatstr)." fs=$formatstr=\n";
							$fs = str_replace('\\','', $formatstr );
							if ( $fs && preg_match( '/^[hmsday\/\-:\., ]+$/i', $fs ) ) { // found day and time format
								$isdate    = true;
								$formatstr = str_replace( array( 'yyyy',':mm','mm','dddd','dd', 'h','ss' ), array('Y',':i','m','l','d', 'H','s' ), $fs );
							}
						}

						if ( $isdate ) {
							$this->formatRecords['xfrecords'][] = array(
								'type'   => 'date',
								'format' => $formatstr,
								'code'   => $indexCode
							);
						} else {
//							echo 'fs='.$formatstr.PHP_EOL;
							$this->formatRecords['xfrecords'][] = array(
								'type'   => 'other',
								'format' => '',
								'code'   => $indexCode
							);
						}
					}
//					echo count( $this->formatRecords['xfrecords'] ).' fs='.$formatstr.' ' . PHP_EOL;
					//echo "\n";
					break;
				case self::TYPE_NINETEENFOUR:
					//echo "Type.NINETEENFOUR\n";
					$this->nineteenFour = ( ord( $this->data[ $pos + 4 ] ) === 1 );
					break;
				case self::TYPE_BOUNDSHEET:
					//echo "Type.BOUNDSHEET\n";
					$rec_offset = $this->_GetInt4d( $pos + 4 );
//					$rec_typeFlag = ord($this->_data[$pos + 8]);
					$rec_length = ord( $this->data[ $pos + 10 ] );
					$hidden = false;
					$rec_name   = '';
					if ( $version === self::BIFF8 ) {
						//ord($this->data[$pos + 9])
						$hidden = ord($this->data[$pos + 8]) === 1;
						$chartype = ord( $this->data[ $pos + 11 ] );
						if ( $chartype === 0 ) {
							$rec_name = $this->_substr( $this->data, $pos + 12, $rec_length );
							$rec_name = $this->_Latin1toDef( $rec_name );
						} else {
							$rec_name = $this->_substr( $this->data, $pos + 12, $rec_length * 2 );
							$rec_name = $this->_UTF16toDef( $rec_name );
						}
					} elseif ( $version === self::BIFF7 ) {
						$rec_name = $this->_substr( $this->data, $pos + 11, $rec_length );
					}
					$this->boundsheets[] = array(
						'name'   => $rec_name,
						'offset' => $rec_offset,
						'hidden' => $hidden,
						'active' => false
					);

					break;

				case self::TYPE_WINDOW1:
					$this->activeSheet = $this->_GetInt2d( $pos + 14 );
					break;
			}

			//echo "Code = ".base_convert($r['code'],10,16)."\n";
			$pos    += $length + 4;
			$code   = $this->_GetInt2d( $pos );
			$length = $this->_GetInt2d( $pos + 2 );

			//$r = &$this->nextRecord();
			//echo "1 Code = ".base_convert($r['code'],10,16)."\n";
		}

		foreach ( $this->boundsheets as $key => $val ) {
			$this->sn = $key;
			$this->_parseSheet( $val['offset'] );
			if ( $key === $this->activeSheet ) {
				$this->boundsheets[ $key ]['active'] = true;
			}
		}

		return true;

	}
	protected function _Latin1toDef( $string ) {
		$result = $string;
		if ( $this->defaultEncoding ) {
			$result = mb_convert_encoding( $string, $this->defaultEncoding, 'ISO-8859-1' );
		}

		return $result;
	}
	protected function _UTF16toDef( $string ) {
		$result = $string;
		if ( $this->defaultEncoding && $this->defaultEncoding !== 'UTF-16LE' ) {
			$result = mb_convert_encoding( $string, $this->defaultEncoding, 'UTF-16LE' );
		}

		return $result;
	}

	protected function _parseSheet($spos ) {
		$cont = true;
		// read BOF
//		$code = ord($this->_data[$spos]) | ord($this->_data[$spos + 1]) << 8;
        list( $length, $version, $substreamType ) = $this->parseSubstreamHeader( $spos );

		if ( ( $version !== self::BIFF8 ) && ( $version !== self::BIFF7 ) ) {
			return false;
		}

		if ( $substreamType !== self::WORKSHEET ) {
			return false;
		}
		//echo "Start parse code=".base_convert($code,10,16)." version=".base_convert($version,10,16)." substreamType=".base_convert($substreamType,10,16).""."\n";
		$spos += $length + 4;
		//var_dump($this->formatRecords);
		//echo "code $code $length";

        $this->sheets[ $this->sn ]['maxrow'] = 0;
        $this->sheets[ $this->sn ]['maxcol'] = 0;
        $this->sheets[ $this->sn ]['numRows'] = 0;
        $this->sheets[ $this->sn ]['numCols'] = 0;

		while ( $cont ) {
			//echo "mem= ".memory_get_usage()."\n";
//            $r = &$this->file->nextRecord();
			$lowcode = ord( $this->data[ $spos ] );
			if ( $lowcode === self::TYPE_EOF ) {
				break;
			}
			$code                                = $lowcode | ord( $this->data[ $spos + 1 ] ) << 8;
			$length                              = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
			$spos                                += 4;

			//echo "Code=".base_convert($code,10,16)." $code\n";
			unset( $this->recType );
			$this->multiplier = 1; // need for format with %
			switch ( $code ) {
				case self::TYPE_DIMENSION:
					//echo 'Type_DIMENSION ';
					if ( ! isset( $this->numRows ) ) {
						if ( ( $length === 10 ) || ( $version === self::BIFF7 ) ) {
							$this->sheets[ $this->sn ]['numRows'] = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
							$this->sheets[ $this->sn ]['numCols'] = ord( $this->data[ $spos + 6 ] ) | ord( $this->data[ $spos + 7 ] ) << 8;
						} else {
							$this->sheets[ $this->sn ]['numRows'] = ord( $this->data[ $spos + 4 ] ) | ord( $this->data[ $spos + 5 ] ) << 8;
							$this->sheets[ $this->sn ]['numCols'] = ord( $this->data[ $spos + 10 ] ) | ord( $this->data[ $spos + 11 ] ) << 8;
						}
					}
					//echo 'numRows '.$this->numRows.' '.$this->numCols."\n";
					break;
				case self::TYPE_MERGEDCELLS:
					$cellRanges = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
					for ( $i = 0; $i < $cellRanges; $i ++ ) {
						$fr = ord( $this->data[ $spos + 8 * $i + 2 ] ) | ord( $this->data[ $spos + 8 * $i + 3 ] ) << 8;
						$lr = ord( $this->data[ $spos + 8 * $i + 4 ] ) | ord( $this->data[ $spos + 8 * $i + 5 ] ) << 8;
						$fc = ord( $this->data[ $spos + 8 * $i + 6 ] ) | ord( $this->data[ $spos + 8 * $i + 7 ] ) << 8;
						$lc = ord( $this->data[ $spos + 8 * $i + 8 ] ) | ord( $this->data[ $spos + 8 * $i + 9 ] ) << 8;
						//$this->sheets[$this->sn]['mergedCells'][] = array($fr + 1, $fc + 1, $lr + 1, $lc + 1);
						if ( $lr - $fr > 0 ) {
							$this->sheets[ $this->sn ]['cellsInfo'][ $fr + 1 ][ $fc + 1 ]['rowspan'] = $lr - $fr + 1;
						}
						if ( $lc - $fc > 0 ) {
							$this->sheets[ $this->sn ]['cellsInfo'][ $fr + 1 ][ $fc + 1 ]['colspan'] = $lc - $fc + 1;
						}
					}
					//echo "Merged Cells $cellRanges $lr $fr $lc $fc\n";
					break;
				case self::TYPE_RK:
				case self::TYPE_RK2:
					//echo 'self::TYPE_RK'."\n";
					$row      = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
					$column   = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
					$rknum    = $this->_GetInt4d( $spos + 6 );
					$numValue = $this->_GetIEEE754( $rknum );
					//echo $numValue." ";
					if ( $this->isDate( $spos ) ) {
						list( $string, $raw ) = $this->createDate( $numValue );
					} else {
						$raw = $numValue;
						if ( isset( $this->columnsFormat[ $column + 1 ] ) ) {
							$this->curFormat = $this->columnsFormat[ $column + 1 ];
						}
						$string = sprintf( $this->curFormat, $numValue * $this->multiplier );
						//$this->addcell(RKRecord($r));
					}
					$this->addCell( $row, $column, $string, $raw );
					//echo "Type_RK $row $column $string $raw {$this->curformat}\n";
					break;
				case self::TYPE_LABELSST:
					$row    = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
					$column = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
//					$xfindex = ord($this->_data[$spos + 4]) | ord($this->_data[$spos + 5]) << 8;
					$index = $this->_GetInt4d( $spos + 6 );
					//var_dump($this->sst);
					$this->addCell( $row, $column, $this->sst[ $index ] );
					//echo "LabelSST $row $column $string\n";
					break;
				case self::TYPE_MULRK:
					$row      = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
					$colFirst = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
					$colLast  = ord( $this->data[ $spos + $length - 2 ] ) | ord( $this->data[ $spos + $length - 1 ] ) << 8;
					$columns  = $colLast - $colFirst + 1;
					$tmppos   = $spos + 4;
					for ( $i = 0; $i < $columns; $i ++ ) {
						$numValue = $this->_GetIEEE754( $this->_GetInt4d( $tmppos + 2 ) );
						if ( $this->isDate( $tmppos - 4 ) ) {
							list( $string, $raw ) = $this->createDate( $numValue );
						} else {
							$raw = $numValue;
							if ( isset( $this->columnsFormat[ $colFirst + $i + 1 ] ) ) {
								$this->curFormat = $this->columnsFormat[ $colFirst + $i + 1 ];
							}
							$string = sprintf( $this->curFormat, $numValue * $this->multiplier );
						}
						//$rec['rknumbers'][$i]['xfindex'] = ord($rec['data'][$pos]) | ord($rec['data'][$pos+1]) << 8;
						$tmppos += 6;
						$this->addCell( $row, $colFirst + $i, $string, $raw );
						//echo "MULRK $row ".($colFirst + $i)." $string\n";
					}
					//MulRKRecord($r);
					// Get the individual cell records from the multiple record
					//$num = ;

					break;
				case self::TYPE_NUMBER:
					$row    = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
					$column = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
					$tmp    = unpack( 'ddouble', $this->_substr( $this->data, $spos + 6, 8 ) ); // It machine machine dependent
					if ( $this->isDate( $spos ) ) {
						list( $string, $raw ) = $this->createDate( $tmp['double'] );
						//   $this->addcell(DateRecord($r, 1));
					} else {
						//$raw = $tmp[''];
						if ( isset( $this->columnsFormat[ $column + 1 ] ) ) {
							$this->curFormat = $this->columnsFormat[ $column + 1 ];
						}
						$raw    = $this->createNumber( $spos );
						$string = sprintf( $this->curFormat, $raw * $this->multiplier );

						//   $this->addcell(NumberRecord($r));
					}
					$this->addCell( $row, $column, $string, $raw );
					//echo "Number $row $column $string\n";
					break;
				case self::TYPE_FORMULA:
				case self::TYPE_FORMULA2:
					$row    = ord( $this->data[ $spos ] ) | ord( $this->data[ $spos + 1 ] ) << 8;
					$column = ord( $this->data[ $spos + 2 ] ) | ord( $this->data[ $spos + 3 ] ) << 8;
					/*
	$byte6 = ord($this->_data[$spos + 6]);
	$byte12 = ord($this->_data[$spos + 12]);
	$byte13 = ord($this->_data[$spos + 13]);

	if ( $byte6 === 0 && $byte12 === 255 && $byte13 === 255 ) {
	//String formula. Result follows in a STRING record
	//echo "FORMULA $row $column Formula with a string<br>\n";
	} else if ($byte6 === 1 && $byte12 === 255 && $byte13 === 255 ) {
	//Boolean formula. Result is in +2; 0=false,1=true
	} else if ($byte6 === 2 && $byte12 === 255 && $byte13 === 255) {
	//Error formula. Error code is in +2;
	} else if ( $byte6 === 3 && $byte12 === 255 && $byte13 === 255) {
	//Formula result is a null string.
	*/
					if ( ! ( ord( $this->data[ $spos + 6 ] ) < 4 && ord( $this->data[ $spos + 12 ] ) === 255 && ord( $this->data[ $spos + 13 ] ) === 255 ) ) {
						// result is a number, so first 14 bytes are just like a _NUMBER record
						$tmp = unpack( 'ddouble', $this->_substr( $this->data, $spos + 6, 8 ) ); // It machine machine dependent
						if ( $this->isDate( $spos ) ) {
							list( $string, $raw ) = $this->createDate( $tmp['double'] );
							//   $this->addcell(DateRecord($r, 1));
						} else {
							//$raw = $tmp[''];
							if ( isset( $this->columnsFormat[ $column + 1 ] ) ) {
								$this->curFormat = $this->columnsFormat[ $column + 1 ];
							}
							$raw    = $this->createNumber( $spos );
							$string = sprintf( $this->curFormat, $raw * $this->multiplier );

							//   $this->addcell(NumberRecord($r));
						}
						$this->addCell( $row, $column, $string, $raw );
						//echo "Number $row $column $string\n";
					}
					break;
				case self::TYPE_BOOLERR:
					$row    = $this->_GetInt2d( $spos );
					$column = $this->_GetInt2d( $spos + 2 );
					$string = ord( $this->data[ $spos + 6 ] );
					$this->addCell( $row, $column, $string );
					//echo 'Type_BOOLERR '."\n";
					break;
				case self::TYPE_ROW:
				case self::TYPE_DBCELL:
				case self::TYPE_MULBLANK:
					break;
				case self::TYPE_LABEL:
					$row    = $this->_GetInt2d( $spos );
					$column = $this->_GetInt2d( $spos );
					$this->addCell( $row, $column, $this->_substr( $this->data, $spos + 8, ord( $this->data[ $spos + 6 ] ) | ord( $this->data[ $spos + 7 ] ) << 8 ) );

					// $this->addcell(LabelRecord($r));
					break;

				case self::TYPE_EOF:
					$cont = false;
					break;
				default:
					//echo ' unknown :'.base_convert($r['code'],10,16)."\n";
					break;

			}
			$spos += $length;
		}

		if ( $this->sheets[ $this->sn ]['numRows'] === 0 ) {
			$this->sheets[ $this->sn ]['numRows'] = $this->sheets[ $this->sn ]['maxrow'];
		}
		if ( $this->sheets[ $this->sn ]['numCols'] === 0 ) {
			$this->sheets[ $this->sn ]['numCols'] = $this->sheets[ $this->sn ]['maxcol'];
		}

		return true;
	}

	//}}}
	//{{{ createDate()

	protected function _GetIEEE754( $rknum ) {
		if ( ( $rknum & 0x02 ) !== 0 ) {
			$value = $rknum >> 2;
		} else {
//mmp
// first comment out the previously existing 7 lines of code here
//                $tmp = unpack("d", pack("VV", 0, ($rknum & 0xfffffffc)));
//                //$value = $tmp[''];
//                if (array_key_exists(1, $tmp)) {
//                    $value = $tmp[1];
//                } else {
//                    $value = $tmp[''];
//                }
// I got my info on IEEE754 encoding from
// http://research.microsoft.com/~hollasch/cgindex/coding/ieeefloat.html
// The RK format calls for using only the most significant 30 bits of the
// 64 bit floating point value. The other 34 bits are assumed to be 0
// So, we use the upper 30 bits of $rknum as follows...
			$sign     = ( $rknum & 0x80000000 ) >> 31;
			$exp      = ( $rknum & 0x7ff00000 ) >> 20;
			$mantissa = ( 0x100000 | ( $rknum & 0x000ffffc ) );
			$value    = $mantissa / pow( 2, 20 - ( $exp - 1023 ) );
			if ( $sign ) {
				$value = - 1 * $value;
			}
//end of changes by mmp

		}

		if ( ( $rknum & 0x01 ) !== 0 ) {
			$value /= 100;
		}

		return $value;
	}

	protected function isDate( $spos ) {
		//$xfindex = GetInt2d(, 4);
		$xfindex = ord( $this->data[ $spos + 4 ] ) | ord( $this->data[ $spos + 5 ] ) << 8;
//      echo 'check is date '.$xfindex.' '.$this->formatRecords['xfrecords'][$xfindex]['type']."\n";


		if ( $this->formatRecords['xfrecords'][ $xfindex ]['type'] === 'date' ) {
			$this->curFormat = $this->formatRecords['xfrecords'][ $xfindex ]['format'];
			$this->recType   = 'date';

			return true;
		}

		if ( $this->formatRecords['xfrecords'][ $xfindex ]['type'] === 'number' ) {
			$this->curFormat = $this->formatRecords['xfrecords'][ $xfindex ]['format'];
			$this->recType   = 'number';
			if ( ( $xfindex === 0x9 ) || ( $xfindex === 0xa ) ) {
				$this->multiplier = 100;
			}
		} else {
			$this->curFormat = $this->defaultFormat;
			$this->recType   = 'unknown';
		}

		return false;
	}

	/**
	 * Convert the raw Excel date into a human readable format
	 *
	 * Dates in Excel are stored as number of seconds from an epoch.  On
	 * Windows, the epoch is 30/12/1899 and on Mac it's 01/01/1904
	 *
	 * @param integer $timevalue The raw Excel value to convert
	 *
	 * @return array First element is the converted date, the second element is number a unix timestamp
	 */
	public function createDate( $timevalue ) {
//		$offset = ($timeoffset===null)? date('Z') : $timeoffset * 3600;
		if ($timevalue > 1) {
			$timevalue -= ( $this->nineteenFour ? 24107 : 25569 );
		}
		$ts = round($timevalue * 24 * 3600);
		$string = $this->datetimeFormat ? gmdate( $this->datetimeFormat, $ts ) : gmdate( $this->curFormat, $ts );
		return array( $string, $ts );

	}

	protected function addCell( $row, $col, $string, $raw = '' ) {
		//echo "ADD cel $row-$col $string\n";
		$this->sheets[ $this->sn ]['maxrow']                                                      = max( $this->sheets[ $this->sn ]['maxrow'], $row );
		$this->sheets[ $this->sn ]['maxcol']                                                      = max( $this->sheets[ $this->sn ]['maxcol'], $col );
		$this->sheets[ $this->sn ]['cells'][ $row ][ $col ] = $string;
		if ( $raw ) {
			$this->sheets[ $this->sn ]['cellsInfo'][ $row ][ $col ]['raw'] = $raw;
		}
		if ( isset( $this->recType ) ) {
			$this->sheets[ $this->sn ]['cellsInfo'][ $row ][ $col ]['type'] = $this->recType;
		}

	}

	protected function createNumber( $spos ) {
		$rknumhigh = $this->_GetInt4d( $spos + 10 );
		$rknumlow  = $this->_GetInt4d( $spos + 6 );
		//for ($i=0; $i<8; $i++) { echo ord($this->_data[$i+$spos+6]) . " "; } echo "<br>";
		$sign         = ( $rknumhigh & 0x80000000 ) >> 31;
		$exp          = ( $rknumhigh & 0x7ff00000 ) >> 20;
		$mantissa     = ( 0x100000 | ( $rknumhigh & 0x000fffff ) );
		$mantissalow1 = ( $rknumlow & 0x80000000 ) >> 31;
		$mantissalow2 = ( $rknumlow & 0x7fffffff );
		$value        = $mantissa / pow( 2, 20 - ( $exp - 1023 ) );
		if ( $mantissalow1 !== 0 ) {
			$value += 1 / pow( 2, 21 - ( $exp - 1023 ) );
		}
		$value += $mantissalow2 / pow( 2, 52 - ( $exp - 1023 ) );
		//echo "Sign = $sign, Exp = $exp, mantissahighx = $mantissa, mantissalow1 = $mantissalow1, mantissalow2 = $mantissalow2<br>\n";
		if ( $sign ) {
			$value = - 1 * $value;
		}

		return $value;
	}

	/**
	 * Set the encoding method
	 *
	 * @param string $encoding Encoding to use
	 *
	 * @access public
	 */
	public function setOutputEncoding( $encoding ) {
		$this->defaultEncoding = $encoding;
	}


	/**
	 * Set the default number format
	 *
	 * @access public
	 *
	 * @param string $sFormat Default  format
	 */
	public function setDefaultFormat( $sFormat ) {
		$this->defaultFormat = $sFormat;
	}

	/**
	 * Force a column to use a certain format
	 *
	 * @access public
	 *
	 * @param integer $column Column number
	 * @param string $sFormat Format
	 */
	public function setColumnFormat( $column, $sFormat ) {
		$this->columnsFormat[ $column ] = $sFormat;
	}
	protected function _strlen( $str ) {
		return (ini_get('mbstring.func_overload') & 2) ? mb_strlen($str , '8bit') : strlen($str);
	}
	protected function _strpos( $haystack, $needle, $offset = 0 ) {
		return (ini_get('mbstring.func_overload') & 2) ? mb_strpos( $haystack, $needle, $offset , '8bit') : strpos($haystack, $needle, $offset);
	}
	protected function _substr( $str, $start, $length = null ) {
		return (ini_get('mbstring.func_overload') & 2) ? mb_substr( $str, $start, ($length === null) ? mb_strlen($str,'8bit') : $length, '8bit') : substr($str, $start, ($length === null) ? strlen($str) : $length );
	}
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
