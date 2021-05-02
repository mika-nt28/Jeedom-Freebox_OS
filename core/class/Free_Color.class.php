<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class Free_Color
{

	/**
	 * Converts RGB values to XY values
	 * Based on: http://stackoverflow.com/a/22649803
	 *
	 * @param int $red   Red value
	 * @param int $green Green value
	 * @param int $blue  Blue value
	 *
	 * @return array x, y, bri key/value
	 */
	public static function HTMLtoXY($color)
	{

		$color = str_replace('0x', '', $color);
		$color = str_replace('#', '', $color);
		$red = hexdec(substr($color, 0, 2));
		$green = hexdec(substr($color, 2, 2));
		$blue = hexdec(substr($color, 4, 2));

		// Normalize the values to 1
		$normalizedToOne['red'] = $red / 255;
		$normalizedToOne['green'] = $green / 255;
		$normalizedToOne['blue'] = $blue / 255;

		// Make colors more vivid
		foreach ($normalizedToOne as $key => $normalized) {
			if ($normalized > 0.04045) {
				$color[$key] = pow(($normalized + 0.055) / (1.0 + 0.055), 2.4);
			} else {
				$color[$key] = $normalized / 12.92;
			}
		}

		// Convert to XYZ using the Wide RGB D65 formula
		$xyz['x'] = $color['red'] * 0.664511 + $color['green'] * 0.154324 + $color['blue'] * 0.162028;
		$xyz['y'] = $color['red'] * 0.283881 + $color['green'] * 0.668433 + $color['blue'] * 0.047685;
		$xyz['z'] = $color['red'] * 0.000000 + $color['green'] * 0.072310 + $color['blue'] * 0.986039;

		// Calculate the x/y values
		if (array_sum($xyz) == 0) {
			$x = 0;
			$y = 0;
		} else {
			$x = $xyz['x'] / array_sum($xyz);
			$y = $xyz['y'] / array_sum($xyz);
		}

		return array(
			'x' => $x,
			'y' => $y,
			'bri' => round($xyz['y'] * 255),
		);
	}
	/**
	 * Converts XY (and brightness) values to RGB
	 *
	 * @param float $x X value
	 * @param float $y Y value
	 * @param int $bri Brightness value
	 *
	 * @return array red, green, blue key/value
	 */
	public static function XYtoHTML($x, $y, $bri = 255)
	{
		// Calculate XYZ
		$z = 1.0 - $x - $y;
		$xyz['y'] = $bri / 255;
		$xyz['x'] = ($xyz['y'] / $y) * $x;
		$xyz['z'] = ($xyz['y'] / $y) * $z;
		// Convert to RGB using Wide RGB D65 conversion
		$color['r'] = $xyz['x'] * 1.656492 - $xyz['y'] * 0.354851 - $xyz['z'] * 0.255038;
		$color['g'] = -$xyz['x'] * 0.707196 + $xyz['y'] * 1.655397 + $xyz['z'] * 0.036152;
		$color['b'] = $xyz['x'] * 0.051713 - $xyz['y'] * 0.121364 + $xyz['z'] * 1.011530;
		$maxValue = 0;
		foreach ($color as $key => $normalized) {
			// Apply reverse gamma correction
			if ($normalized <= 0.0031308) {
				$color[$key] = 12.92 * $normalized;
			} else {
				$color[$key] = (1.0 + 0.055) * pow($normalized, 1.0 / 2.4) - 0.055;
			}
			$color[$key] = max(0, $color[$key]);
			if ($maxValue < $color[$key]) {
				$maxValue = $color[$key];
			}
		}
		foreach ($color as $key => $normalized) {
			if ($maxValue > 1) {
				$color[$key] /= $maxValue;
			}
			// Scale back from a maximum of 1 to a maximum of 255
			$color[$key] = round($color[$key] * 255);
		}
		return sprintf("#%02X%02X%02X", $color['r'], $color['g'], $color['b']);
	}
	public static function RGBtoHTML($r, $g = -1, $b = -1)
	{
		if (is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;

		$r = intval($r);
		$g = intval($g);
		$b = intval($b);

		$r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
		$g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
		$b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

		$color = (strlen($r) < 2 ? '0' : '') . $r;
		$color .= (strlen($g) < 2 ? '0' : '') . $g;
		$color .= (strlen($b) < 2 ? '0' : '') . $b;
		return '#' . $color;
	}
	public static function HEXtoDEC($s)
	{
		$s = str_replace("#", "", $s);
		$output = 0;
		for ($i = 0; $i < strlen($s); $i++) {
			$c = $s[$i]; // you don't need substr to get 1 symbol from string
			if (($c >= '0') && ($c <= '9'))
				$output = $output * 16 + ord($c) - ord('0'); // two things: 1. multiple by 16 2. convert digit character to integer
			elseif (($c >= 'A') && ($c <= 'F')) // care about upper case
				$output = $output * 16 + ord($s[$i]) - ord('A') + 10; // note that we're adding 10
			elseif (($c >= 'a') && ($c <= 'f')) // care about lower case
				$output = $output * 16 + ord($c) - ord('a') + 10;
		}

		return $output;
	}
	public static function DECtoHEX($d)
	{
		return ("#" . substr("000000" . dechex($d), -6));
	}
}
