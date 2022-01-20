<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require 'composer/vendor/autoload.php';
class mailmanager
{
    private $host = null;
    private $username = null;
    private $password = null;
    private $port = null;
    
    function __construct($host, $port, $username, $password){
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
   
    }
   
    public function send_mail_no_reply(
            $reciepient,  
            $reciepient_name,
            $subject,
            $body,
            $sender_name = "Genotype-Match",
            $sender_domain = "genotypematch.com"
            ){
        
        $mail = new PHPMailer(); //Argument true in constructor enables exceptions
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPDebug = SMTP::DEBUG_OFF;  // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true;  // authentication enabled
        $mail->SMTPSecure = 'ssl';
        $mail->Host = $this->host;
        $mail->Port = $this->port;
        $mail->Username   = $this->username;
        $mail->Password   = $this->password;
        $mail->IsHTML(true);
        $mail->AddAddress($reciepient, $reciepient_name);
        $mail->setFrom($this->username, $sender_name);
        $mail->addReplyTo("no-reply@".$sender_domain, $sender_name);
        $mail->Subject = $subject;
        $mail->Body = $body;
        //$mail->addAttachment($attachment);
        try {
            $mail->send();
        } catch (Exception $e) {
        }

        
    }
    
    
    public function send_mail_with_reply(
            $reciepient,  
            $reciepient_name,
            $subject,
            $body,
            $sender_name = "Genotype-Match",
            $attachment = "https://genotypematch.com/img/logo.png",
            $sender_hosting_email = "info@genotypematch.com"
            ){
        
        $mail = new PHPMailer(); //Argument true in constructor enables exceptions
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true;  // authentication enabled
        $mail->SMTPSecure = 'ssl';
        $mail->Host = $this->host;
        $mail->Port = $this->port;
        $mail->Username   = $this->username;
        $mail->Password   = $this->password;
        
        $mail->IsHTML(true);
        $mail->AddAddress($reciepient, $reciepient_name);
        $mail->setFrom($this->username, $sender_name);
        $mail->addReplyTo($sender_hosting_email, $sender_name);
        $mail->addEmbeddedImage("https://genotypematch.com/img/logo.png", "logoid", "logo.png");
        $mail->Subject = $subject;
        $mail->Body = $body;
        //$mail->addAttachment($attachment);
        try {
            $mail->send();
            return "success";
        } catch (Exception $e) {
            return "error";
        }

        
    }
    
    
    public function emailFormatOne($content){
        
        return '<!DOCTYPE html>
<html>

<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
</head>

<body style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #ffffff; height: 100%; width: 100%; margin: 0; padding: 0;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse;">
        <!-- LOGO -->
        
        <tr>
            <td bgcolor="#ffffff" align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 0px 0px 5px 0px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; max-width: 600px; border-collapse: collapse;">
                    <tr>
                        <td bgcolor="#ffffff" align="center" valign="top" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                          <img src="https://genotypematch.com/img/logo.png" width="120" height="100" style="-ms-interpolation-mode: bicubic; height: auto; outline: none; text-decoration: none; display: block; border: 0px;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 0px 5px 0px 5px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; max-width: 600px; border-collapse: collapse;">
                    <tr>
                        <td bgcolor="#ffffff" align="left" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 10px 10px 10px 0px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">'.$content.'</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td bgcolor="#ffffff" align="left" style="-webkit-text-size-adjust: 100%; padding: 10px 10px 10px 0px; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">Cheers,<br>Genotype-Match Team</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" align="center" style="-webkit-text-size-adjust: 100%; padding: 10px 10px 10px 0px; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; max-width: 600px; border-collapse: collapse;">
                    <tr>
                        <td bgcolor="#ffffff" align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #00B7EC; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <h2 style="font-size: 20px; font-weight: 400; color: #000000; margin: 0;">Need more help?</h2>
                            <p color="#00B7EC" style="margin: 0; color: #00B7EC;">support@genotypematch.com</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#1F3A68" align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 10px 10px 10px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; max-width: 600px; border-collapse: collapse;">
                    <tr>
                        <td bgcolor="#1F3A68" align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #ffffff; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400;">
                            <p style="margin: 0;color: #ffffff;">If you feel this email was not triggered by you, feel free to ignore.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
';
    }
    
    
    public function emailVerificationStyle($name, $code){
        
        return '
<!--[if !mso]><!--><!--<![endif]-->
  <!--[if IE]><div class="ie-container"><![endif]-->
  <!--[if mso]><div class="mso-container"><![endif]-->
  <table style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;background-color: #f7f7f7;width: 100%;line-height: inherit;color: #000000;" cellpadding="0" cellspacing="0">
  <tbody style="line-height: inherit;">
  <tr style="vertical-align: top;line-height: inherit;border-collapse: collapse;">
    <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;line-height: inherit;color: #000000;">
    <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color: #f7f7f7;"><![endif]-->
    

<div class="u-row-container" style="padding: 0px;background-color: transparent;line-height: inherit;">
  <div class="u-row" style="overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;line-height: inherit;">
    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;line-height: inherit;">
      <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0"><tr style="background-color: #ffffff;"><![endif]-->
      
<!--[if (mso)|(IE)]><td align="center" width="550" style="width: 550px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;" valign="top"><![endif]-->
<div class="u-col u-col-100" style="display: table-cell;vertical-align: top;line-height: inherit;">
  <div style="width: 100% !important;line-height: inherit;">
  <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;line-height: inherit;"><!--<![endif]-->
  
<table style="font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody style="line-height: inherit;">
    <tr style="line-height: inherit;vertical-align: top;border-collapse: collapse;">
      <td style="overflow-wrap: break-word;word-break: break-word;font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" align="left">
        
  <table height="0px" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;line-height: inherit;color: #000000;">
    <tbody style="line-height: inherit;">
      <tr style="vertical-align: top;line-height: inherit;border-collapse: collapse;">
        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #000000;">
          <span style="line-height: inherit;">&#160;</span>
        </td>
      </tr>
    </tbody>
  </table>

      </td>
    </tr>
  </tbody>
</table>

  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
  </div>
</div>
<!--[if (mso)|(IE)]></td><![endif]-->
      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
    </div>
  </div>
</div>



<div class="u-row-container" style="padding: 0px;background-color: transparent;line-height: inherit;">
  <div class="u-row" style="overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;line-height: inherit;">
    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;line-height: inherit;">
      <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="left"><table cellpadding="0" cellspacing="0" border="0"><tr style="background-color: #ffffff;"><![endif]-->
      
<!--[if (mso)|(IE)]><td align="left" style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;" valign="top"><![endif]-->
<div class="u-col u-col-100" style="display: table-cell;vertical-align: top;line-height: inherit;">
  <div style="width: 100% !important;line-height: inherit;">
  <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;line-height: inherit;"><!--<![endif]-->
  
<table style="font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody style="line-height: inherit;">
    <tr style="line-height: inherit;vertical-align: top;border-collapse: collapse;">
      <td style="overflow-wrap: break-word;word-break: break-word;font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" align="left">
        
  <div style="line-height: 140%; text-align: left; word-wrap: break-word;">
    <p style="font-size: 14px;line-height: 140%;margin: 0;"><span style="font-size: 16px; line-height: 22.4px;"><strong style="line-height: inherit;">Hi '.$name.',</strong></span></p>
<p style="font-size: 14px;line-height: 140%;margin: 0;"><br style="line-height: inherit;"><span style="font-size: 16px; line-height: 22.4px;">You are almost ready to meet your match!</span></p>
<p style="font-size: 14px;line-height: 140%;margin: 0;"><br style="line-height: inherit;">We need a little more information to complete your registration, including a confirmation of your email address.</p>
<p style="font-size: 14px;line-height: 140%;margin: 0;"><br style="line-height: inherit;">Use the code below to confirm your email address:</p>
  </div>

      </td>
    </tr>
  </tbody>
</table>

<table style="font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody style="line-height: inherit;">
    <tr style="line-height: inherit;vertical-align: top;border-collapse: collapse;">
      <td style="overflow-wrap: break-word;word-break: break-word;padding: 10px;font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" align="left">
        
<div align="center" style="line-height: inherit;">
  <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;font-family:arial,helvetica,sans-serif;"><tr><td style="font-family:arial,helvetica,sans-serif;" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="" style="height:51px; v-text-anchor:middle; width:160px;" arcsize="8%" stroke="f" fillcolor="#FF0808"><w:anchorlock/><center style="color:#FFFFFF;font-family:arial,helvetica,sans-serif;"><![endif]-->
    <a href="" target="_blank" style="box-sizing: border-box;display: inline-block;font-family: arial,helvetica,sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #FFFFFF;background-color: #FF0808;border-radius: 4px;-webkit-border-radius: 4px;-moz-border-radius: 4px;width: auto;max-width: 100%;overflow-wrap: break-word;word-break: break-word;word-wrap: break-word;mso-border-alt: none;line-height: inherit;">
      <span style="display:block;padding:12px 40px;line-height:120%;"><span style="font-size: 22px; line-height: 26.4px;"><strong style="line-height: inherit;"><span style="line-height: 26.4px; font-family: Lato, sans-serif; font-size: 22px;">'.$code.'</span></strong></span></span>
    </a>
  <!--[if mso]></center></v:roundrect></td></tr></table><![endif]-->
</div>

      </td>
    </tr>
  </tbody>
</table>

  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
  </div>
</div>
<!--[if (mso)|(IE)]></td><![endif]-->
      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
    </div>
  </div>
</div>



<div class="u-row-container" style="padding: 0px;background-color: transparent;line-height: inherit;">
  <div class="u-row" style="margin: 0 auto;min-width: 320px;max-width: 550px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;line-height: inherit;">
    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;line-height: inherit;">
      <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:550px;"><tr style="background-color: #ffffff;"><![endif]-->
      
<!--[if (mso)|(IE)]><td align="center" width="550" style="width: 550px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;" valign="top"><![endif]-->
<div class="u-col u-col-100" style="max-width: 320px;min-width: 550px;display: table-cell;vertical-align: top;line-height: inherit;">
  <div style="width: 100% !important;line-height: inherit;">
  <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;line-height: inherit;"><!--<![endif]-->
  
<table style="font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody style="line-height: inherit;">
    <tr style="line-height: inherit;vertical-align: top;border-collapse: collapse;">
      <td style="overflow-wrap: break-word;word-break: break-word;padding: 10px;font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" align="left">
        
  <table height="0px" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #f1f1f1;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;line-height: inherit;color: #000000;">
    <tbody style="line-height: inherit;">
      <tr style="vertical-align: top;line-height: inherit;border-collapse: collapse;">
        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #000000;">
          <span style="line-height: inherit;">&#160;</span>
        </td>
      </tr>
    </tbody>
  </table>

      </td>
    </tr>
  </tbody>
</table>

  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
  </div>
</div>
<!--[if (mso)|(IE)]></td><![endif]-->
      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
    </div>
  </div>
</div>



<div class="u-row-container" style="padding: 0px;background-color: transparent;line-height: inherit;">
  <div class="u-row" style="overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;line-height: inherit;">
    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;line-height: inherit;">
      <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0"><tr style="background-color: #ffffff;"><![endif]-->
      
<!--[if (mso)|(IE)]><td align="left" style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;" valign="top"><![endif]-->
<div class="u-col u-col-100" style="display: table-cell;vertical-align: top;line-height: inherit;">
  <div style="width: 100% !important;line-height: inherit;">
  <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;line-height: inherit;"><!--<![endif]-->
  
<table style="font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody style="line-height: inherit;">
    <tr style="line-height: inherit;vertical-align: top;border-collapse: collapse;">
      <td style="overflow-wrap: break-word;word-break: break-word;font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" align="left">
        
  <div style="line-height: 140%; text-align: left; word-wrap: break-word;">
    <p style="font-size: 14px;line-height: 140%;margin: 0;">Have questions or feedback? You can contact us anytime at <span style="color: #00B7EC; font-size: 14px; line-height: 19.6px;"><a style="color: #00B7EC;line-height: inherit;text-decoration: underline;" href="mailto:info@genotypematch.com" target="_blank" rel="noopener">info@genotypematch.com</a> </span></p>
  </div>

      </td>
    </tr>
  </tbody>
</table>

  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
  </div>
</div>
<!--[if (mso)|(IE)]></td><![endif]-->
      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
    </div>
  </div>
</div>



<div class="u-row-container" style="padding: 0px;background-color: transparent;line-height: inherit;">
  <div class="u-row" style="overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;line-height: inherit;">
    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;line-height: inherit;">
      <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="left"><table cellpadding="0" cellspacing="0" border="0"><tr style="background-color: transparent;"><![endif]-->
      
<!--[if (mso)|(IE)]><td align="left" style="background-color: #ffffff;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;" valign="top"><![endif]-->
<div class="u-col u-col-100" style="display: table-cell;vertical-align: top;line-height: inherit;">
  <div style="background-color: #ffffff;width: 100% !important;line-height: inherit;">
  <!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;line-height: inherit;"><!--<![endif]-->
  
<table style="font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
  <tbody style="line-height: inherit;">
    <tr style="line-height: inherit;vertical-align: top;border-collapse: collapse;">
      <td style="overflow-wrap: break-word;word-break: break-word;padding: 10px;font-family: arial,helvetica,sans-serif;line-height: inherit;color: #000000;vertical-align: top;border-collapse: collapse;" align="left">
        
  <table height="0px" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;line-height: inherit;color: #000000;">
    <tbody style="line-height: inherit;">
      <tr style="vertical-align: top;line-height: inherit;border-collapse: collapse;">
        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #000000;">
          <span style="line-height: inherit;">&#160;</span>
        </td>
      </tr>
    </tbody>
  </table>

      </td>
    </tr>
  </tbody>
</table>

  <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
  </div>
</div>
<!--[if (mso)|(IE)]></td><![endif]-->
      <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
    </div>
  </div>
</div>


    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
    </td>
  </tr>
  </tbody>
  </table>
  <!--[if mso]></div><![endif]-->
  <!--[if IE]></div><![endif]-->';
        
    }
    
    
}

