# 18app
18app PHP SDK.

## Install
Copy the files under `src/` to your program

OR

```bash
composer require mrpck/app18 1.0.0
```


## Usage

```php
use Mrpck\app18\app18;

// connecting to 18app
$app = new app18($certificato, $pswd, $wdsl_url, $pi);

$codice_voucher = 'XmHnBpO0';
$response = $app->VerificaVoucher($codice_voucher);

```
