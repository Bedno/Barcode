<?
// Compact barcode creator in PHP.
// Renders single or multiple barcodes of various types to browser or file.
// Produces Code39, Interleave25, Standard25, Code93, RoyalMail, or PostNet to JPG, PNG, GIF or BMP.
// Configurable options for type, height, width, rotation, ratio, inverted, checkdigit and subtitle.
// URL arguments:
//   t      Text         Barcode text to render as a single image.  Omit to use interactive form.
//   1-999  Lines        Multiple texts to output as a web page.
//   m      Mode         1=Code39; 2=Interleave25; 3=Standard25; 4=Code93 (default); 5=RoyalMail; 6=PostNet;
//   o      Output       1=PNG (default); 2=JPG; 3=GIF; 4=BMP;
//   b      Barwidth     1=small; 2=medium (default); 3=large;
//   h      Height       50 (default=50;
//   r      Rotate       0=0 (default); 1=90; 2=180; 3=270;
//   w      W/N Ratio    2=2/1 (default);  3=3/1;
//   s      SubTitle     0=No;  1=Yes (default);
//   i      Inverted     0=No (default);  1=Yes;
//   c      CheckDigit   0=No (default); 1=Yes;
// Ex: Generate a PNG of a single barcode: <IMG SRC="barcode.php?t=123456">
// Ex: Generate HTML page of multiple barcodes: http://.../barcode.php?1=1234&2=5678&3=...

// Beautify the constants.
define("BC_TYPE_CODE39", 1);
define("BC_TYPE_INTER25", 2);
define("BC_TYPE_STD25", 3);
define("BC_TYPE_CODE93", 4);
define("BC_TYPE_ROYMAIL4", 5);
define("BC_TYPE_POSTNET", 6);
define("BC_IMG_TYPE_PNG", 1);
define("BC_IMG_TYPE_JPG", 2);
define("BC_IMG_TYPE_GIF", 3);
define("BC_IMG_TYPE_WBMP", 4);
define("BC_ROTATE_0", 0);
define("BC_ROTATE_90", 1);
define("BC_ROTATE_180", 2);
define("BC_ROTATE_270", 3);

// Main application.
// If only using this file as an include, comment out next line to load supporting functions but not generate web page.

BarCodePage();  // Uncomment this line to use as standalone application web page.

