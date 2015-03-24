#!/usr/bin/php
<?php
/**
 * Rename every occurence of the specified word by another in the specified file including filename.
 */

// nice to have: option -q quiet

$usage = <<<USAGE
Usage: php replace.php [-m] [-d] [-h] [-v] <word-to-replace> <word-to-replace> [<filename>[<otherfile>]*]
Options:
	-d dryrun
	-m enable magic casing (WARNING feature experimentale USE AT YOUR OWN RISKS)
	-v use svn versioning system
	-h display this message

USAGE;

$options = getopt('dhmv');
cleanArgvFromOptions($options, $argv);
array_shift($argv);

if(isset($options['h'])) {
	print_r($usage);
	exit(0);
}

if(!isset($options['m'])) {
	$magicCasing = FALSE;
} else {
	$magicCasing = TRUE;
}

if(!isset($options['d'])) {
	$dryrun = FALSE;
} else {
	$dryrun = TRUE;
}

if(!isset($options['v'])) {
	$svn = FALSE;
} else {
	$svn = TRUE;
}

if(count($argv) === 0) {
	echo 'word to replace MUST be specified'.PHP_EOL;
	exit(1);
} else {
	$oldValue = array_shift($argv);
}

if(count($argv) === 0) {
	echo 'replacing word MUST be specified'.PHP_EOL;
	exit(1);
} else {
	$newValue = array_shift($argv);
}

$total_count = 0;

if(count($argv) === 0) {
	while($filename = fgets(STDIN)){
		doReplace($filename, $oldValue, $newValue, $magicCasing, $dryrun, $svn, $total_count);
	}

} else {
	while(count($argv) > 0) {
		$filename = array_shift($argv);
		doReplace($filename, $oldValue, $newValue, $magicCasing, $dryrun, $svn, $total_count);
	}
}

echo $total_count.' replacements done.'.PHP_EOL;

function doReplace($filename, $oldValue, $newValue, $magicCasing, $dryrun, $svn, &$total_count) {

	$filename = trim($filename);

	if(empty($filename) or strpos($filename, '.svn') !== FALSE or strpos($filename, '.git') !== FALSE) {
		return;
	}

	if(!file_exists($filename)) {
		echo 'File: '.$filename.' does not exist'.PHP_EOL;
		return;
	}

	// TODO enhancement
	// keep track of dir list and theat them at the end.
	if(is_dir($filename)) {
		return;
	}

	$needles = array();
	$values = array();

	if($magicCasing) {
		// Asked
		$needles[] = $oldValue;
		$values[] = $newValue;
		// Low case
		$needles[] = lcfirst($oldValue);
		$values[] = lcfirst($newValue);
		$needles[] = strtolower($oldValue);
		$values[] = strtolower($newValue);
		// Up case
		$needles[] = ucfirst($oldValue);
		$values[] = ucfirst($newValue);
		$needles[] = strtoupper($oldValue);
		$values[] = strtoupper($newValue);
	} else {
		$needles[] = $oldValue;
		$values[] = $newValue;
	}

	// TODO enhancement se débrouiller pour dédoublonner

	foreach($needles as $key => $needle) {
		if($needle === $values[$key]) {
			unset($values[$key]);
			unset($needles[$key]);
		}
	}

	if($dryrun) {
		foreach($needles as $key => $needle) {
			echo "Will replace $needle by ".$values[$key].PHP_EOL;
		}
	} else {
		// Replace content
		$fileContent = file_get_contents($filename);
		$fileContent = str_replace($needles, $values, $fileContent, $count);
		file_put_contents($filename, $fileContent);
		$total_count += $count;
	}

	// Replace in file name (not directories)
	$origineFile = basename($filename);
	$dir = dirname($filename);
	$newFileName = str_replace($needles, $values, $origineFile, $count);
	$total_count += $count;

	if($newFileName !== $origineFile) {

		if($svn) {
			$cmd = 'svn move ';
		} else {
			$cmd = 'mv ';
		}

		$cmd .= $filename.' '.$dir.'/'.$newFileName;

		if($dryrun) {
			echo $cmd.PHP_EOL;
		} else {
			exec($cmd);
		}

	}
}

function cleanArgvFromOptions($options, &$argv) {
	$pruneargv = array();
	foreach ($options as $option => $value) {
		foreach ($argv as $key => $chunk) {
			$regex = '/^'. (count($option) > 1 ? '--' : '-') . $option . '/';
			if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
				array_push($pruneargv, $key);
			}
		}
	}
	while ($key = array_pop($pruneargv)) unset($argv[$key]);
}

?>
