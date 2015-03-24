#!/usr/bin/php
<?php

// FIX glitch dans les coin extérieurs des boxes à droite en fin de colonnes.

/**
 *
 *  This script will morph this kind of boxes (only if the box is a comment) :
 *
 *  +-------------+     +-------------+
 *  |             +----->   ANOTHER   |
 *  |             |     +------+------+
 *  |     BOX     |            |
 *  |             <------------+
 *  |             |
 *  +-------------+
 *
 * into this :
 *
 *  ┌─────────────┐     ┌─────────────┐
 *  │             ├─────>   ANOTHER   │
 *  │             │     └──────┬──────┘
 *  │     BOX     │            │
 *  │             <────────────┘
 *  │             │
 *  └─────────────┘
 *
 * Usage: php diagram_polish.php [<filename>[<otherfile>]*]
 *
 */

array_shift($argv);

while(count($argv) > 0) {
	$filename = array_shift($argv);
	polish($filename);
}

function polish($file) {

	if(!file_exists($file)) {return;}

	$blanks = str_split(' *abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

	// Load
	$data = explode(PHP_EOL, file_get_contents($file));
	foreach($data as $i => $v) {
		$data[$i] = str_split($v);
	}

	// Modify
	foreach($data as $y => &$line) {

		if(
			(count($line) < 2) or
			$line[0] !== ' ' or
			$line[1] !== '*'
		) {
			// Skip every line not starting with ' *'
			continue;
		}

		foreach($line as $x => &$char) {

			// Apply rules
			if($char === '+') {

				if(
					(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche vide
					!(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite plein
					!(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous plein
					(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus vide
				) {
					$char = '┌';
				}

				else if(
					!(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche plein
					(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite vide
					!(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous plein
					(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus vide
				) {
					$char = '┐';
				}

				else if(
					(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche vide
					!(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite plein
					(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous vide
					!(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus plein
				) {
					$char = '└';
				}

				else if(
					!(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche plein
					(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite vide
					(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous vide
					!(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus plein
				) {
					$char = '┘';
				}

				else if(
					(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche vide
					!(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite plein
					!(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous plein
					!(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus plein
				) {
					$char = '├';
				}

				else if(
					!(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche plein
					(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite vide
					!(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous plein
					!(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus plein
				) {
					$char = '┤';
				}

				else if(
					!(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche plein
					!(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite plein
					(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous vide
					!(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus plein
				) {
					$char = '┴';
				}

				else if(
					!(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche plein
					!(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite plein
					!(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous plein
					(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus vide
				) {
					$char = '┬';
				}

				else if(
					!(!isset($data[$y][$x-1]) or in_array($data[$y][$x-1], $blanks)) and // si gauche plein
					!(!isset($data[$y][$x+1]) or in_array($data[$y][$x+1], $blanks)) and// si droite plein
					!(!isset($data[$y+1][$x]) or in_array($data[$y+1][$x], $blanks)) and // si dessous plein
					!(!isset($data[$y-1][$x]) or in_array($data[$y-1][$x], $blanks)) // si dessus plein
				) {
					$char = '┼';
				}

			}

			if($char === '-') $char = '─';

			if($char === '|') $char = '│';

			if($char === '+') $char = '┼';

		}
	}

	// Save
	foreach($data as $i => $v) {
		$data[$i] = implode('', $v);
	}
	file_put_contents($file, implode(PHP_EOL, $data));

}