function BarCodePage() {

  global $bc_text, $bc_mode, $bc_output, $bc_barwidth, $bc_height, $bc_rotate, $bc_wnratio, $bc_subtitle, $bc_inverted, $bc_checkdigit;

  // Fetch an arg from GET or POST, handle default and range validation.
  function ArgGet($arg_code, $arg_dflt, $arg_min, $arg_max) {
    $arg_out = '';
    if (isset($_GET[strtolower($arg_code)])) { $arg_out=$_GET[strtolower($arg_code)]; } else { if (isset($_GET[strtoupper($arg_code)])) { $arg_out=$_GET[strtoupper($arg_code)]; } else { if (isset($_POST[strtolower($arg_code)])) { $arg_out=$_POST[strtolower($arg_code)]; } else { if (isset($_POST[strtoupper($arg_code)])) { $arg_out=$_POST[strtoupper($arg_code)]; } } } }
    if ( (! stristr($arg_code,'t')) && (! stristr($arg_code,'n')) && ( (strlen($arg_out) < 1) || ($arg_out > $arg_max) || ($arg_out < $arg_min) ) ) { $arg_out = $arg_dflt; }
    return($arg_out);
  }

  // Display web form to generate arbitrary barcodes.
  function BarcodeForm () {
    global $bc_text, $bc_mode, $bc_output, $bc_barwidth, $bc_height, $bc_rotate, $bc_wnratio, $bc_subtitle, $bc_inverted, $bc_checkdigit;
    function ArgOpt($arg_val, $arg_sel, $arg_text) {
      print "<option value='".$arg_sel."'";
      if ($arg_val == $arg_sel) { print " selected"; }
      print ">".$arg_text."</option>\n";
    }
    print "<HTML><BODY><font face=Arial size=2><GENERATE BARCODE<br>\n";
    print "<FORM METHOD=GET ACTION='barcode.php' target='barcodeout'>\n";
    print "Text: <INPUT TYPE='text' NAME='t' VALUE='".$bc_text."' maxlength='40' size=40><br>\n";
    print "BarCode Type:\n";
    print "<SELECT NAME='m'>\n";
    ArgOpt($bc_mode, '1', 'Code39');
    ArgOpt($bc_mode, '2', 'Interleave25');
    ArgOpt($bc_mode, '3', 'Standard25');
    ArgOpt($bc_mode, '4', 'Code93');
    ArgOpt($bc_mode, '5', 'RoyalMail');
    ArgOpt($bc_mode, '6', 'PostNet');
    print "</SELECT><br>\n";
    print "Bar Width: \n";
    print "<SELECT NAME='b'>\n";
    ArgOpt($bc_barwidth, '1', 'Small');
    ArgOpt($bc_barwidth, '2', 'Medium');
    ArgOpt($bc_barwidth, '3', 'Large');
    print "</SELECT><br>\n";
    print "Bar Height: \n";
    print "<SELECT NAME='h'>\n";
    ArgOpt($bc_height, '25', '25');
    ArgOpt($bc_height, '50', '50');
    ArgOpt($bc_height, '75', '75');
    ArgOpt($bc_height, '100', '100');
    ArgOpt($bc_height, '125', '125');
    ArgOpt($bc_height, '150', '150');
    print "</SELECT><br>\n";
    print "Wide to Narrow Ratio: \n";
    print "<SELECT NAME='w'>\n";
    ArgOpt($bc_wnratio, '2', 'x2');
    ArgOpt($bc_wnratio, '3', 'x3');
    print "</SELECT><br>\n";
    print "Image Type: \n";
    print "<SELECT NAME='o'>\n";
    ArgOpt($bc_output, '1', 'PNG');
    ArgOpt($bc_output, '2', 'JPG');
    ArgOpt($bc_output, '3', 'GIF');
    ArgOpt($bc_output, '4', 'BMP');
    print "</SELECT><br>\n";
    print "Caption: \n";
    print "<SELECT NAME='s'>\n";
    ArgOpt($bc_subtitle, '0', 'No');
    ArgOpt($bc_subtitle, '1', 'Yes');
    print "</SELECT><br>\n";
    print "Inverted: \n";
    print "<SELECT NAME='i'>\n";
    ArgOpt($bc_inverted, '0', 'No');
    ArgOpt($bc_inverted, '1', 'Yes');
    print "</SELECT><br>\n";
    print "Check Digit: \n";
    print "<SELECT NAME='c'>\n";
    ArgOpt($bc_checkdigit, '0', 'No');
    ArgOpt($bc_checkdigit, '1', 'Yes');
    print "</SELECT><br>\n";
    print "Rotate: \n";
    print "<SELECT NAME='r'>\n";
    ArgOpt($bc_rotate, '0', 'No');
    ArgOpt($bc_rotate, '1', '90 degrees');
    ArgOpt($bc_rotate, '2', '180 degrees');
    ArgOpt($bc_rotate, '3', '270 degrees');
    print "</SELECT><br>\n";
    print "<INPUT TYPE='submit' value='GENERATE'>\n";
    print "</FORM></font></body></html>\n";
  }
  $bc_barcode_cnt = 0;
  for ($bc_loop = 1; $bc_loop < 1000; $bc_loop++) {
    $bc_barcode_tmp = '';
    if (isset($_GET[$bc_loop])) { $bc_barcode_tmp=$_GET[$bc_loop]; } else { if (isset($_POST[$bc_loop])) { $bc_barcode_tmp=$_POST[$bc_loop]; } }
    if (strlen($bc_barcode_tmp) >= 1) {
      $bc_barcodes[$bc_barcode_cnt] = $bc_barcode_tmp;
      $bc_barcode_cnt++;
    }
  }

  $bc_text = strtoupper(ArgGet('t', '', '', ''));
  if (! $bc_text) { $bc_text = strtoupper(ArgGet('n', '', '', '')); }
  $bc_mode = ArgGet('m', 4, 1, 6);
  $bc_output = ArgGet('o', 1, 1, 4);
  $bc_barwidth = ArgGet('b', 1, 1, 3);
  $bc_height = ArgGet('h', 50, 25, 200);
  $bc_rotate = ArgGet('r', 0, 0, 3);
  $bc_wnratio = ArgGet('w', 2, 2, 3);
  $bc_subtitle = ArgGet('s', 1, 0, 1);
  $bc_inverted = ArgGet('i', 0, 0, 1);
  $bc_checkdigit = ArgGet('c', 0, 0, 1);

  if ( (! $bc_text) && ($bc_barcode_cnt < 1) ) {
    BarcodeForm();
  } else {
    if ($bc_barcode_cnt > 0) {
      print "<html><head><title>BARCODES</title></head><body>\n";
      for ($bc_loop = 0; $bc_loop < $bc_barcode_cnt; $bc_loop++) {
        $bc_url = 'barcode.php?m='.$bc_mode.'&o='.$bc_output.'&b='.$bc_barwidth.'&h='.$bc_height.'&r='.$bc_rotate.'&w='.$bc_wnratio.'&s='.$bc_subtitle.'&i='.$bc_inverted.'&c='.$bc_checkdigit.'&t='.$bc_barcodes[$bc_loop];
        print  '<IMG SRC="'.$bc_url.'" style="{margin-bottom:'.round($bc_height/1.5).'}"><br>'."\n";
      }
      print "</body></html>\n";
    } else {
      BarCode($bc_mode, $bc_text, $bc_barwidth, $bc_wnratio, $bc_barwidth, $bc_inverted, $bc_height, $bc_output, $bc_subtitle, $bc_rotate, $bc_checkdigit, '');
    }
  }

}

// Graphical generation routines from GPL source by Walter Cattebeke
// May require GD-1.8 or higher and GIF may not work in some GD versions.

