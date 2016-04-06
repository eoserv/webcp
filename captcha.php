<?php

require 'config.php';
require 'class/Session.class.php';

header('Cache-control: private, no-cache');

$sess = new Session($cpid.'_EOSERVCP');

function hsl($hsl) { 
	$h = $hsl[0]/360;
	$s = $hsl[1]/100;
	$l = $hsl[2]/100;
	if ($s == 0.0) { $r = $g = $b = $l; }
	else {
		if ($l<=0.5) { $m2 = $l*($s+1); }
		else { $m2 = $l+$s-($l*$s); }
		$m1 = $l*2 - $m2;
		$r = hue($m1, $m2, ($h+1/3));
		$g = hue($m1, $m2, $h);
		$b = hue($m1, $m2, ($h-1/3));
	}
	return array(round($r*255), round($g*255), round($b*255));
}

function hue($m1, $m2, $h) {
	if ($h<0) { $h = $h+1; }
	if ($h>1) { $h = $h-1; }
	if ($h*6<1) { return $m1+($m2-$m1)*$h*6; }
	if ($h*2<1) { return $m2; }
	if ($h*3<2) { return $m1+($m2-$m1)*(2/3-$h)*6; }
	return $m1;
}

$dictionary = array('B', 'C', 'D', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'X', '2', '3', '4', '5', '6', '7', '8', '9');
$length = 6 + rand(-1, 1);
$word = '';
$fonts = $captcha_fonts;

$last = ' ';

for ($i = 0; $i < $length; ++$i)
{
	do
	{
		$letter = $dictionary[rand(0, count($dictionary) - 1)];
	} while ($letter == $last);
	
	$word .= $letter;
	
	$last = $letter;
}

$sess->captcha = $word;

$img = imagecreatetruecolor(120, 40);

$background_hsl = array(rand(0, 359), rand(15, 30), rand(60, 75));

$background = call_user_func_array('imagecolorallocate', array_merge(array($img), hsl($background_hsl)));

imagefilledrectangle($img, 0, 0, 120, 40, $background);

for ($i = 0; $i < 6; ++$i)
{
	$method = rand(0, 2);
	$x = rand(-50, 200);
	$y = rand(-30, 60);
	$r = rand(40, 120);

	switch ($method)
	{
		case 0:
			$hsl = array($background_hsl[0], $background_hsl[1] + 10, $background_hsl[2] + 5);
			break;

		case 1:
			$hsl = array($background_hsl[0] + rand(-19, 19), $background_hsl[1], $background_hsl[2] - 3);
			break;

		case 2:
			$hsl = array($background_hsl[0] + rand(-19, 19), $background_hsl[1], $background_hsl[2] + 5);
			break;
	}

	$color = call_user_func_array('imagecolorallocate', array_merge(array($img), hsl($hsl)));
	
	imagefilledellipse($img, $x, $y, $r, $r, $color);
}

foreach (str_split($word) as $i => $char)
{
	$method = rand(0, 2);
	$tilt = rand(-15, 15);
	$size = rand(12, 16);
	$offset_x = rand(-1, 1);
	$offset_y = rand(-1, 5);

	if (count($fonts) > 0)
		$font = $fonts[rand(0, count($fonts) - 1)];
	else
		$font = null;
	
	switch ($method)
	{
		case 0:
			$hsl = array($background_hsl[0], $background_hsl[1] + 40, $background_hsl[2] + 30);
			break;

		case 1:
			$hsl = array($background_hsl[0] + rand(90, 270), $background_hsl[1] - 15, $background_hsl[2] - 25);
			break;

		case 2:
			$hsl = array($background_hsl[0] + rand(90, 270), $background_hsl[1] - 25, $background_hsl[2] + 35);
			break;
	}

	$color = call_user_func_array('imagecolorallocate', array_merge(array($img), hsl($hsl)));

	if (isset($font) && is_file($font))
		imagettftext($img, $size, $tilt, 10 + $i * (10 + (7 - min($length, 6)) * 5) + $offset_x, 27 + $offset_y, $color, $font, $char);
	else
		imagestring($img, rand(4,5), 10 + $i * (10 + (7 - min($length, 6)) * 5) + $offset_x, 8 + $offset_y, $char, $color);
}

header('Content-type: image/png');
imagepng($img, null);
