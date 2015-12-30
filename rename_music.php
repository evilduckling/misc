<?php

const DIR_PATH = '/Volumes/SAMSUNG/music/';
const SPACE_CHAR = ' ';
const HASH = 'RTYFS45DHGF95VBDVS';
const DRY_RUN = TRUE;


/**
 *
 * Rename directories from music folder.
 *
 */

$doublePass = false;

foreach(scandir(DIR_PATH) as $dir) {

	if($dir !== '.' and $dir !== '..') {

		$cleanDir = clean_dir_name($dir);

		if(is_dir(DIR_PATH.$dir) and $cleanDir !== $dir) {

			echo "$dir -> $cleanDir\n";

			if(DRY_RUN === FALSE) {
				if(!is_file(DIR_PATH.$cleanDir)) {

					$from = DIR_PATH.$dir;
					$to = DIR_PATH.$cleanDir;

					// MacOs bug
					if(strtolower($from) === strtolower($to)) {
						$doublePass = true;
						$to .= ' '.HASH; // generate double pass
					}

					rename($from, $to);

				} else {
					echo "!!! WARNING !!! possible overridding filename $cleanDir already exist\n";
				}
			}

		}
	}

}

if($doublePass) {
	echo "!!! WARNING !!! This script needs to be re-runned\n";
}

function clean_dir_name($dirname) {

	$chunk = multi_explode(array(' ', '_', ')', '(', '-khet', 'khet', '[', ']', 'CD', HASH), $dirname);

	foreach($chunk as $k => $piece) {
		$chunk[$k] = ucfirst($piece);
		if(strtolower($piece) === 'e.p') $chunk[$k] = '[EP]';
		if(strtolower($piece) === 'ep') $chunk[$k] = '[EP]';
		if(strtolower($piece) === 'lp') $chunk[$k] = '[LP]';
		if(strtolower($piece) === 'uk') $chunk[$k] = '[UK]';
	}

	$dirname = implode(SPACE_CHAR, $chunk);

	// replace double space by space
	while(strpos($dirname, SPACE_CHAR.SPACE_CHAR) !== FALSE) {
		$dirname = str_replace(SPACE_CHAR.SPACE_CHAR, SPACE_CHAR, $dirname);
	}

	return trim($dirname);
}

function multi_explode($delimiters, $string) {
	$ready = str_replace($delimiters, $delimiters[0], $string);
	return explode($delimiters[0], $ready);
}
