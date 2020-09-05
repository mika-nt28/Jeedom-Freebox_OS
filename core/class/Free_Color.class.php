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

	public static function convertRGBToXY($RGB)
	{
		// Get decimal RGB
		$r = hexdec(substr($RGB, 1, 2));
		$g = hexdec(substr($RGB, 3, 2));
		$b = hexdec(substr($RGB, 5, 2));

		$normalizedToOne['red'] = $r / 255;
		$normalizedToOne['green'] = $g / 255;
		$normalizedToOne['blue'] = $b / 255;
		foreach ($normalizedToOne as $key => $normalized) {
			if ($normalized > 0.04045) {
				$color[$key] = pow(($normalized + 0.055) / (1.0 + 0.055), 2.4);
			} else {
				$color[$key] = $normalized / 12.92;
			}
		}
		$xyz['x'] = $color['red'] * 0.664511 + $color['green'] * 0.154324 + $color['blue'] * 0.162028;
		$xyz['y'] = $color['red'] * 0.283881 + $color['green'] * 0.668433 + $color['blue'] * 0.047685;
		$xyz['z'] = $color['red'] * 0.000000 + $color['green'] * 0.072310 + $color['blue'] * 0.986039;
		if (array_sum($xyz) == 0) {
			$x = 0;
			$y = 0;
		} else {
			$x = $xyz['x'] / array_sum($xyz);
			$y = $xyz['y'] / array_sum($xyz);
		}
		$bri = round($xyz['y'] * 255);
		log::add('Freebox_OS', 'debug', '│──────────> Value x ' . $x);
		log::add('Freebox_OS', 'debug', '│──────────> Value y ' . $y);
		log::add('Freebox_OS', 'debug', '│──────────> bri ' . $bri);
		$parametre['x'] = $x;
		$parametre['y'] = $y;
		$parametre['bri'] = $bri;
		return $parametre;
		//return array('x' => $x, 'y' => $y, 'bri' => round($xyz['y'] * 255));
	}

	public static function convertxyToRGB($x, $y, $bri = 255)
	{
		$z = 1.0 - $x - $y;
		$xyz['y'] = $bri / 255;
		$xyz['x'] = ($xyz['y'] / $y) * $x;
		$xyz['z'] = ($xyz['y'] / $y) * $z;
		$color['red'] = $xyz['x'] * 1.656492 - $xyz['y'] * 0.354851 - $xyz['z'] * 0.255038;
		$color['green'] = -$xyz['x'] * 0.707196 + $xyz['y'] * 1.655397 + $xyz['z'] * 0.036152;
		$color['blue'] = $xyz['x'] * 0.051713 - $xyz['y'] * 0.121364 + $xyz['z'] * 1.011530;
		$maxValue = 0;
		foreach ($color as $key => $normalized) {
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
			$color[$key] = round($color[$key] * 255);
		}
		return $color;
	}
}
