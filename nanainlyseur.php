<?php

$usage = <<<USAGE
Usage: php nanainlyseur.php [-r <range>] -p <positionx,positiony> [-h]
Options:
    -h display this message
    -p player position (x,y)
    -r range of sight

USAGE;

$options = getopt('hp:r:');

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

$nickname = "Evil Duckling";
$level = '132cm';
$range++;

echo " [[*CD*]] $nickname (Longueur barbe : $level - Contractuelle - distance : 0 - position : $mine_i,$mine_j) : (pas de description) | Attaquer ! | Ecrire | ".PHP_EOL;
echo "XXXXX".PHP_EOL;

for($i=1; $i<= 22;$i++) {
    for($j=1; $j<= 8;$j++) {

        $distance = sqrt(pow(($i - $mine_i), 2) + pow(($j - $mine_j), 2));

        if(
            ($i != $mine_i or $j != $mine_j) and
            $distance < $range
        ) {
            echo " Bandeau Noir (distance : 6 position : $i, $j) Tombe en poussière dans : 8 jour(s) 7 heures".PHP_EOL;
        }
    }
}
