<?php

namespace Rahulreghunath\Nttdatapay;

class Atom
{
    private $password;
    public $merchantId;

    public function __construct()
    {
        $this->merchantId = config('nttdatapay.merchantId');
        $this->password = config('nttdatapay.password');
    }
    /**
     * Create payment authentication token
     *
     * @param Array $data
     * @return Integer $atomTokenId
     */
    public function createTokenId($data)
    {
        $data['payInstrument']['merchDetails']['merchId'] = $this->merchantId;
        $data['payInstrument']['merchDetails']['password'] = $this->password;

        $url = config('nttdatapay.payUrl');

        $jsonData = json_encode($data);

        $encData = $this->encrypt($jsonData);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "encData=" . $encData . "&merchId=" . $data["payInstrument"]["merchDetails"]["merchId"],
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $atomTokenId = null;
        $response = curl_exec($curl);
        $resp = json_decode($response, true);

        if (isset($resp['txnMessage']) && $resp['txnMessage'] == 'FAILED') {
            echo $resp['txnDescription'];
        } else {
            $getresp = explode("&", $response);
            $encresp = substr($getresp[1], strpos($getresp[1], "=") + 1);
            $res = $this->decrypt($encresp);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                // echo "error = " . $error_msg;
            }
            if (isset($error_msg)) {
                // echo "error = " . $error_msg;
            }
            curl_close($curl);

            if ($res) {
                if ($res['responseDetails']['txnStatusCode'] == 'OTS0000') {
                    $atomTokenId = $res['atomTokenId'];
                } else {
                    echo "Error getting data";
                    $atomTokenId = null;
                }
            }
        }
        return $atomTokenId;
    }



    /**
     * Track transaction status
     *
     * @param string $mer_txn
     * @param string $amount
     * @param string $date
     * @return Array response array from API
     */
    public function transactionStatus($mer_txn, $amount, $date)
    {
        $data = "merchantid={$this->merchantId}&merchanttxnid={$mer_txn}&amt={$amount}&tdate={$date}";
        $enc = $this->encrypt($data, 'sha1');

        $url = config('nttdatapay.transactionTrackingUrl');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_PORT => 443,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "login=" . $this->merchantId . "&encdata=" . $enc,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
            CURLOPT_USERAGENT => 'woo-commerce plugin',
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $this->decrypt($response, 'sha1');
    }

    /**
     * Encrypt request data
     *
     * @param string $data
     * @param string $digest_algo
     * @return string encrypted data
     */

    public function encrypt($data, $digest_algo = 'sha512')
    {
        $encRequestKey = config('nttdatapay.encKey');
        $method = "AES-256-CBC";
        $iv = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        $chars = array_map("chr", $iv);
        $IVbytes = join($chars);
        $salt1 = mb_convert_encoding($encRequestKey, "UTF-8"); //Encoding to UTF-8
        $key1 = mb_convert_encoding($encRequestKey, "UTF-8"); //Encoding to UTF-8
        $hash = openssl_pbkdf2($key1, $salt1, '256', '65536', $digest_algo);
        $encrypted = openssl_encrypt($data, $method, $hash, OPENSSL_RAW_DATA, $IVbytes);
        return bin2hex($encrypted);
    }

    /**
     * Decrypt request data
     *
     * @param string $data
     * @param string $digest_algo
     * @return JSON decrypted data
     */
    public function decrypt($data, $digest_algo = 'sha512')
    {
        $decResponseKey = config('nttdatapay.decKey');

        $dataEncypted = hex2bin($data);
        $method = "AES-256-CBC";
        $iv = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        $chars = array_map("chr", $iv);
        $IVbytes = join($chars);
        $salt1 = mb_convert_encoding($decResponseKey, "UTF-8"); //Encoding to UTF-8
        $key1 = mb_convert_encoding($decResponseKey, "UTF-8"); //Encoding to UTF-8
        $hash = openssl_pbkdf2($key1, $salt1, '256', '65536', $digest_algo);
        $decrypted = openssl_decrypt($dataEncypted, $method, $hash, OPENSSL_RAW_DATA, $IVbytes);
        return json_decode($decrypted, true);
    }
}
