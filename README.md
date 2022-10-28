# ntt-atom-payment

Laravel integration for [NTT DATA PAY](https://www.nttdatapay.com) Payment gateway.

## Installation

Install the package using using composer install.
```shell
composer require rahulreghunath/nttdatapay
```

Run the command to publish the configuration file.
        
```shell
php artisan vendor:publish --provider="Rahulreghunath\Nttdatapay\ServiceProvider"
```        
## Configuration
Set the credentials and configurations in `config/nttdatapay.php` file.


|Configuration|Description|Required|
|----|----|:-:|
|encKey|Encryption Key|<img src="https://github.githubassets.com/images/icons/emoji/unicode/2714.png" width="20px"/>|
|decKey|Decryption Key|<img src="https://github.githubassets.com/images/icons/emoji/unicode/2714.png" width="20px"/>|
|payUrl|Payment Url|<img src="https://github.githubassets.com/images/icons/emoji/unicode/2714.png" width="20px"/>|
|transactionTrackingUrl|Transaction Tracking Url|<img src="https://github.githubassets.com/images/icons/emoji/unicode/274c.png" width="20px">
|merchantId|Merchant id|<img src="https://github.githubassets.com/images/icons/emoji/unicode/2714.png" width="20px"/>|
|password| Merchant Password|<img src="https://github.githubassets.com/images/icons/emoji/unicode/2714.png" width="20px"/>|

Please note that the configurations will be different for testing and production environments and will be provided by NTT DATA.
 
 ## Usage

 #### Create Token Id
use the method `createTokenId($data)` to create token id to initiate the payment request.

sample data


```php
$data = [
    "payInstrument" => [
        "headDetails" => [
            "version" => "OTSv1.1",
            "api" => "AUTH",
            "platform" => "FLASH"
        ],
        "merchDetails" => [
            "merchTxnId" => "Test123450",
            "merchTxnDate" => "2021-09-04 20:46:00"
        ],
        "payDetails" => [
            "amount" => "1",
            "product" => "PRODUCT", // optional value
            "custAccNo" => "ACC NO", // optional value
            "txnCurrency" => "INR"
        ],
        "custDetails" => [
            "custEmail" => "user@email.com",
            "custMobile" => "0000000000"
        ],
        "extras" => [
            "udf1" => "", // optional value
            "udf2" => "", // optional value
            "udf3" => "", // optional value
            "udf4" => "", // optional value
            "udf5" => "" // optional value
        ]
    ]
];

```

```php
$payment = new Atom();

$atomTokenId = $payment->createTokenId($data);
  ```
 #### Calling Javascript API
Use the Atom Token Id to call the javascript API

```javascript
<button onclick="pay()">Pay</button>

<script src="CDN provided by NTT DATA"></script>

<script> 
    const pay=()=>{
        const options = {
            atomTokenId: "atomTokenId ", // token id get from atom
            merchId: "000000", // merchant id
            custEmail: "customer-email",
            custMobile: "customer-mobile",
            returnUrl: "your-response-url"
        }
        const atom = new AtomPaynetz(options,'uat');
    }
</script>
 ```
Mandatory JavaScript CDN link will be provided by NTT DATA and will be different for production and testing environments.

#### Check Transaction Status

check the status of the payment using `transactionStatus($merTxn,$amt,$date)` method.

```php
$payment = new Atom();

$response = $payment->transactionStatus($merchantTransactionId,$amount,$date);
 ```

 #### Decrypt Response

 use `decrypt($data)` to decrypt the response message from Atom.

 ```php
 $payment = new Atom();

 $jsonData = $payment->decrypt($encryptedData,$digest_algo="sha512");
  ```
  default hashing algorithm used is `sha512` and can be use different algorithm based as per NTT DATA's specifications.
