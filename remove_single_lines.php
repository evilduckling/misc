#!/usr/bin/php
<?php

/**
 *
 * This script remove all the lines in which the checksum is unique in the file.
 * /!\ This script expect the input files to be sorted.
 *
 * Usage: php remove_single_lines.php [<filename>[<otherfile>]*]
 *
 */

array_shift($argv);

while(count($argv) > 0) {
	$filename = array_shift($argv);
	treat($filename);
}

function treat($file) {

	if(!file_exists($file)) {return;}

	// Load
	$data = explode(PHP_EOL, file_get_contents($file));

	// Modify
	$previousMd5 = "a";
	foreach($data as $y => $line) {
		$md5 = substr($line, 0, 32);

		if (isset($data[$y + 1])) {
			$nextMd5 = substr($data[$y + 1], 0, 32);
		} else {
			$nextMd5 = "z";
		}

		if ($md5 != $nextMd5 && $md5 != $previousMd5) {
			unset($data[$y]);
		} else {
			$previousMd5 = $md5;
		}
	}

	// Save
	file_put_contents($file, implode(PHP_EOL, $data));

}