function getBarcodeText($i, $txt){
  switch ($i) {
    case BC_TYPE_CODE39:
    /*
      Code 39 needs starting and ending special control chars (ascii asterisk).
    */
      return strtoupper("*" . $txt . "*"); // add starting and ending control chars to original text
      break;
    case BC_TYPE_INTER25:
    /*
      Interleaved 2 of 5 needs an even number of chars and starting - ending special control chars.
      Because of the "interleave" this control chars are made of the "asterisk asterisk" (**) and
      "plus minus" (+-) combinations.
    */
      if (strlen($txt) % 2 == 0){ return "**" . $txt . "+-"; } // OK, even length
      else{ return "**" . "0" . $txt . "+-"; } // make it even by adding a starting 0
      break;
    case BC_TYPE_STD25:
    /*
      Standard 2 of 5 needs narrow spaces between bars. This is done by "interleaving" chars with
      special control char "dollar" ($).
      It also uses a special start (%!) stop (&!) combination.
    */
      $txt1 = $txt . $txt;
      for ($j=0; $j<strlen($txt); $j++){
        $txt1[$j*2] = $txt[$j];
        $txt1[$j*2+1] = "$";
      }
      return "%!" . $txt1 . "&!";
      break;
    case BC_TYPE_CODE93:
    /*
      Code 93 needs starting and ending special control chars (ascii asterisk).
      Special @ sign is used to put safe final bar
    */
      return strtoupper("*" . $txt . "*" . "@"); // add starting and ending control chars to original text
      break;
    case BC_TYPE_ROYMAIL4:
    /*
      Royal Mail needs starting and ending special control chars (ascii '(' and ')').
    */
      return strtoupper("*" . $txt . "+"); // add starting and ending control chars to original text
      break;
    case BC_TYPE_POSTNET: 
    /*
      Postnet needs starting and ending special control chars (ascii asterisk).
    */
      return strtoupper("*" . $txt . "*"); // add starting and ending control chars to original text
      break;
    default:
      return $txt;
      break;
  }
}

function checkCheckDigit($type, $ck){
  switch ($type) {
    case BC_TYPE_CODE93:
    case BC_TYPE_ROYMAIL4:
    case BC_TYPE_POSTNET:
      return TRUE; // mandatory check digit
      break;
    case BC_TYPE_STD25:
    case BC_TYPE_INTER25:
    case BC_TYPE_CODE39:
      return $ck; // user selection
      break;
    default:
      return FALSE; // no check digit
      break;
  }
}

function checkCharGap($i, $ig){
  switch ($i) {
    case BC_TYPE_CODE39:
    case BC_TYPE_ROYMAIL4:
    case BC_TYPE_POSTNET:
      return $ig; // use gap
      break;
    case BC_TYPE_STD25:
    case BC_TYPE_INTER25:
    case BC_TYPE_CODE93:
      return 0; // no gap
      break;
    default:
      return 1; // return 1 pixel width gap
      break;
  }
}

function checkWideToNarrow($i, $wtn){
  switch ($i) {
    case BC_TYPE_CODE39:
    case BC_TYPE_STD25:
    case BC_TYPE_INTER25:
      return $wtn; // user selection
      break;
    case BC_TYPE_CODE93:
    case BC_TYPE_ROYMAIL4:
    case BC_TYPE_POSTNET:
      return 1; // no wide2narrow in this type
      break;
    default:
      return 2; // return wide to narrow factor of 2
      break;
  }
}

function getCharCount($i, $txt){
  switch ($i) {
    case BC_TYPE_CODE39:
    case BC_TYPE_CODE93:
    case BC_TYPE_ROYMAIL4:
    case BC_TYPE_POSTNET:
      return strlen($txt); // same as message length
      break;
    case BC_TYPE_STD25:
    case BC_TYPE_INTER25:
      return strlen($txt) / 2; // half the message length (interleaved)
      break;
    default:
      return strlen($txt); // same as message length
      break;
  }
}

function getBarcodeLength($i, $txt, $xd, $wtn, $qz, $ig){
  switch ($i) {
    case BC_TYPE_CODE39:
      return strlen($txt) * ( 6 * $xd + 3 * $xd * $wtn) // message width
        +  (strlen($txt) - 1) * $ig // interchar gap width
        + 2 * $qz; // quiet zone width
      break;
    case BC_TYPE_INTER25:
      return (strlen($txt) - 4) * ( 3 * $xd + 2 * $xd * $wtn) // message width
        + 2 * $qz // quiet zone width
        + ( 6 * $xd + 1 * $xd * $wtn); // control chars width
      break;
    case BC_TYPE_STD25:
      return (strlen($txt) - 4) / 2 * ( 3 * $xd + 2 * $xd * $wtn) // message width
        + (strlen($txt) - 4) / 2 * ( 5 * $ig ) // interchar gap width
        + 2 * $qz // quiet zone width
        + ( 8 * $xd + 4 * $xd * $wtn); // control chars width
      break;
    case BC_TYPE_CODE93:
      return (strlen($txt) - 1) * 9 * $xd // message width
        + 2 * $qz // quiet zone width
        + 1 * $xd; // final bar width
      break;
    case BC_TYPE_ROYMAIL4:
      return (strlen($txt) - 2) * 7 * $xd + 2 * $xd // message width
        + (strlen($txt) - 1) * $ig // interchar gap width
        + 2 * $qz; // quiet zone width
      break;
    case BC_TYPE_POSTNET:
      return (strlen($txt) - 2) * 9 * $xd + 2 * $xd // message width
        + (strlen($txt) - 1) * $ig // interchar gap width
        + 2 * $qz; // quiet zone width
      break;
    default:
      return 0;
      break;
  }
}

