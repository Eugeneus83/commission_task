<?php

declare(strict_types=1);

namespace Eugene\CommissionTask\Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Eugene\CommissionTask\Service\Math;

class MathTest extends TestCase
{
    /**
     * @var Math
     */
    private $math;

    public function setUp(): void
    {
        $this->math = new Math(0.03, 0.5, 0.3, 1000, 3, 'EUR');
    }

    public static function dataProviderForCalculationTesting(): array
    {
        return [
            ['2014-12-31', 4, 'private', 'withdraw', 1200.00, 'EUR', 0.60],
            ['2015-01-01', 4, 'private', 'withdraw', 1000.00, 'EUR', 3.00],
            ['2016-01-05', 4, 'private', 'withdraw', 1000.00, 'EUR', 0.00],
            ['2016-01-05', 1, 'private', 'deposit', 200.00, 'EUR', 0.06],
            ['2016-01-06', 2, 'business', 'withdraw', 300.00, 'EUR', 1.50],
            ['2016-01-06', 1, 'private', 'withdraw', 30000, 'JPY', 0.00],
            ['2016-01-07', 1, 'private', 'withdraw', 1000.00, 'EUR', 0.56],
            ['2016-01-07', 1, 'private', 'withdraw', 100.00, 'USD', 0.30],
            ['2016-01-10', 1, 'private', 'withdraw', 100.00, 'EUR', 0.30],
            ['2016-01-10', 2, 'business', 'deposit', 10000.00, 'EUR', 3.00],
            ['2016-01-10', 3, 'private', 'withdraw', 1000.00, 'EUR', 0.00],
            ['2016-02-15', 1, 'private', 'withdraw', 300.00, 'EUR', 0.00],
            ['2016-02-19', 5, 'private', 'withdraw', 3000000, 'JPY', 8514.85]
        ];
    }

    #[DataProvider('dataProviderForCalculationTesting')]
    public function testCalculateFee(string $transactionDate, int $userId, string $clientType, string $operationType, float $amount, string $currency, float $expected): void
    {
        $result = $this->math->calculateTransactionFee($userId, $transactionDate, $clientType, $operationType, $amount, $currency);
        $result = round($result, 2);
        $this->assertEquals($expected, $result);
    }
}
