<?php

$mine_i = 13;
$mine_j = 3;
$range = 15;

echo " [[*CD*]] Evil Duckling (Longueur barbe : 132cm - Contractuelle - distance : 0 - position : $mine_i,$mine_j) : (pas de description) | Attaquer ! | Ecrire | ".PHP_EOL;
echo "XXXXX".PHP_EOL;

for($i=1; $i<= 22;$i++) {
	for($j=1; $j<= 8;$j++) {
		if($i !== $mine_i or $j !== $mine_j) {
			echo " Bandeau Noir (distance : 6 position : $i, $j) Tombe en poussière dans : 8 jour(s) 7 heures".PHP_EOL;
		}
	}
}
