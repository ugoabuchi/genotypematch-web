<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
class mailer{

    private $to = "";
    private $subject = "";
    private $message = "";

    //default digits is 4
    function __construct($to, $subject, $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function getCode()
    {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <enquiry@example.com>' . "\r\n";
        $headers .= 'Cc: myboss@example.com' . "\r\n";
        
        mail($this->to, $this->subject, $this->message, $headers);
    }
}