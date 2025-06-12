# MRP K/S - laravel
- PHP (8) Laravel 8 wrapper for MRP K/S
- client have to activate MRP K/S - https://faq.mrp.sk/e-shop-nastavenia/Ako-prepojit-MRP-K-S-s-internetovym-obchodom-538

## FEATURES
- encryption (aes-256-ctr)

## INSTALL
```composer require engazan/mrp-ks-laravel```

run ```php artisan vendor:publish``` look for ```Engazan\MrpKs\MrpKsServiceProvider```

### Config .env
```dotenv
MRP_KS_URI=192.168.0.166
MRP_KS_PORT=120
MRP_KS_USERNAME=MRPDBA
MRP_KS_PASSWORD=MRPDBA
MRP_KS_ENCRYPTION_KEY=
```
- if ```MRP_KS_ENCRYPTION_KEY``` is provided all request are sent as __ENCRYPTED__, also you need __DECRYPT__ response XML

## USAGE
```php
use Engazan\MrpKs\MrpKs;
use Engazan\MrpKs\MrpKsResponse;

$productFilter = [
    'malObraz' => 'T',
    'velObraz' => 'F',
    'SKKAR.CISLO' => '500..510',
];

// products
$response = MrpKs::EXPEO0($productFilter);
$response = MrpKs::EXPEO1($productFilter);

// addresses
$response = MrpKs::ADREO0();

// prices (default filter "cenovaSkupina" is set to "1")
$response = MrpKs::CENEO0();

// CHAINED calls
$mrpKs = new MrpKs();
$response = $mrpKs->setCommand('EXPEO0')
                    ->setFilters($productFilter)
                    ->sendRequest();
               
// DECRYPT response (needed only if MRP_KS_ENCRYPTION_KEY is filled) 
if (config('mrp-ks.encryption')) {
    $decryptedResponse = MrpKsResponse::decryptEncryptedResponse($response);
}

```


## POST requests
Some MRP-K/S endpoints require you to POST arbitrary XML payloads (e.g. invoice imports). Use the new sendPostRequest() helper:

```php
use Engazan\MrpKs\MrpKs;
use Engazan\MrpKs\MrpKsResponse;

// 1) Build your MRPKSData XML payload
$xmlPayload = <<<'XML'
<MRPKSData version="2.0" countryCode="SK" currencyCode="EUR">
  <CustomBlock>
    <Foo>Bar</Foo>
  </CustomBlock>
</MRPKSData>
XML;

// 2) Send it with a POST-only command (e.g. IMPFV0)
$response = (new MrpKs())
    ->setCommand('IMPFV0')
    ->sendPostRequest($xmlPayload);

// 3) Inspect HTTP status and raw envelope
$status   = $response->original['statusCode'];  // e.g. 200
$envelope = $response->original['data'];        // the <mrpEnvelope>…</mrpEnvelope> XML

// 4) If encryption is enabled, decrypt + parse
$plainXml = MrpKsResponse::decryptEncryptedResponse($envelope);
$doc      = new \SimpleXMLElement($plainXml);



```