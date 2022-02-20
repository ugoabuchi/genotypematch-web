<?php

require_once (dirname(  __FILE__).'/composer/vendor/autoload.php');
require_once ( dirname(  __FILE__)."/logwriter.php" );
require_once ( dirname(  __FILE__)."/database.php" );

$logger = new logwriter();
$logid = "genotypematch-web-log.txt";

$db = new database("gm");

//perform cron for expo push notification
$notificationQuery = $db->execute_return("SELECT `id`, `userdbid`, `matchdbid`, `type`, `response`, `timestamp` FROM `notifications` WHERE sent='false' AND seen='false'");
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
            $data = json_encode($data, 1);
            
            //lets make channel names which are specific to particular users for specific notification purposes using their email
            $channelName = $matchdbidDetails['email'];
            $recipient = $matchdbidDetails['PNID'];
            $notificationPayLoad = array(
            'title' => 'GenotypeMatch',
            'body' => $userdbidname.' Just Yupped You',
            'data' => $data
            );
            
            //boot up an expo SDK instance
            $expo = \ExponentPhpSDK\Expo::normalSetup();
            $expo->subscribe($channelName, $recipient);
            $expo->notify([$channelName], $notificationPayLoad);
            
        }
    }
}





//$logger->log(dirname(  __FILE__)."/../../logs/".$logid, "Notification successfully sent");