function barcodeImgFunction($i){
  switch ($i) {
    case BC_IMG_TYPE_PNG: return "imagepng"; break;
    case BC_IMG_TYPE_JPG: return "imagejpeg"; break;
    case BC_IMG_TYPE_GIF: return "imagegif"; break;
    case BC_IMG_TYPE_WBMP: return "imagewbmp"; break;
    default: return "imagepng"; break;
  }
}

function barcodeFileExt($i){
  switch ($i) {
    case BC_IMG_TYPE_PNG: return "png"; break;
    case BC_IMG_TYPE_JPG: return "jpg"; break;
    case BC_IMG_TYPE_GIF: return "gif"; break;
    case BC_IMG_TYPE_WBMP: return "bmp"; break;
    default: return "png"; break;
  }
}


function barcodeHeaderContent($i){
  switch ($i) {
    case BC_IMG_TYPE_PNG: return "image/png"; break;
    case BC_IMG_TYPE_JPG: return "image/jpeg"; break;
    case BC_IMG_TYPE_GIF: return "image/gif"; break;
    case BC_IMG_TYPE_WBMP: return "image/bmp"; break;
    default: return "image/png"; break;
  }
}

function code39spec($i) {
/*
  Code 39 specification for symbols (index represented by ascii codes)
  n -> narrow bar
  w -> wide bar
*/
  $arrSpec = array (
    48 => "nnnwwnwnn", // 0
    49 => "wnnwnnnnw", // 1
    50 => "nnwwnnnnw", // 2
    51 => "wnwwnnnnn", // 3
    52 => "nnnwwnnnw", // 4
    53 => "wnnwwnnnn", // 5
    54 => "nnwwwnnnn", // 6
    55 => "nnnwnnwnw", // 7
    56 => "wnnwnnwnn", // 8
    57 => "nnwwnnwnn", // 9
    65 => "wnnnnwnnw", // A
    66 => "nnwnnwnnw", // B
    67 => "wnwnnwnnn", // C
    68 => "nnnnwwnnw", // D
    69 => "wnnnwwnnn", // E
    70 => "nnwnwwnnn", // F
    71 => "nnnnnwwnw", // G
    72 => "wnnnnwwnn", // H
    73 => "nnwnnwwnn", // I
    74 => "nnnnwwwnn", // J
    75 => "wnnnnnnww", // K
    76 => "nnwnnnnww", // L
    77 => "wnwnnnnwn", // M
    78 => "nnnnwnnww", // N
    79 => "wnnnwnnwn", // O
    80 => "nnwnwnnwn", // P
    81 => "nnnnnnwww", // Q
    82 => "wnnnnnwwn", // R
    83 => "nnwnnnwwn", // S
    84 => "nnnnwnwwn", // T
    85 => "wwnnnnnnw", // U
    86 => "nwwnnnnnw", // V
    87 => "wwwnnnnnn", // W
    88 => "nwnnwnnnw", // X
    89 => "wwnnwnnnn", // Y
    90 => "nwwnwnnnn", // Z
    45 => "nwnnnnwnw", // -
    46 => "wwnnnnwnn", // .
    32 => "nwwnnnwnn", // SPACE
    36 => "nwnwnwnnn", // $
    47 => "nwnwnnnwn", // /
    43 => "nwnnnwnwn", // +
    37 => "nnnwnwnwn", // %
    42 => "nwnnwnwnn"  // *
  );
  return $arrSpec[$i];
}

