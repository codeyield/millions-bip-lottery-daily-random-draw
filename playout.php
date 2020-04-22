<?php

class Playout {

	/**
	 * Selects randomly the specified number of wallets from the array
	 * @param  array       $wallets List of wallets to choose from
	 * @param  integer     $qty     Number of wallets to choose
	 * @return array|false List of randomly selected wallets
	 */
	public function random($wallets, $qty) {
		
		if(!is_array($wallets) or ($qty < 1)) {
			return false;
		}
		
		$winners = [];
			
		for($i = 1; $i <= $qty; $i++) {

			if(count($wallets) > 0) {
				
				// Reordering indexes of array
				$wallets = array_values($wallets);

				// Mersenne Twister random algorithm is used
				$index = mt_rand(0, count($wallets) - 1);
				
				// Adding next winner to the result array
				$winners[] = $wallets[$index];
				
				// Removing an already added winner from the source array
				unset($wallets[$index]);
			
			}
			else {
				break;
			}
			
		}
		
		return $winners;
	}

}