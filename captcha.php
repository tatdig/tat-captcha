<?php

session_start();

 /**
  * Like str_word_count() but showing how preg can do the same.
  * This function is most flexible but not faster than str_word_count.
  * @param $wRgx the "word regular expression" as defined by user.
  * @param $triggError changes behaviour causing error event.
  * @param $OnBadUtfTryAgain when true mimic the str_word_count behaviour.
  * @return 0 or positive integer as word-count, negative as PCRE error.
  * $type - what to return: 0 - count, 1 - array.
  */
 function preg_word_count($s,$wRgx='/[-\'\p{L}\xC2\xAD]+/u', $triggError=true,
                          $OnBadUtfTryAgain=true, $type=1) {
   if ( preg_match_all($wRgx,$s,$m) !== false ){
        switch($type){
            case 0: return count($m[0]);
            break;
            case 1: return $m[0];
            break;
        }
   }else {
      $lastError = preg_last_error();
      $chkUtf8 = ($lastError==PREG_BAD_UTF8_ERROR);
      if ($OnBadUtfTryAgain && $chkUtf8)
         return preg_word_count(
            iconv('CP1252','UTF-8',$s), $wRgx, $triggError, false
         );
      elseif ($triggError) trigger_error(
         $chkUtf8? 'non-UTF8 input!': "error PCRE_code-$lastError",
         E_USER_NOTICE
         );
      return -$lastError;
   }
 }

function get_string(){
    $txt = file_get_contents('input.txt');
    $words=preg_word_count($txt);
    $res = array_filter(
    $words,
    function ($value) {
        return (mb_strlen($value) < 9 && mb_strlen($value) > 3);
    }
);

foreach($res as $wrd) { $result[]=$wrd; }
    return $result[mt_rand(0,count($result)-1)];
}


$image = imagecreatetruecolor(200, 50);

imageantialias($image, true);

$colors = [];

$red = rand(125, 175);
$green = rand(125, 175);
$blue = rand(125, 175);

for($i = 0; $i < 5; $i++) {
  $colors[] = imagecolorallocate($image, $red - 20*$i, $green - 20*$i, $blue - 20*$i);
}

imagefill($image, 0, 0, $colors[0]);

for($i = 0; $i < 10; $i++) {
  imagesetthickness($image, rand(2, 10));
  $line_color = $colors[rand(1, 4)];
  imagerectangle($image, rand(-10, 190), rand(-10, 10), rand(-10, 190), rand(40, 60), $line_color);
}

$black = imagecolorallocate($image, 0, 0, 0);
$white = imagecolorallocate($image, 255, 255, 255);
$textcolors = [$black, $white];
//arialtat.ttf  SLCONCBI.TTF  SLCONCI_.TTF  SLPECBI_.TTF  SLPECI__.TTF
$fonts = [dirname(__FILE__).'/fonts/tverdana.ttf', dirname(__FILE__).'/fonts/sl_pet.ttf', dirname(__FILE__).'/fonts/sl_arial.ttf'];
/*  
dirname(__FILE__).'/fonts/t_futuri.ttf', dirname(__FILE__).'/fonts/SLCONCI_.TTF',
    dirname(__FILE__).'/fonts/SLPECBI_.TTF']; 
*/

$string_length = 8;
$captcha_string = mb_convert_case(get_string(),MB_CASE_LOWER);

$_SESSION['captcha_text'] = $captcha_string;

for($i = 0; $i < $string_length; $i++) {
  $letter_space = 170/$string_length;
  $initial = 15;
  imagettftext($image, 24, rand(-15, 15), $initial + $i*$letter_space, rand(25, 45), $textcolors[rand(0, 1)], $fonts[array_rand($fonts)], mb_substr($captcha_string,$i,1));
}

header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>