function code93spec($i) {
/*
  Code 93 specification for symbols (index represented by ascii codes)
  1 -> bar (black bar)
  0 -> space (white bar)
*/
  $arrSpec = array (
    48 => "100010100", // 0
    49 => "101001000", // 1
    50 => "101000100", // 2
    51 => "101000010", // 3
    52 => "100101000", // 4
    53 => "100100100", // 5
    54 => "100100010", // 6
    55 => "101010000", // 7
    56 => "100010010", // 8
    57 => "100001010", // 9
    65 => "110101000", // A
    66 => "110100100", // B
    67 => "110100010", // C
    68 => "110010100", // D
    69 => "110010010", // E
    70 => "110001010", // F
    71 => "101101000", // G
    72 => "101100100", // H
    73 => "101100010", // I
    74 => "100110100", // J
    75 => "100011010", // K
    76 => "101011000", // L
    77 => "101001100", // M
    78 => "101000110", // N
    79 => "100101100", // O
    80 => "100010110", // P
    81 => "110110100", // Q
    82 => "110110010", // R
    83 => "110101100", // S
    84 => "110100110", // T
    85 => "110010110", // U
    86 => "110011010", // V
    87 => "101101100", // W
    88 => "101100110", // X
    89 => "100110110", // Y
    90 => "100111010", // Z
    45 => "100101110", // -
    46 => "111010100", // .
    32 => "111010010", // SPACE
    36 => "111001010", // $
    47 => "101101110", // /
    43 => "101110110", // +
    37 => "110101110", // %
    60 => "100100110", // < -> ($)
    61 => "111011010", // = -> (%)
    62 => "111010110", // > -> (/)
    63 => "100110010", // ? -> (+)
    42 => "101011110", // *
    64 => "1        "  // @ special safe final bar !!!
  );
  return trim($arrSpec[$i]);
}

function code393char2value($i) {
/*
  Code 93 specification for symbols (index represented by ascii codes)
  Used for check digit calculation.
*/
  $arrSpec = array (
    48 => "0", // 0
    49 => "1", // 1
    50 => "2", // 2
    51 => "3", // 3
    52 => "4", // 4
    53 => "5", // 5
    54 => "6", // 6
    55 => "7", // 7
    56 => "8", // 8
    57 => "9", // 9
    65 => "10", // A
    66 => "11", // B
    67 => "12", // C
    68 => "13", // D
    69 => "14", // E
    70 => "15", // F
    71 => "16", // G
    72 => "17", // H
    73 => "18", // I
    74 => "19", // J
    75 => "20", // K
    76 => "21", // L
    77 => "22", // M
    78 => "23", // N
    79 => "24", // O
    80 => "25", // P
    81 => "26", // Q
    82 => "27", // R
    83 => "28", // S
    84 => "29", // T
    85 => "30", // U
    86 => "31", // V
    87 => "32", // W
    88 => "33", // X
    89 => "34", // Y
    90 => "35", // Z
    45 => "36", // -
    46 => "37", // .
    32 => "38", // SPACE
    36 => "39", // $
    47 => "40", // /
    43 => "41", // +
    37 => "42", // %
    60 => "43", // < -> ($)
    61 => "44", // = -> (%)
    62 => "45", // > -> (/)
    63 => "46", // ? -> (+)
    64 => "1        "  // @ special safe final bar !!!
  );
  return trim($arrSpec[$i]);
}

function code393value2char($i) {
/*
  Code 93 specification for symbols (index represented by ascii codes)
  Used for check digit calculation.
*/
  $arrSpec = array (
    0 => "0", // 0
    1 => "1", // 1
    2 => "2", // 2
    3 => "3", // 3
    4 => "4", // 4
    5 => "5", // 5
    6 => "6", // 6
    7 => "7", // 7
    8 => "8", // 8
    9 => "9", // 9
    10 => "A", // A
    11 => "B", // B
    12 => "C", // C
    13 => "D", // D
    14 => "E", // E
    15 => "F", // F
    16 => "G", // G
    17 => "H", // H
    18 => "I", // I
    19 => "J", // J
    20 => "K", // K
    21 => "L", // L
    22 => "M", // M
    23 => "N", // N
    24 => "O", // O
    25 => "P", // P
    26 => "Q", // Q
    27 => "R", // R
    28 => "S", // S
    29 => "T", // T
    30 => "U", // U
    31 => "V", // V
    32 => "W", // W
    33 => "X", // X
    34 => "Y", // Y
    35 => "Z", // Z
    36 => "-", // -
    37 => ".", // .
    38 => " ", // SPACE
    39 => "$", // $
    40 => "/", // /
    41 => "+", // +
    42 => "%", // %
    43 => "<", // < -> ($)
    44 => "=", // = -> (%)
    45 => ">", // > -> (/)
    46 => "?", // ? -> (+)
  );
  return $arrSpec[$i];
}

function std25spec($i) {
/*
  Standard 2 of 5 specification for symbols (index represented by ascii codes).
  Also used for Interleaved 2 of 5.
  Some special signs ($*+-%&!) are introduced to manage special control chars.
  n -> narrow bar
  w -> wide bar
*/
  $arrSpec = array (
    48 => "nnwwn", // 0
    49 => "wnnnw", // 1
    50 => "nwnnw", // 2
    51 => "wwnnn", // 3
    52 => "nnwnw", // 4
    53 => "wnwnn", // 5
    54 => "nwwnn", // 6
    55 => "nnnww", // 7
    56 => "wnnwn", // 8
    57 => "nwnwn", // 9
    36 => "nnnnn", // dollar sign ($) used for standard 2 of 5
    37 => "wwn  ", // percent sign (%) used for control char
    38 => "wnw  ", // ampersand sign (&) used for control char
    33 => "nnn  ", //  sign (!) used for control char
    42 => "nn   ", // asterisk sign (*) used for control char
    43 => "wn   ", // plus sign (+) used for control char
    45 => "n    "  // minus sign (-) used for control char
  );
  return $arrSpec[$i];
}

