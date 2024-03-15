<?php

namespace Eugene\CommissionTask\Service\Rules;

abstract class AbstractRule
{
    /* @var float */
    protected $feePercentage = 0;

    /* @var integer */

    protected $transactionUserId;

    /* @var string */

    protected $transactionDate;

    /* @var float */

    protected $transactionAmount;

    /* @var array */

    private static $instances = [];

    /* @var array */

    protected $transactions = [];

    protected function __construct(){}

    protected function __clone() {}

    /* @return float */

    public function setFeePercentage(float $percent): void
    {
        $this->feePercentage = $percent;
    }

    public function setupTransaction(int $userId, string $date, float $amount): void
    {
        $this->transactionUserId = $userId;
        $this->transactionDate = $date;
        $this->transactionAmount = $amount;
    }

    public function saveTransaction(): void
    {
        if (!isset($this->transactions[$this->transactionUserId])) {
            $this->transactions[$this->transactionUserId] = [];
        }
        $this->transactions[$this->transactionUserId][] = ['date' => strtotime($this->transactionDate), 'amount' => $this->transactionAmount];
    }

    /* @return array */

    protected function getUserTransactions(): array
    {
        return isset($this->transactions[$this->transactionUserId]) ? $this->transactions[$this->transactionUserId] : [];
    }

    /* @return array */

    protected function getUserWeekTransactions(): array
    {
        $weekTransactions = [];
        $transactions = $this->getUserTransactions();
        if (count($transactions)) {
            $operationTime = strtotime($this->transactionDate);
            $weekDateNum = date('w', $operationTime);
            $daysFromStartWeek = $weekDateNum > 0 ? $weekDateNum - 1 : 6;
            // Find the date when the operational week start
            $startWeekTime = $operationTime - 3600 * 24 * $daysFromStartWeek;
            // Find the date when the operational week end
            $endWeekTime = $startWeekTime + 3600 * 24 * 6;
            // Find all the transactions within the current transaction operational week
            foreach ($transactions as $transaction) {
                if ($transaction['date'] >= $startWeekTime && $transaction['date'] <= $endWeekTime) {
                    $weekTransactions[] = $transaction;
                }
            }
        }

        return $weekTransactions;
    }

    /* @return float */

    public function calculateFee(): float
    {
        return $this->transactionAmount * $this->feePercentage / 100;
    }

    public static function getInstance(): static
    {
        $className = static::class;
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new static();
        }
        return self::$instances[$className];
    }
}

