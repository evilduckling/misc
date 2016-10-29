<?php

$usage = <<<USAGE
Usage: php nanainlyseur.php [-r <range>] -p <positionx,positiony> -n name [-m] [-h]
Options:
    -h display this message
    -m display map
    -n name
    -p player position (x,y)
    -r range of sight

USAGE;

$options = getopt('hn:mp:r:');

if(isset($options['h'])) {
    echo $usage;
    exit(0);
}

if(isset($options['m'])) {
    $debugger = true;
} else {
    $debugger = false;
}

if(isset($options['r'])) {
    $range = $options['r'];
} else {
    $range = 1;
}

if(isset($options['p'])) {
    list($mine_i, $mine_j) = explode(',', $options['p']);
} else {
    echo 'Missing position parameter'.PHP_EOL;
    exit(0);
}

if(isset($options['n'])) {
    $nickname = $options['n'];
} else {
    echo 'Missing name parameter'.PHP_EOL;
    exit(0);
}

$level = '141cm';
$squareRange = getSquareRange($range + 1);

if(!$debugger) echo " [[*CD*]] $nickname (Longueur barbe : $level - Contractuelle - distance : 0 - position : $mine_i,$mine_j) : (pas de description) | Attaquer ! | Ecrire | ".PHP_EOL;
if(!$debugger) echo "XXXXX".PHP_EOL;

for($j=1; $j<= 8;$j++) {
    for($i=1; $i<= 22;$i++) {

        $squareDistance = pow(($i - $mine_i), 2) + pow(($j - $mine_j), 2);

        if(
            ($i != $mine_i or $j != $mine_j) and
            $squareDistance < $squareRange
        ) {
            if(!$debugger) echo " Bandeau Noir (distance : 6 position : $i, $j) Tombe en poussière dans : 8 jour(s) 7 heures".PHP_EOL;
            if($debugger) echo ' # ';
        } else {
            if($debugger) echo ' - ';
        }
    }
    if($debugger) echo PHP_EOL;
}

function getSquareRange($range)
{
    switch($range) {
        case 2:
            return 4;
        case 3:
            return 8;
        case 4:
            return 13;
        case 5:
            return 21;
        case 6:
            return 30;
        case 7:
            return 45;
        case 8:
            return 56;

            // TODO

        default :
            return pow($range, 2);
    }
}
