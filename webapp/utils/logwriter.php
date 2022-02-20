<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
class logwriter
{
    function __construct()
    {}

    public function log($uri, $logtext)
    {
        
        //make sure empty log id not recieved
        $message = trim($logtext);
        if($message != "" && $message != null && !empty($message))
        {
            //save new log
            $fp = fopen($uri,'a');
            fwrite($fp,$message."\n");
            fclose($fp);
        }
    } 

    //delete log
    public function deletelog($uri, $logtext)
    {
        //check if file exist before attempting delete
        if(file_exists($uri))
        {
            unlink($uri);
        }
    }


}