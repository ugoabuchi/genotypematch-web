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
        //check if file exist, if not create one
        if(!file_exists($uri))
        {
            $fp = fopen($uri,'W');
            fclose($fp);
        }

        //save new log
        $fp = fopen($uri,'a');
        fwrite($fp,$logtext."\n");
        fclose($fp);
    } 

    //delete log
    public function deletelog($uri, $logtext)
    {
        //check if file exist before attempting delete
        if(!file_exists($uri))
        {
            unlink($uri);
        }
    }


}