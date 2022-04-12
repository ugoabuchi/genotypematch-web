<?php

require_once (dirname(  __FILE__).'/composer/vendor/autoload.php');
require_once ( dirname(  __FILE__)."/logwriter.php" );
require_once ( dirname(  __FILE__)."/database.php" );


$mypool = Spatie\Async\Pool::create();
$logger = new logwriter();
$logid = "genotypematch-web-log.txt";

$db = new database("gm");


//perform cron for expo push notification
$notificationQuery = $db->execute_return("SELECT `id`, `userdbid`, `matchdbid`, `type`, `response`, `timestamp` FROM `notifications` WHERE sent='false'");
//proceed only if a notification exist
if(is_array($notificationQuery) && count($notificationQuery) > 0)
{
    foreach($notificationQuery as $notification){
        
        $notificationID = $notification['id'];
        $userdbid = $notification['userdbid'];
        $matchdbid = $notification['matchdbid'];
        $notificationType = (int)$notification['type'];
        $response = $notification['response'];
        $timeStamp = $notification['timestamp'];
        
        //Check if "1 -> Like Notification"
        if($notificationType == 1)
        {
            //get userdbid user name
            $userdbidname = $db->execute_return("SELECT name FROM `users` WHERE id='$userdbid'")[0]['name'];
            $matchdbidDetails = $db->execute_return("SELECT PNID, email FROM `users` WHERE id='$matchdbid'")[0];
            
            $data['userdbid'] = $userdbid;
            $data['response'] = $response;
            $data['timeStamp'] = $timeStamp;
            $data['sendersName'] = $userdbidname;
            $data["GNC"] = $db->execute_count_return("SELECT COUNT(*) FROM `notifications` WHERE type != '2' AND type != '6' AND seen='false' AND matchdbid='$matchdbid'");
            $data = json_encode($data, 1);
            
            //lets make channel names which are specific to particular users for specific notification purposes using their email
            $channelName = $matchdbidDetails['email']."-1";
            $recipient = $matchdbidDetails['PNID'];
            $notificationPayLoad = array(
            'title' => 'GenotypeMatch',
            'body' => $userdbidname.' - Yupped',
            'data' => $data
            );
            
            //$db->execute_no_return("UPDATE `notifications` SET sent='true' WHERE id='$notificationID'");
            //calling aync operation global variable
           $mypool->
                add(function () use($logger, $logid, $channelName, $notificationPayLoad, $notificationID) {
                    $expo = \ExponentPhpSDK\Expo::normalSetup();
                    $expo->notify([$channelName], $notificationPayLoad);
                    return "success";
                })
                ->then(function ($output) use($logger, $logid, $channelName, $notificationPayLoad, $notificationID) {
                    // On success, `$output` is returned by the process or callable you passed to the queue.
                    if($output != "success")
                        {
                            $expo = \ExponentPhpSDK\Expo::normalSetup();
                            $expo->notify([$channelName], $notificationPayLoad);
                            
                        }
                })
                ->catch(function ($exception) use($logger, $logid, $channelName, $notificationPayLoad, $notificationID) {
                    // When an exception is thrown from within a process, it's caught and passed here.
                    $expo = \ExponentPhpSDK\Expo::normalSetup();
                    $expo->notify([$channelName], $notificationPayLoad);
                    
                })
                ->timeout(function () use($logger, $logid, $channelName, $notificationPayLoad, $notificationID) {
                    // A process took too long to finish.
                    $expo = \ExponentPhpSDK\Expo::normalSetup();
                    $expo->notify([$channelName], $notificationPayLoad);
                    
                });

            
        }
    }
}


//Perform cron for security alerts to mail

/*
 //code for sending mails
 $this->pool->add(function () use ($email, $name) {
                                $memail = explode("@", $email)[0];
                                $this->sendmail->send_mail_no_reply(
                                $email, 
                                $memail, 
                                "Verification Code from Genotype-Match - ".date("Y-m-d, H-i-s"), 
                                $this->sendmail->emailFormatOne(
                                $this->sendmail->emailVerificationStyle($name, $this->getEmailVericationcode($email))
                                        ),
                                "verify@Genotype-Match - ".date("Y-m-d, H-i-s"));
                            });
        $this->pool->wait();
 */

//$logger->log(dirname(  __FILE__)."/../../logs/".$logid, "Notification successfully sent");


