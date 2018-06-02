<?php
require_once(__DIR__.'/../blockChain/blockchain.php');
require_once(__DIR__.'/../utilities.php');


/*
Set up a simple chain and mine blocks.
*/

echo "##################################################\n";
echo "---         LAUNCHING ELISHA COIN              ---\n";
echo "##################################################\n";
echo "\n";


$elishaCoin = new BlockChain();
printLine(json_encode($elishaCoin, JSON_PRETTY_PRINT));


echo "--------------------------------------------------\n";
echo "***      CREATING COMPUTER ACCOUNTS            ***\n";
echo "\n";

$computer1Address = "computer1";
$computer2Address = "computer2";



$newTransaction;
function generateTransaction($currentChain, $address1, $address2, $minerAddress)
{
	$result;
	$canProceed = false;
	while(!$canProceed)
	{
		$coin = rand(0,2);
		$newAmount = rand(0,2000);
		
		$payer = ($coin == 0 ? $address1 : $address2);
		$payee = ($coin == 0 ? $address2 : $address1);
		
		$result = new Transaction
		(
			array
			(
				new Payment
				(
					$payer, 
					-$newAmount
				)
				,
				new Payment
				(
					$payee, 
					$newAmount
				)
				,
				// transaction fee to mine coins
				new Payment
				(
					$minerAddress, 
					12.5
				)
			)
		);
		
		echo " - $payer wants to send $newAmount elisha coins to $payee.\n";
		
		$canProceed = $currentChain->validateTransaction($payer, $newAmount);
		if (!$canProceed)
		{
			echo "!!!   But $payer does not have enough funds to do so.   !!!\n\n";
			echo ">>> Press enter to continue";
			echo fgets(STDIN);
			echo "\n";
		}
	}
	
	return $result;
}



printLine(" - Giving computer1 1000 elisha coins ...");
$elishaCoin->push
(
	new Block
	(
		1, 
		strtotime("now"), 
		new Transaction
		(
			array
			( 
				new Payment
				(
					$computer1Address, 
					1000
				)
			) 
		)
	)
);

printLine(" - Giving computer2 1000 elisha coins ...");
$elishaCoin->push
(
	new Block
	(
		2, 
		strtotime("now"), 
		new Transaction
		(
			array
			( 
				new Payment
				(
					$computer2Address, 
					1000
				)
			) 
		)
	)
);

printLine(" - Accounts Created.");

printLine(json_encode($elishaCoin, JSON_PRETTY_PRINT));


$userAddress = "user";	
$coinNo = 3;

echo "--------------------------------------------------\n";
echo "***        INITIATING MINING MODE              ***\n";
echo "\n";

while (true)
{
	echo "~  FUNDS  ~\n";
	echo "	".$computer1Address;
	echo "\n 	: ".$elishaCoin->acquireBalance($computer1Address)." elisha coins\n";
	echo "	".$computer2Address;
	echo "\n 	: ".$elishaCoin->acquireBalance($computer2Address)." elisha coins\n";
	echo "	".$userAddress;
	echo "\n 	: ".$elishaCoin->acquireBalance($userAddress)." elisha coins\n\n";
	
	$newTransaction = generateTransaction($elishaCoin, $computer1Address, $computer2Address, $userAddress);
	
	echo "\n";
	echo ">>> Press enter to mine transaction";
	echo fgets(STDIN);
	
	printLine(" - Mining block $coinNo...");
	$elishaCoin->push(new Block($coinNo, strtotime("now"), $newTransaction));

	printLine(json_encode($elishaCoin, JSON_PRETTY_PRINT));
	
	printLine(" - You have earned 12.5 elisha coins.");

	$coinNo = $coinNo + 1;

}
