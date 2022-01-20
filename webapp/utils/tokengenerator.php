<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
class tokengenerator{

    private $token = "";
    private $filetoken = "";

    function __construct()
    {
        //Generate a random string.
        
        //Convert the binary data into hexadecimal representation.
        $this->token = bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16))."".bin2hex(openssl_random_pseudo_bytes(16))."".md5("genotypematch")."". md5(openssl_random_pseudo_bytes(16))."".md5("I was born on the 26th of december 1996, call me +2348028449626").md5(openssl_random_pseudo_bytes(16));  
        
    }

    public function getToken()
    {
        return $this->token;
    }
    
    public function getFileToken()
    {
        return $this->filetoken = bin2hex(openssl_random_pseudo_bytes(16));
    }
}