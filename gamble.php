<?php

/**
 * Determine gambling probabilities on doubling up.
 *
 */

const STARTING_AMOUNT = 10; //$
const TOTAL_ROLL = 150; //$
const WINNING_CHANCE = 46; //%

// Gamble
$losingRound = 0;
$amount = TOTAL_ROLL;

while($amount >= pow(2, $losingRound) * STARTING_AMOUNT) {
	$amountGambled = pow(2, $losingRound) * STARTING_AMOUNT;
	if(mt_rand(1, 100) <= WINNING_CHANCE) {
		//win
		$losingRound = 0;
		$amount += $amountGambled;
		echo "win";
	} else {
		//lose
		$losingRound++;
		$amount -= $amountGambled;
		echo "loose";
	}

	echo " new balance is $amount<br/>";

	if($amountGambled * 2 > 100) {
		$losingRound = 0;
	}
}

echo "You lost! ($losingRound in a raw)";
exit;