function postnetspec($i) {
/*
  Royal Mail 4-State specification for symbols (index represented by ascii codes).
  Some special signs (*) are introduced to manage special control chars.
  f -> full bar
  u -> upper half bar
  l -> lower half bar
*/
  $arrSpec = array (
    48 => "fffflflfl", // 0
    49 => "lflflffff", // 1
    50 => "lflffflff", // 2
    51 => "lflfffffl", // 3
    52 => "lffflflff", // 4
    53 => "lffflfffl", // 5
    54 => "lffffflfl", // 6
    55 => "fflflflff", // 7
    56 => "fflflfffl", // 8
    57 => "fflffflfl", // 9
    42 => "f        ", // asterisk sign '*' used for start & stop bar
  );
  return $arrSpec[$i];
}

function roymail4spec($i) {
/*
  Royal Mail 4-State specification for symbols (index represented by ascii codes).
  Some special signs, '*' & '+', are introduced to manage special control chars.
  f -> full bar
  t -> track only bar
  a -> track & ascender bar
  d -> track & descender bar
*/
  $arrSpec = array (
    48 => "tftffff", // 0
    49 => "tfdfaff", // 1
    50 => "tfdfffa", // 2
    51 => "dftfaff", // 3
    52 => "dftfffa", // 4
    53 => "dfdfafa", // 5
    54 => "tfafdff", // 6
    55 => "tffftff", // 7
    56 => "tfffdfa", // 8
    57 => "dfaftff", // 9
    65 => "dfafdfa", // A
    66 => "dffftfa", // B
    67 => "tfafffd", // C
    68 => "tfffafd", // D
    69 => "tffffft", // E
    70 => "dfafafd", // F
    71 => "dfaffft", // G
    72 => "dfffaft", // H
    73 => "aftfdff", // I
    74 => "afdftff", // J
    75 => "afdfdfa", // K
    76 => "fftftff", // L
    77 => "fftfdfa", // M
    78 => "ffdftfa", // N
    79 => "aftfffd", // O
    80 => "afdfafd", // P
    81 => "afdffft", // Q
    82 => "fftfafd", // R
    83 => "fftffft", // S
    84 => "ffdfaft", // T
    85 => "afafdfd", // U
    86 => "affftfd", // V
    87 => "afffdft", // W
    88 => "ffaftfd", // X
    89 => "ffafdft", // Y
    90 => "fffftft", // Z
    42 => "      a", // parenthesis sign '*' used for start char
    43 => "f      " // parenthesis sign '+' used for stop char
  );
  return $arrSpec[$i];
}

function roymail4value2char($row, $col) {
/*
  Royal Mail 4-State check digit calculation table.
*/
  $arrSpec = array (
    1 => "501234", // 0
    2 => "B6789A", // 1
    3 => "HCDEFG", // 2
    4 => "NIJKLM", // 3
    5 => "TOPQRS", // 4
    0 => "ZUVWXY" // 5
  );
  return $arrSpec[$row][$col];
}

function interleaveChars($c1, $c2) {
  $tmp1 = std25spec($c1);
  $tmp2 = std25spec($c2);
  $tmp = $tmp1 . $tmp2;
  for ($i=0; $i<strlen($tmp1); $i++) {
    $tmp[$i*2] = $tmp1[$i];
    $tmp[($i*2)+1] = $tmp2[$i];
  }
  return trim($tmp);
}

function safeStr($i, $auxStr){ // check whether a string has only valid chars or not
  switch ($i) {
    case BC_TYPE_CODE39:
    case BC_TYPE_CODE93:
      $charList = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%"; // asterisk (*) not included
      break;
    case BC_TYPE_INTER25:
    case BC_TYPE_STD25:
    case BC_TYPE_POSTNET:
      $charList = "0123456789";
      break;
    case BC_TYPE_ROYMAIL4:
      $charList = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      break;
    default:
      return 0;
      break;
  }
  for($j=0; $j<strlen($auxStr); $j++){
    if (strpos($charList, $auxStr[$j]) === FALSE){
      return FALSE;
    }
  }
  return TRUE;
}

function getSpec($type, $txt, $i) {
  switch ($type) {
    case BC_TYPE_CODE39:
      return code39spec(ord($txt[$i]));
      break;
    case BC_TYPE_STD25:
    case BC_TYPE_INTER25:
      return interleaveChars(ord($txt[$i*2]), ord($txt[$i*2+1]));
      break;
    case BC_TYPE_CODE93:
      return code93spec(ord($txt[$i]));
      break;
    case BC_TYPE_ROYMAIL4:
      return roymail4spec(ord($txt[$i]));
      break;
    case BC_TYPE_POSTNET:
      return postnetspec(ord($txt[$i]));
      break;
    default: // treat as code39
      return code39spec(ord($txt[$i]));
      break;
  }
}

