<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
class codegenerator{

    private $code = "";

    //default digits is 4
    function __construct($digits = 4)
    {
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digits){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        //Convert the binary data into hexadecimal representation.
        $this->code = $pin;
    }

    public function getCode()
    {
        return $this->code;
    }
}