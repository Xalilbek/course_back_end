<?php


namespace App\Helpers;

class SendSmsHelper
{
    public static function send($phone,$code)
    {
        $url = 'http://sms.atltech.az:8080/bulksms/api';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
         <request>
            <head>
                <operation>submit</operation>
                <login>AppGunsms</login>
                <password>1Fps26Gz</password>
                <title>appGundelik</title>
                <scheduled>NOW</scheduled>
                <isbulk>false</isbulk>
                <controlid>'.rand(10,100).time().'</controlid>
            </head>
            <body>
                <msisdn>'.$phone.'</msisdn>
                <message>Sizin tesdiqleme kodunuz : '.$code.'.Tehlukesizlik uchun kodu hechkime gostermeyin.Kod 30 saniye erzinde aktivdir</message>
            </body>
         </request>';

        return self::xml_extract_object(self::sendResponse($url,$xml));
    }

    public static function sendResponse($url,$xml){
        $ch = curl_init($url);    // initialize curl handle
        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        #curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).DIRECTORY_SEPARATOR."certs".DIRECTORY_SEPARATOR."azzon.crt");
//        curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__).DIRECTORY_SEPARATOR."certs".DIRECTORY_SEPARATOR."azzon.crt");
//        curl_setopt($ch, CURLOPT_SSLKEY, dirname(__FILE__).DIRECTORY_SEPARATOR."certs".DIRECTORY_SEPARATOR."azzon.key");
        #curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->sert);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        // curl_setopt($ch, CURLOPT_STDERR, $stdout);
        if( ! $answer = curl_exec($ch)) {
            throw new \Exception(curl_errno($ch));
        }
        curl_close($ch);

        return $answer;
    }

    public static function xml_extract_object($xml) {
        $parse_it = simplexml_load_string($xml);
        return json_decode(json_encode($parse_it));
    }
}
