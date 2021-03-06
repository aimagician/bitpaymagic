# bitpaymagic
Simple Laravel 5 wrapper for the [Bitpay php client](https://github.com/bitpay/php-bitpay-client/)

## Installation
Add this package to your project, with [Composer](https://getcomposer.org/)

```bash
composer require aimagician/bitpaymagic
```

## Configuration

Run the following command to have Laravel set up a configuration file for you.

```bash
php artisan vendor:publish
```

This will create in config folder a "bitpaymagic.php" config file. Config file uses a .env variables.
Update .env file with these keys.

```bash
BITPAY_STORAGE_PASS=YourTopSecretPassword
BITPAY_PAIRING_CODE=YourPairingCode
BITPAY_PAIRING_CODE_LABEL="Test Token Label - optional"
BITPAY_ENV=testnet
BITPAY_GENERATED_TOKEN="Put here generated token"
```

Pairing code can be setup here in: Payment Tools -> Manage API tokens -> Add new token -> Add token

To generate needed [keys](https://github.com/bitpay/php-bitpay-client/tree/master/examples/tutorial)

run this artisan command, and update generated token value in .env file(BITPAY_GENERATED_TOKEN)

```bash
php artisan bitpaymagic:setup
```

This step is needed to run just once, if error is being shown, please revoke and update pairing code, and try again, 
or follow this [tutorial](https://github.com/bitpay/php-bitpay-client/tree/master/examples/tutorial) and do everything manually.

## Trait
A Bitpaymagic Trait `initBitpaymagicClient()` function will return a client
```
use BitpaymagicTrait;
...
...
$client = $this->initBitpaymagicClient();
```

## Facades
Simply use Laravel facades:
```
use Bitpay\Buyer;
use Bitpay\Currency;
use Bitpay\Invoice;
use Bitpay\Item;
```
 
Bitpay [documentation](https://bitpay.com/docs/) 

How to setup [test environment](https://bitpay.com/docs/testing)

A full working example: [https://github.com/aimagician/bitpay-test](https://github.com/aimagician/bitpay-test) 