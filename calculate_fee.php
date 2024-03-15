<?php

use Eugene\CommissionTask\Exceptions\RateNotExtractedException;
use Eugene\CommissionTask\Exceptions\RateNotFoundException;
use Eugene\CommissionTask\Exceptions\SourceFileNotFoundException;
use Eugene\CommissionTask\Service\Math;

require 'vendor/autoload.php';

$delimiter = $_SERVER['DOCUMENT_ROOT'] ? "<br/>" : "\n";

$sourcePath = isset($argv[1]) ? trim($argv[1]) : false;

/*Parameters passed to Math constructor
  1) Deposit fee percentage,
  2) Withdraw business client fee percentage,
  3) Withdraw private client fee percentage,
  4) Withdraw private client free of charge limit amount,
  5) Withdraw private client free of charge transactions limit,
  6) Base currency
*/

try {
    $math = new Math(0.03, 0.5, 0.3, 1000, 3, 'EUR');
    $inputData = $math->readInput($sourcePath);
    $math->processData($inputData);
    $output = $math->getOutput();
    foreach ($output as $line) {
        echo $line. $delimiter;
    }
} catch (RateNotExtractedException $e) {
    echo "Extract currency rate exception: " . $e->getMessage();
} catch (RateNotFoundException $e) {
    echo "Rate exception: " . $e->getMessage();
} catch (SourceFileNotFoundException $e) {
    echo "Source file exception. " . $e->getMessage();
}