function getCheckDigit($type, $txt) {
  $stLen = strlen($txt);
  switch ($type) {
    case BC_TYPE_CODE39: // modulo 43
      $ck = 0;
      for ($i=0; $i<$stLen; $i++){
        $ck = $ck + code393char2value(ord($txt[$i]));
      }
      return $txt . code393value2char($ck % 43);
      break;
    case BC_TYPE_CODE93: // C y K check digits. Modulo 47
      $ck = 0;
      for ($i=0; $i<$stLen; $i++){
        $ck = $ck + code393char2value(ord($txt[$stLen - $i -1])) * ($i % 20 + 1);
      }
      $txt1 = $txt . code393value2char($ck % 47);
      $stLen++;
      $ck = 0;
      for ($i=0; $i<$stLen; $i++){
        $ck = $ck + code393char2value(ord($txt1[$stLen - $i -1])) * ($i % 15 + 1);
      }
      return $txt1 . code393value2char($ck % 47);
      break;
    case BC_TYPE_STD25:
    case BC_TYPE_INTER25: // modulo 10
      $ck = 0;
      for ($i=0; $i<$stLen; $i++){
        $w = ($i%2==0)?3:1;
        $ck = $ck + $txt[$stLen -1 -$i] * $w;
      }
      $ex = (10 - ($ck % 10)) % 10;
      return $txt . $ex;
      break;
    case BC_TYPE_ROYMAIL4:
      $lh = 0;
      $uh = 0;
      for ($i=0; $i<$stLen; $i++){
        $chrSpec = strrev(getSpec($type, $txt, $i));
        $tlh = 0;
        $tuh = 0;
        for ($j=0; $j<4; $j++) {
          switch (ord($chrSpec[$j*2])) {
            case ord("f"):
              $um=1;
              $lm=1;
              break;
            case ord("t"):
              $um=0;
              $lm=0;
              break;
            case ord("a"):
              $um=1;
              $lm=0;
              break;
            case ord("d"):
              $um=0;
              $lm=1;
              break;
            default:
              $um=0;
              $lm=0;
              break;
          }
          if ($j > 0)
            $bw = pow(2, $j-1);
          else
            $bw = 0;
          $tlh = $tlh + $bw * $lm;
          $tuh = $tuh + $bw * $um;
        }
        if ($tlh == 6)
          $tlh = 0;
        if ($tuh == 6)
          $tuh = 0;
        $uh = $uh + $tuh;
        $lh = $lh + $tlh;
      }        
      return $txt . roymail4value2char($uh % 6, $lh % 6);
      break;
    case BC_TYPE_POSTNET:
      $ck = 0;
      for ($i=0; $i<$stLen; $i++){
        $ck = $ck + $txt[$i];
      }
      $ex = (10 - ($ck % 10)) % 10;
      return $txt . $ex;
      break;
    default:
      return $txt;
      break;
  }
}

