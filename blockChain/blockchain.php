<?php
require_once("block.php");

/**
 * A simple blockchain class with proof-of-work (mining).
 */
class BlockChain
{
    /**
     * Instantiates a new Blockchain.
     */
    public function __construct()
    {
        $this->chain = [$this->createGenesisBlock()];
		
		// TODO: increase if more computers are used
        $this->difficulty = 4;
    }

    /**
     * Creates the genesis block.
     */
    private function createGenesisBlock()
    {
        return new Block(0, strtotime("2017-01-01"), "Genesis Block");
    }

    /**
     * Gets the last block of the chain.
     */
    public function getLastBlock()
    {
        return $this->chain[count($this->chain)-1];
    }

    /**
     * Pushes a new block onto the chain.
     */
    public function push($block)
    {
        $block->previousHash = $this->getLastBlock()->hash;
        $this->mine($block);
        array_push($this->chain, $block);
    }

    /**
     * Mines a block.
     */
    public function mine($block)
    {
        while (substr($block->hash, 0, $this->difficulty) !== str_repeat("0", $this->difficulty)) {
            $block->nonce++;
            $block->hash = $block->calculateHash();
        }

        echo "Block mined: ".$block->hash."\n";
    }

    /**
     * Validates the blockchain's integrity. True if the blockchain is valid, false otherwise.
     */
    public function isValid()
    {
        for ($i = 1; $i < count($this->chain); $i++) {
            $currentBlock = $this->chain[$i];
            $previousBlock = $this->chain[$i-1];

            if ($currentBlock->hash != $currentBlock->calculateHash()) {
                return false;
            }

            if ($currentBlock->previousHash != $previousBlock->hash) {
                return false;
            }
        }

        return true;
    }
	
	/**
	 * validateTransaction:
	 * - acquires an address, and checks all the transactions this address went through
	 *   to determine if they have the valid amount to send
	 */
	public function validateTransaction($checkingAddress, $checkingAmount, $currentAmount = 0, $currentIndex = 0)
	{
		$reverseIndex = (count($this->chain) - 1) - $currentIndex;
		$reverseIndex = $reverseIndex < 0 ? 0 : $reverseIndex;
		$currentTransaction = $this->chain[$reverseIndex]->data;
		
		if ($currentTransaction instanceof Transaction)
		{
			$currentAmount = $currentAmount + $currentTransaction->acquirePayment($checkingAddress);
		}
		
		// if address has enough funds, return true
		if ($currentAmount >= $checkingAmount)
		{
			return true;
		}
		
		// if first block is reached and checking amount is still too high,
		// checking amount is invalid
		if ($currentIndex >= count($this->chain))
		{
			return false;
		}
		
		return $this->validateTransaction($checkingAddress, $checkingAmount, $currentAmount, $currentIndex + 1);
	}
	
	
	/**
	 * acquireBalance:
	 * - acquires an address, and checks all the transactions this address went through,
	 *   adding all the funds that were gained or lost, and returns that result
	 */
	public function acquireBalance($checkingAddress, $currentAmount = 0, $currentIndex = 0)
	{
		$reverseIndex = (count($this->chain) - 1) - $currentIndex;
		$reverseIndex = $reverseIndex < 0 ? 0 : $reverseIndex;
		$currentTransaction = $this->chain[$reverseIndex]->data;
		
		if ($currentTransaction instanceof Transaction)
		{
			$currentAmount = $currentAmount + $currentTransaction->acquirePayment($checkingAddress);
		}
		
		if ($currentIndex >= count($this->chain))
		{
			return $currentAmount;
		}
		
		return $this->acquireBalance($checkingAddress, $currentAmount, $currentIndex + 1);
	}
}
