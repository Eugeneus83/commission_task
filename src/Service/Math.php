<?php

declare(strict_types=1);

namespace Eugene\CommissionTask\Service;

use Eugene\CommissionTask\Service\Rules\AbstractRule;
use Eugene\CommissionTask\Service\Rules\Deposit;
use Eugene\CommissionTask\Service\Rules\WithdrawPrivate;
use Eugene\CommissionTask\Service\Rules\WithdrawBusiness;
use Eugene\CommissionTask\Service\CurrencyRateManager;
use Eugene\CommissionTask\Exceptions\SourceFileNotFoundException;

class Math
{
    private $outputData = [];
    private $currencyRateManager;

    function __construct(
        private float $depositFeePercentage,
        private float $withdrawBusinessFeePercentage,
        private float $withdrawPrivateFeePercentage,
        private float $withdrawPrivateNoChargeLimitAmount,
        private int $withdrawPrivateNoChargeTransactionLimit,
        string $currencyCode
    ){
            // Class that manage currency rates
            $this->currencyRateManager = new CurrencyRateManager($currencyCode);
     }

    public function processData(array $inputData)
    {
        foreach ($inputData as $row) {
            $row = array_map('trim', $row);
            // Calculate commission fee for current transaction
            $feeAmount = $this->calculateTransactionFee((int)$row[1], $row[0], $row[2], $row[3], (float)$row[4], $row[5]);
            //Add output log
            $this->addOutput($this->formatAmount($feeAmount));
        }
    }

    public function calculateTransactionFee(int $userId, string $transactionDate, string $clientType, string $operationType, float $amount, string $currency): float
    {
        // Find the corresponding class responsible for fee calculation, depends on operation type and client type
        $ruleObject = null;
        if ($operationType === 'deposit') {
            $ruleObject = Deposit::getInstance();
            $ruleObject->setFeePercentage($this->depositFeePercentage);
        } elseif ($operationType === 'withdraw') {
            if ($clientType === 'business') {
                $ruleObject = WithdrawBusiness::getInstance();
                $ruleObject->setFeePercentage($this->withdrawBusinessFeePercentage);
            } elseif ($clientType === 'private') {
                $ruleObject = WithdrawPrivate::getInstance();
                $currencyRate = $this->currencyRateManager->getRate($currency);
                $ruleObject->setFeePercentage($this->withdrawPrivateFeePercentage);
                // For private client and withdraw transaction we do some extract setup - free of charge amount and free transactions limit
                $ruleObject->setNoChargeLimitAmount($this->withdrawPrivateNoChargeLimitAmount);
                $ruleObject->setNoChargeTransactionsLimit($this->withdrawPrivateNoChargeTransactionLimit);
                // Setup currency rate
                $ruleObject->setCurrencyRate($currencyRate);
            }
        }

        $feeAmount = 0;
        if ($ruleObject) {
            $feeAmount = $this->calculateRuleTransactionFee($ruleObject, $userId, $transactionDate, $amount);
        }

        return $feeAmount;
    }

    private function calculateRuleTransactionFee(AbstractRule $rule, int $userId, string $operationDate, float $amount): float
    {
        $rule->setupTransaction($userId, $operationDate, $amount);
        $feeAmount = $rule->calculateFee();
        // Save current transaction in transactions history for future calculations
        $rule->saveTransaction();

        return $feeAmount;
    }

    public function readInput(string $path): array
    {
        if (!file_exists($path)) {
            throw new SourceFileNotFoundException('Source file not found');
        }

        $fp = fopen($path, 'r');
        $inputData = [];
        while ($row = fgetcsv($fp, 10000, ',', '"')) {
            $inputData[] = $row;
        }
        fclose($fp);

        return $inputData;
    }

    private function addOutput($line): void
    {
         $this->outputData[] = $line;
    }

    public function getOutput(): array
    {
        return $this->outputData;
    }

    public function formatAmount($amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