// MAIN FUNCTION to call from parent program.
// Outputs a single barcode image including header directly to browser.
// Or specify file name to save the image.
function BarCode(
    $p_barcodeType, // Type of barcode to be generated
    $p_origText, // Text to be generated as barcode
    $p_xDim, // smallest element width
    $p_w2n, // wide to narrow factor
    $p_charGap, // Intercharacter gap width. usually the same as xDim
    $p_invert, // Whether or not invert starting bar colors 
    $p_charHeight, // height in pixels of a single character
    $p_imgType, // image type output
    $p_drawLabel, // Whether or not include a text label below barcode
    $p_rotationAngle, // Barcode Image rotation angle 
    $p_check, // Whether or not include check digit 
    $p_fileName // File name to use in case of writing to file
  ) {
  if ($p_rotationAngle < BC_ROTATE_0 || $p_rotationAngle > BC_ROTATE_270){
    $p_rotationAngle = BC_ROTATE_0;
  }
  $p_rotationAngle = $p_rotationAngle * 90;
  $font = 3; // font type. GD dependent
  $p_w2n = checkWideToNarrow($p_barcodeType, $p_w2n);
  $p_charGap = checkCharGap($p_barcodeType, $p_charGap);
  $p_check = checkCheckDigit($p_barcodeType, $p_check);
  $quietZone = 10 * $p_xDim; // safe white zone before and after the barcode
  if ($p_check) {
    $textCheck = getCheckDigit($p_barcodeType, $p_origText);
  } else {
    $textCheck = $p_origText;
  }
  $text2bar = getBarcodeText($p_barcodeType, $textCheck); // format text 
  $charCount = getCharCount($p_barcodeType, $text2bar); // number of symbols
  // image height & width
  $imgWidth = getBarcodeLength($p_barcodeType, $text2bar, $p_xDim, $p_w2n, $quietZone, $p_charGap);
  $imgHeight = $p_charHeight ;
  $hMidHeight = floor($p_charHeight / 2);
  $hTrackWidth = floor($p_charHeight / 4);
  if (($p_charHeight - $hTrackWidth) % 2 != 0){
    $hTrackWidth = $hTrackWidth + 1;
  }
  $hAscWidth = floor(($p_charHeight - $hTrackWidth) / 2);
  if ($p_drawLabel) { // increase image height when adding label
    $imgHeight = $imgHeight + imagefontheight($font);
  }
  $extraWidth = imagefontwidth($font) * strlen($p_origText) - $imgWidth;
  if ($extraWidth > 0) {
    $quietZone = $quietZone + $extraWidth / 2 + 1;
    $imgWidth = getBarcodeLength($p_barcodeType, $text2bar, $p_xDim, $p_w2n, $quietZone, $p_charGap);
  }
  $im = @imagecreate($imgWidth, $imgHeight) or die("Cannot Initialize new GD image stream");
  $xPos = $quietZone; // starting bar X position
  $bgColor = imagecolorallocate($im, 255, 255, 255); // white background
  $blackColor = imagecolorallocate($im, 0, 0, 0);
  $whiteColor = imagecolorallocate($im, 255, 255, 255);
  $black = !$p_invert; // what color is the first bar?
  for($j=0;$j<$charCount;$j++){ // traverse string
    $currChar = getSpec($p_barcodeType, $text2bar, $j); // get symbol spec.
    for ($i=0;$i<strlen($currChar);$i++) { // traverse symbol spec.
      if ($black){ // what color is next bar?
        $barColor = $blackColor;
      } else {
        $barColor = $whiteColor;
      }
      if ($currChar[$i] == "n"){ // draw a narrow bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = 0;
        $yPos1 = $p_charHeight - 1;
      } elseif ($currChar[$i] == "w") { // draw a wide bar
        $xPos1 = $xPos + $p_xDim * $p_w2n - 1;
        $yPos = 0;
        $yPos1 = $p_charHeight - 1;
      } elseif ($currChar[$i] == "1") { // draw a narrow black bar
        $xPos1 = $xPos + $p_xDim - 1;
        $barColor = $p_invert?$whiteColor:$blackColor;
        $yPos = 0;
        $yPos1 = $p_charHeight - 1;
      } elseif ($currChar[$i] == "0") { // draw a narrow white space
        $xPos1 = $xPos + $p_xDim - 1;
        $barColor = $p_invert?$blackColor:$whiteColor;
        $yPos = 0;
        $yPos1 = $p_charHeight - 1;
      } elseif ($currChar[$i] == "f") { // draw a full vertical bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = 0;
        $yPos1 = $p_charHeight - 1;
      } elseif ($currChar[$i] == "u") { // draw a mid upper vertical bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = 0;
        $yPos1 = $hMidHeight - 1;
      } elseif ($currChar[$i] == "l") { // draw a mid lower vertical bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = $hMidHeight;
        $yPos1 = $p_charHeight - 1;
      } elseif ($currChar[$i] == "t") { // draw a track only vertical bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = $hAscWidth;
        $yPos1 = $hAscWidth + $hTrackWidth - 1;
      } elseif ($currChar[$i] == "a") { // draw a track & ascender vertical bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = 0;
        $yPos1 = $hAscWidth + $hTrackWidth - 1;
      } elseif ($currChar[$i] == "d") { // draw a track & descender vertical bar
        $xPos1 = $xPos + $p_xDim - 1;
        $yPos = $hAscWidth;
        $yPos1 = $p_charHeight - 1;
      }
      if ($currChar[$i] != " ") {
        imagefilledrectangle($im, $xPos , $yPos, $xPos1, $yPos1, $barColor);
        $black = !$black;
        $xPos = $xPos1 + 1;
      }
    }
    // draw intercharacter gap if gap lenght > 0
    if ($j < $charCount - 1 && $p_charGap > 0) { // do not draw last gap
      if ($black){ // it is supposed to be always false but you never know
        $barColor = $blackColor;
      } else {
        $barColor = $whiteColor;
      }
      $xPos1 = $xPos + $p_charGap - 1;
      $yPos = 0;
      $yPos1 = $p_charHeight - 1;
      imagefilledrectangle($im, $xPos, $yPos, $xPos1, $yPos1, $barColor);
      $black = !$black;
      $xPos = $xPos1 + 1;
    }
  }
  if ($p_drawLabel) { // draw text label
    $imgTextWidth = imagefontwidth($font) * strlen($p_origText);
    $xText = ($imgWidth - $imgTextWidth) / 2;
    imagestring($im, $font, $xText, $p_charHeight, $p_origText, $blackColor );
  }
  $functionName = barcodeImgFunction($p_imgType); // get php image output function
  if ($p_fileName){
    $fileExt = barcodeFileExt($p_imgType); // get file extension
    $functionName(imagerotate($im, $p_rotationAngle, $whiteColor), $p_fileName . "." . $fileExt); // Automatic image type output
  } else {
    $headerContent = barcodeHeaderContent($p_imgType); // get header type
    header("Content-type: $headerContent"); // Automatic content type output
    $functionName(imagerotate($im, $p_rotationAngle, $whiteColor)); // Automatic image type output
  }
  imagedestroy($im); // free image resource
}

?>
