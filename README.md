# Eugene Commission task

**Requirements?**

PHP 8.0 or newer.

## Installation

1. Run "composer install" in command line interface, e.g. via SSH.

## How system should be run? What command to use?

php calculate_fee.php input.csv"

## How to initiate system's tests? What command to use? 

You can use one of following commands:
php bin/phpunit tests
composer run test

## Short description

When you run script calculate_fee.php it reads data from the input file (file input.csv is included), then for each transactions it defines the class
that is responsible for the calculation and pass data to the class. When calculation is done it saves the result and saves the transaction to the transaction log
for future calculations with the next lines.


