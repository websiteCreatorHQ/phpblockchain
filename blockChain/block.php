<?php
class Block
{
    public $nonce;

    public function __construct($index, $timestamp, $data, $previousHash = null)
    {
        $this->index = $index;
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->previousHash = $previousHash;
        $this->hash = $this->calculateHash();
        $this->nonce = 0;
    }

    public function calculateHash()
    {
        return hash
		(
			"sha256", 
				$this->index.
				$this->previousHash.
				$this->timestamp.
				(($this->data instanceof Transaction) ? $this->data->toString() : ((string)$this->data)).
				$this->nonce
		);
    }
}

/**
 * Transaction:
 * - contains details of addresses and the amount
 *   they send or receive, this will be stored in the block
 *   of a block-chain
 */
class Transaction
{
	private $payments;
	
	function __construct($newPayments)
	{
		$this->payments = $newPayments;
	}
	
	/*
		acquirePayment:
		- acquires the address and checks to see if that address
		  is contained inside this transaction
		- returns transaction amount if it exists, 0 otherwise
	*/
	public function acquirePayment($requestingAddress)
	{
		for ($i = 0; $i < count($this->payments); $i++)
		{
			if ($this->payments[$i]->address == $requestingAddress)
			{
				return $this->payments[$i]->transactionAmount;
			}
		}
		
		return 0;
	}
	
	/*
		toString:
		- returns JSON version of array, complete with keys
	*/
	public function toString()
	{
		$result = 
		"
		{
			[
		";
		for ($i = 0; $i < count($this->payments); $i++)
		{
			$currentAddress = $this->payments[$i]->address;
			$currentAmount = $this->payments[$i]->transactionAmount;
			$result .=
			"
					{
						address           : $currentAddress,
						transactionAmount : $currentAmount,
					}
			";
			$result .= (($i == count($this->payments) - 1) ? "" : ",");
		}
		$result .= 
		"
			]
		}
		";
	}
}
/**
 * Payment:
 * - contains the address of a user's wallet as well as their
 *   payment amount
 */
class Payment
{
	public $address;
	public $transactionAmount;
	
	function __construct($newAddress, $newAmount)
	{
		$this->address = $newAddress;
		$this->transactionAmount = $newAmount;
	}
}