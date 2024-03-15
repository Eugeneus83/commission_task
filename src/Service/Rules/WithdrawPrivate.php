<?php

namespace Eugene\CommissionTask\Service\Rules;

class WithdrawPrivate extends AbstractRule
{
    /* @var float */

    protected $noChargeLimitAmount = 0;

    /* @var integer */

    protected $noChargeTransactionsLimit = 0;

    /* @var float */

    protected $transactionCurrencyRate = 1;

    public function setNoChargeLimitAmount(float $amount): void
    {
        $this->noChargeLimitAmount = $amount;
    }

    public function setNoChargeTransactionsLimit(int $limit): void
    {
        $this->noChargeTransactionsLimit = $limit;
    }

    public function setCurrencyRate(float $rate): void
    {
        $this->transactionCurrencyRate = $rate;
    }

    /* @return float */

    public function calculateFee(): float
    {
        // Convert transaction amount into the base currency
        $this->transactionAmount = $this->transactionAmount / $this->transactionCurrencyRate;
        $feeAmount = $amountToChange = 0;
        // Get the previous transactions list within the operational week of current transaction
        $weekTransactions = $this->getUserWeekTransactions();

        if (count($weekTransactions) > $this->noChargeTransactionsLimit) {
            // If transactions limit is exceeded charge the fee percentage from the transaction amount
            $amountToChange = $this->transactionAmount;
        }else {
            // find the total amount of all previous transactions
            $previousTransactionsTotalAmount = array_sum(array_column($weekTransactions, 'amount'));
            $transactionsTotalAmount = $previousTransactionsTotalAmount + $this->transactionAmount;

            if ($previousTransactionsTotalAmount >= $this->noChargeLimitAmount) {
                // If the total amount exceeds the free of charge limit we charge fee from the entire transaction amount
                $amountToChange = $this->transactionAmount;
            }elseif ($transactionsTotalAmount > $this->noChargeLimitAmount) {
                // Charge fee from only the amount over the free of charge limit
                $amountToChange = $transactionsTotalAmount - $this->noChargeLimitAmount;
            }
        }

        if ($amountToChange) {
            $feeAmount = $amountToChange * $this->feePercentage / 100;
        }

        // Convert transaction amount back into the target currency
        return $feeAmount * $this->transactionCurrencyRate;
    }
}