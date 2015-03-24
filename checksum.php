#!/usr/bin/php
<?php
/**
 * Generate a MEDIA_VERSION hash.for a path of files.
 */
$usage = <<<USAGE
Usage: php checksum.php -p <mediapath> -c <conffile> [-h]
Options:
	-c configuration file
	-p root path of media ressources
	-h display this message

USAGE;

$options = getopt('c:p:h');

if(isset($options['h'])) {
	print_r($usage);
	exit(0);
}

if(isset($options['p'])) {
	$mediaPath = realpath($options['p']);
} else {
	print_r('Missing path parameter'.PHP_EOL);
	exit(0);
}

if(isset($options['c'])) {
	$conffile = $options['c'];
} else {
	print_r('Missing confFile parameter'.PHP_EOL);
	exit(0);
}

$hash = hashDir($mediaPath);

// Write conf file
$conf_file_content = file_get_contents($conffile);
$filesLines = explode("\n", $conf_file_content);
foreach($filesLines as $index => $confLine) {
	if(strpos($confLine, 'MEDIA_CHECKSUM')) {
		$filesLines[$index] = "\tconst MEDIA_CHECKSUM = '".$hash."';";
	}
}
$conf_file_content = implode("\n", $filesLines);
file_put_contents($conffile, $conf_file_content);

// Recursively
function hashDir($dirname) {

	$hash = '';

	$dh  = opendir($dirname);
	while (FALSE !== ($filename = readdir($dh))) {
		if($filename !== '.' and $filename !== '..') {
			$files[] = $dirname.'/'.$filename;
		}
	}
	closedir($dh);

	foreach($files as $file) {
		if(is_dir($file)) {
			$hash .= hashDir($file);
		} else {
			$hash .= md5_file($file);
		}
	}

	return md5($hash);
}

exit(0);