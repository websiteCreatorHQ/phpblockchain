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


// data that will be pushed into the blockchain
$newTransaction;
/*
	generateTransaction:
	- simulates two computers transferring funds to each other,
	  will only continue if the computer sending funds has enough funds to send
	- TODO:
		- Add option to add invalid transaction
		- Create threads for other miners to reject that block
*/
function generateTransaction($currentChain, $address1, $address2, $minerAddress)
{
	$result;
	$canProceed = false;
	while(!$canProceed)
	{
		$coin = rand(0,2);
		$newAmount = rand(0,2000);
		
		$payer = ($coin > 1 ? $address1 : $address2);
		$payee = ($coin > 1 ? $address2 : $address1);
		
		$result = new Transaction
		(
			array
			(
				new Payment
				(
					$payer,
					- $newAmount
				)
				,
				new Payment
				(
					$payee,
					$newAmount
				)
				,
				new Payment
				(
					$minerAddress,
					12.5
				)
			)
		);
		
		echo 
" 
 - MESSAGE RECIEVED:
   $payer wants to send $newAmount elisha coins to $payee.\n
";
		echo ">>> Press enter to verify this unconfirmed transaction";
		echo fgets(STDIN);
			
		$canProceed = verifyUnconfirmedTransaction($currentChain, $payer, $newAmount);
	}
	
	return $result;
}

function verifyUnconfirmedTransaction($currentChain, $payer, $newAmount)
{
	$passedAllTests = true;
	
	echo " - Checking if $payer has $newAmount elisha coins or more ...\n";
	if (!$currentChain->validateTransaction($payer, $newAmount))
	{
		$passedAllTests = false;
		echo "   [FAILED] : $payer does not have enough funds.\n\n";
		echo ">>> Press enter to reject this unconfirmed transaction";
		echo fgets(STDIN);
		echo "\n";
	}
	else
	{
		echo "   [PASSED]!\n\n";
	}
	
	if ($passedAllTests)
	{
		echo "\n   This transaction has passed all the verification tests!\n";
	}
	
	return $passedAllTests;
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
	// show available funds
	echo "~  FUNDS  ~\n";
	echo "	".$computer1Address;
	echo "\n 	: ".$elishaCoin->acquireBalance($computer1Address)." elisha coins\n";
	echo "	".$computer2Address;
	echo "\n 	: ".$elishaCoin->acquireBalance($computer2Address)." elisha coins\n";
	echo "	".$userAddress;
	echo "\n 	: ".$elishaCoin->acquireBalance($userAddress)." elisha coins\n\n";
	
	// computers will make new transaction
	$newTransaction = generateTransaction($elishaCoin, $computer1Address, $computer2Address, $userAddress);
	
	echo "\n";
	echo ">>> Press enter to confirm and mine transaction";
	echo fgets(STDIN);
	
	// mine the transaction
	printLine(" - Mining block $coinNo...");
	$elishaCoin->push(new Block($coinNo, strtotime("now"), $newTransaction));

	printLine(json_encode($elishaCoin, JSON_PRETTY_PRINT));
	
	printLine(" - You have earned 12.5 elisha coins.");

	$coinNo = $coinNo + 1;
}
