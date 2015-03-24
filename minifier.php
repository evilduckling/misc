#!/usr/bin/php
<?php
/**
 * MINIFY Js and Css ressources.
 *
 * Create a file in the dir /minified/ in the <path> specified, containg all subpath ressources minified.
 */
$usage = <<<USAGE
Usage: php minifier.php -p <path> -c <conffile> [-h]
Options:
	-c configuration file
	-e extension
	-p root path of ressources
	-v use svn versioning system
	-h display this message

USAGE;

$options = getopt('e:c:p:hv');

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

if(isset($options['e'])) {
	$extension = $options['e'];
} else {
	print_r('Missing extension parameter'.PHP_EOL);
	exit(0);
}

if(isset($options['c'])) {
	$conffile = $options['c'];
} else {
	$conffile = NULL;
}

if(!isset($options['v'])) {
	$svn = FALSE;
} else {
	$svn = TRUE;
}

$minifiedSubPath = '/minified/';
$ressourceSize = 0;
$bufferedSize = 0;

// Clean already minified files in /minified/
if($svn) {
	$cmd = 'svn remove ';
} else {
	$cmd = 'rm ';
}
$cmd .= $mediaPath.$minifiedSubPath.'*.'.$extension.' 2>/dev/null';

@exec($cmd);

// Warning we search only in 3 recursive directory depth
$ressource_files = array();

$ressource_files = array_merge(glob($mediaPath.'/*.min.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/*/*.min.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/*/*/*.min.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/lib/*.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/lib/*/*.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/*.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/*/*.'.$extension), $ressource_files);
$ressource_files = array_merge(glob($mediaPath.'/*/*/*.'.$extension), $ressource_files);

$ressource_files = array_unique($ressource_files);

// Remove unminified files form process list if we already have a minified copy !
foreach ($ressource_files as $key => $file) {

	$extensionSize = strlen($extension) + 1;
	$needle = substr($file, 0, -$extensionSize).'.min.'.$extension;
	if(in_array($needle, $ressource_files)) {
		// Get rid of this file.
		unset($ressource_files[$key]);
		continue;
	}

	if(strpos($file, '/minified/') !== FALSE) {
		unset($ressource_files[$key]);
		continue;
	}

	if(strpos($file, 'admin') !== FALSE) {
		unset($ressource_files[$key]);
		continue;
	}
}

// Build minified file
$buffer = '';
// Minify (if needed) and add files
foreach($ressource_files as $ressource_file) {
	if(strpos($ressource_file, '/minified/') === FALSE) {
		echo ">>> Add $ressource_file to buffer".PHP_EOL;
		$fileContent = file_get_contents($ressource_file);
		$ressourceSize += strlen($fileContent);
		if(strpos($ressource_file, '.min.') !== FALSE) {
			$buffer .= $fileContent.PHP_EOL;
		} else {
			$buffer .= minify($fileContent, $extension).PHP_EOL;
		}
	}
}

// Write minified file.
$md5 = strtolower(md5($buffer));
@mkdir($mediaPath.$minifiedSubPath);
$output_ressource_filename = $mediaPath.$minifiedSubPath.$md5.'.min.'.$extension;
file_put_contents($output_ressource_filename, $buffer);

if($svn) {
	exec('svn add '.$mediaPath.$minifiedSubPath.' 2>/dev/null');
	exec('svn add '.$output_ressource_filename.' 2>/dev/null');
}

// Read conf file if specified and change the name of the minified file
if($conffile !== NULL) {
	$conf_file_content = file_get_contents($conffile);
	$filesLines = explode("\n", $conf_file_content);
	foreach($filesLines as $index => $confLine) {
		if(strpos($confLine, 'MINIFY_'.strtoupper($extension) )) {
			$filesLines[$index] = "\tconst MINIFY_".strtoupper($extension)." = '".$md5.".min';";
		}
	}
	$conf_file_content = implode("\n", $filesLines);
	file_put_contents($conffile, $conf_file_content);
}

// Display stats
$bufferedSize = strlen($buffer);
$winPercent = round(1000 * ($ressourceSize - $bufferedSize) / $ressourceSize) / 10;

$ressourceSize = (round(10 * $ressourceSize / 1024) / 10);
$bufferedSize = (round(10 * $bufferedSize / 1024) / 10);
echo "Sources: $ressourceSize Kb Vs $bufferedSize Kb minified ($winPercent%)".PHP_EOL;

/**
 * Do minify a file through web service
 */
function minify($raw, $extension) {

	if($extension === 'js') {
		$url = 'http://javascript-minifier.com/raw';
	} else {
		$url = 'http://cssminifier.com/raw';
	}

	$postdata = http_build_query(array('input' => $raw));

	$opts = array('http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		)
	);

	$context  = stream_context_create($opts);
	$result = file_get_contents($url, false, $context);

	return $result;

}

