<?php

const DIR_PATH = "/media/sven/DATA/series_pas_bue/";
const SPACE_CHAR = " ";
const DRY_RUN = FALSE;

/**
 *
 * Rename files from a directory
 *
 *
 */

echo "<pre>\n";

foreach(scandir(DIR_PATH) as $file) {
	
	$cleanFile = clean_file_name($file);

	if(!is_dir(DIR_PATH.$file) and $cleanFile !== $file) {

		echo "<span style='color:red;'>$file</span>\n<span style='color:blue;'>$cleanFile</span><br/>\n";
		
		if(DRY_RUN === FALSE) {
			if(!is_file(DIR_PATH.$cleanFile)) {
				rename(DIR_PATH.$file, DIR_PATH.$cleanFile);
			} else {
				echo "<span style='color:red;'>!!! WARNING !!! possible overridding filename</span>\n";
			}
		}
	}
}

echo "</pre>\n";
echo "chuck !\n";


function clean_file_name($filename) {

	// remove extension
	$chunk = multi_explode(array('.', ' ', '_', '-'), $filename);
	$ext = array_pop($chunk);

	foreach($chunk as $k => $piece) {
		$chunk[$k] = ucfirst($piece);
	}

	// remove . 
	$filename = implode(SPACE_CHAR, $chunk);

	// remove []
	$filename = preg_replace("/(\[.*\])(.*)/", "$2", $filename);

	// ren everything after pattern s??e?? exept tag
	$filename = preg_replace("/(.*)S([0-9][0-9]) ?E([0-9][0-9])(.*)/i", "$1S$2E$3", $filename);
	// ren everything after pattern - ??? - exept tag
	$filename = preg_replace("/(.*) ([0-9])([0-9][0-9]) (.*)/i", "$1 S0$2E$3", $filename);
	// ren everything after pattern ??x?? exept tag
	$filename = preg_replace("/(.*)([0-9][0-9])x([0-9][0-9])(.*)/i", "$1S$2E$3", $filename);
	
	// replace double space by space
	while(strpos($filename, SPACE_CHAR.SPACE_CHAR) !== FALSE) {
		$filename = str_replace(SPACE_CHAR.SPACE_CHAR, SPACE_CHAR, $filename);
	}

	return trim($filename).'.'.$ext;
}

function multi_explode($delimiters, $string) {

	$ready = str_replace($delimiters, $delimiters[0], $string);
	return explode($delimiters[0], $ready);
}
