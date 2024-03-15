# Eugene Commission task skeleton

How system should be run?

The command to run system: "php calculate_fee.php input.csv"

How to initiate system's tests?

The command to run tests: "php bin/phpunit tests" or "composer run test"

Short description:
When you run script calculate_fee.php it reads data from the input file (file input.csv is included), then for each transactions it defines the class
that is responsible for the calculation and pass data to the class. When calculation is done it saves the result and saves the transaction to the transaction log
for future calculations with the next lines.


