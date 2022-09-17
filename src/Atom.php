<?php
namespace Rahulreghunath\Nttdatapay;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;

class Atom
{
    public static function merchantAuthAPI($data)
    {
        $jsonData = json_encode($data);
        $encrypted = Crypt::encryptString($jsonData);
        $url = "https://caller.atomtech.in/ots/aipay/auth?{$encrypted}";
        echo $url;

        $client = new Client();
        $res = $client->get($url);
        echo $res->getStatusCode(); // 200
        echo $res->getBody();
    }
}
