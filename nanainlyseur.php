<?php

$usage = <<<USAGE
Usage: php nanainlyseur.php [-r <range>] -p <positionx,positiony> -n name [-h]
Options:
    -h display this message
    -n name
    -p player position (x,y)
    -r range of sight

USAGE;

const DEBUGGER = false;
$options = getopt('hn:p:r:');

if(isset($options['h'])) {
    echo $usage;
    exit(0);
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

$level = '132cm';
$squareRange = getSquareRange($range + 1);

if(!DEBUGGER) echo " [[*CD*]] $nickname (Longueur barbe : $level - Contractuelle - distance : 0 - position : $mine_i,$mine_j) : (pas de description) | Attaquer ! | Ecrire | ".PHP_EOL;
if(!DEBUGGER) echo "XXXXX".PHP_EOL;

for($j=1; $j<= 8;$j++) {
    for($i=1; $i<= 22;$i++) {

        $squareDistance = pow(($i - $mine_i), 2) + pow(($j - $mine_j), 2);

        if(
            ($i != $mine_i or $j != $mine_j) and
            $squareDistance < $squareRange
        ) {
            if(!DEBUGGER) echo " Bandeau Noir (distance : 6 position : $i, $j) Tombe en poussière dans : 8 jour(s) 7 heures".PHP_EOL;
            if(DEBUGGER) echo ' # ';
        } else {
            if(DEBUGGER) echo ' - ';
        }
    }
    if(DEBUGGER) echo PHP_EOL;
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

            // TODO

        case 8:
            return 56;

        default :
            pow($range, 2);
    }
}
