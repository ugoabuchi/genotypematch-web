<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
use Spatie\Async\Pool;
require 'composer/vendor/autoload.php';
class session
{
    private $sessiondb = null;
    private $token = null;
    private $codegen = null;
    private $defaults = null;
    private $vcode = null;
    private $sendmail = null;
    private $response = null;
    private $pool = null;
    function __construct()
    {
        $this->sessiondb = new database("gm");
        $this->token = new tokengenerator();
        $this->codegen = new codegenerator(10);
        $this->defaults = new defaults();
        $this->vcode = new codegenerator(6);
        $this->sendmail = new mailmanager("genotypematch.com", 465, "devop@genotypematch.com", "blark2018@");
        $this->response = [];
        $this->pool = Pool::create();
    }
    
    
    public function registeruser($userid, $email, $name, $gender, $married, $interestedin, $phone, $bloodgroup, $password, $description, $dob)
    {
         //check if users table exist
                if($this->sessiondb->execute_count_table_no_return("users") == 0)
                {   
                    $tablequery = "
                    CREATE TABLE `users` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `username` VARCHAR(160) NOT NULL ,
                        `email` VARCHAR(160) NOT NULL ,
                        `name` VARCHAR(160) NOT NULL ,
                        `gender` VARCHAR(160) NOT NULL ,
                        `married` VARCHAR(160) NOT NULL ,
                        `interestedin` VARCHAR(160) NOT NULL ,
                        `phone` VARCHAR(160) NOT NULL ,
                        `blooggroup` VARCHAR(160) NOT NULL ,
                        `lastseencountry` VARCHAR(160) NOT NULL ,
                        `lastseencity` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `accounttype` VARCHAR(160) NOT NULL ,
                        `gc` VARCHAR(160) NOT NULL ,
                        `verified` VARCHAR(160) NOT NULL ,
                        `enabled` VARCHAR(160) NOT NULL ,
                        `ltimein` VARCHAR(160) NOT NULL ,
                        `description` TEXT NOT NULL,
                        `dob` DATE NOT NULL,
                        `token` TEXT NOT NULL,
                        `pverified` VARCHAR(160) NOT NULL ,
                        `bverified` VARCHAR(160) NOT NULL ,
                        `passkey` VARCHAR(160) NOT NULL,
                        `PNID` VARCHAR(160) NOT NULL,
                        `salt` VARCHAR(160) NOT NULL,
                        `created` VARCHAR(160) NOT NULL,
                        PRIMARY KEY (`id`), 
                        UNIQUE (`username`, `email`, `phone`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
 
          
            //check if user exist in users table
            $useridsql = "SELECT COUNT(*) FROM `users` WHERE username='$userid'";
            $emailcheck = "SELECT COUNT(*) FROM `users` WHERE email='$email'";
            $phonecheck = "SELECT COUNT(*) FROM `users` WHERE phone='$phone' AND pverified = 'true'";
            
            if($this->validateAge($dob, 18, 0) == false || $this->validateAge($dob, 74, 1) == false)
            {
                //User does not exist
                $this->response["response"] = "error";
                $this->response["message"] = "You must be between the age of 18 - 74 to use Genotype Match";
            }
            else if($this->sessiondb->execute_count_no_return($useridsql) == 1)
            {
                //User does not exist
                $this->response["response"] = "error";
                $this->response["message"] = "User with placed Username already exist ";
            }
            else if($this->sessiondb->execute_count_no_return($emailcheck) == 1)
            {
                //User does not exist
                $this->response["response"] = "error";
                $this->response["message"] = "User with placed Email already exist ";
            }
            else if($this->sessiondb->execute_count_no_return($phonecheck) == 1)
            {
                //User does not exist
                $this->response["response"] = "error";
                $this->response["message"] = "User with placed Phone number already validated and exist ";
            }
            else
            {
                    //get new users related default settings
                    $newuserdefaultgc = $this->sessiondb->execute_return("SELECT `value` FROM `generalstatus` WHERE `identifier` = 'newusergc'")[0]['value'];
                    //generate password salt
                    $salt = $this->codegen->getCode();
                    $accounttype = $this->defaults->getNormal();
                    //register new user
                    $generatedPasskey = md5($password)."-blark-".md5((md5("-blark-").$salt)).md5($password);
                    $sqs = "INSERT INTO `users`(`username`, `email`, `name`, `gender`,`married`, `interestedin`, `phone`, `blooggroup`, `lastseencountry`, `lastseencity`, `lastseencoords`, `accounttype`, `gc`, `verified`, `enabled`, `ltimein`, `description`,`dob`, `token`,`pverified`, `bverified`, `passkey`, `PNID`, `salt`, `created`) VALUES ('$userid', '$email', '$name', '$gender','$married', '$interestedin', '$phone', '$bloodgroup', '', '', '', '$accounttype', '$newuserdefaultgc', 'true', 'true', '', '$description', '$dob', '', 'true', 'false', '$generatedPasskey', '',  '$salt', now())";
                    $this->sessiondb->execute_no_return($sqs);
                    $this->response["response"] = "success";
                    $this->response["message"] = "Your registration was successful";
            }
            return json_encode($this->response, 1);

    }
    
    
    public function sendEamilVerificationMail($email, $name)
    {
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
    }
    
    public function checkUsernameExist($userid)
    {
         //check if users table exist
                if($this->sessiondb->execute_count_table_no_return("users") == 0)
                {   
                    $tablequery = "
                    CREATE TABLE `users` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `username` VARCHAR(160) NOT NULL ,
                        `email` VARCHAR(160) NOT NULL ,
                        `name` VARCHAR(160) NOT NULL ,
                        `gender` VARCHAR(160) NOT NULL ,
                        `married` VARCHAR(160) NOT NULL ,
                        `interestedin` VARCHAR(160) NOT NULL ,
                        `phone` VARCHAR(160) NOT NULL ,
                        `blooggroup` VARCHAR(160) NOT NULL ,
                        `lastseencountry` VARCHAR(160) NOT NULL ,
                        `lastseencity` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `accounttype` VARCHAR(160) NOT NULL ,
                        `gc` VARCHAR(160) NOT NULL ,
                        `verified` VARCHAR(160) NOT NULL ,
                        `enabled` VARCHAR(160) NOT NULL ,
                        `ltimein` VARCHAR(160) NOT NULL ,
                        `description` TEXT NOT NULL,
                        `dob` DATE NOT NULL,
                        `token` TEXT NOT NULL,
                        `pverified` VARCHAR(160) NOT NULL ,
                        `bverified` VARCHAR(160) NOT NULL ,
                        `passkey` VARCHAR(160) NOT NULL,
                        `PNID` VARCHAR(160) NOT NULL,
                        `salt` VARCHAR(160) NOT NULL,
                        `created` VARCHAR(160) NOT NULL,
                        PRIMARY KEY (`id`), 
                        UNIQUE (`username`, `email`, `phone`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
 
          
            //check if user exist in users table
            $useridsql = "SELECT COUNT(*) FROM `users` WHERE username='$userid'";
            
            if($this->sessiondb->execute_count_no_return($useridsql) == 1)
            {
                //User does exist
                $this->response["response"] = "username-exist-true";
                $this->response["message"] = "Username exist";
            }
            else
            {
                    $this->response["response"] = "username-exist-false";
                    $this->response["message"] = "Username does not exist";
            }
            
            return json_encode($this->response, 1);

    }


    public function setusersession($userid, $password, $PNID)
    {
         //check if users table exist
                if($this->sessiondb->execute_count_table_no_return("users") == 0)
                {   
                    $tablequery = "
                    CREATE TABLE `users` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `username` VARCHAR(160) NOT NULL ,
                        `email` VARCHAR(160) NOT NULL ,
                        `name` VARCHAR(160) NOT NULL ,
                        `gender` VARCHAR(160) NOT NULL ,
                        `married` VARCHAR(160) NOT NULL ,
                        `interestedin` VARCHAR(160) NOT NULL ,
                        `phone` VARCHAR(160) NOT NULL ,
                        `blooggroup` VARCHAR(160) NOT NULL ,
                        `lastseencountry` VARCHAR(160) NOT NULL ,
                        `lastseencity` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `accounttype` VARCHAR(160) NOT NULL ,
                        `gc` VARCHAR(160) NOT NULL ,
                        `verified` VARCHAR(160) NOT NULL ,
                        `enabled` VARCHAR(160) NOT NULL ,
                        `ltimein` VARCHAR(160) NOT NULL ,
                        `description` TEXT NOT NULL,
                        `dob` DATE NOT NULL,
                        `token` TEXT NOT NULL,
                        `pverified` VARCHAR(160) NOT NULL ,
                        `bverified` VARCHAR(160) NOT NULL ,
                        `passkey` VARCHAR(160) NOT NULL,
                        `PNID` VARCHAR(160) NOT NULL,
                        `salt` VARCHAR(160) NOT NULL,
                        `created` VARCHAR(160) NOT NULL,
                        PRIMARY KEY (`id`), 
                        UNIQUE (`username`, `email`, `phone`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }

            //check if usersession table exist
                if($this->sessiondb->execute_count_table_no_return("usersession") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `usersession` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL , 
                        `taction` VARCHAR(160) NOT NULL , 
                        `token` TEXT NOT NULL ,
                        `created` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `validity` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
                
                 if($this->sessiondb->execute_count_table_no_return("gallery") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `gallery` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL ,
                        `title` VARCHAR(160) NOT NULL ,
                        `type` VARCHAR(160) NOT NULL ,
                        `ext` TEXT NOT NULL ,
                        `isprofilepicture` VARCHAR(160) NOT NULL ,
                        `uploaded` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`),
                        UNIQUE (`title`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
 
            //check if user exist in users table
            $sql = "SELECT `id`, `username`, `email`, `name`, `gender`, `married`, `interestedin`, `phone`, `blooggroup`, `description`,`dob`,`token`, `accounttype`, `gc`, `verified`, `enabled`, `ltimein`, `pverified`, `bverified`, `passkey`, `salt`  FROM `users` WHERE username='$userid'";
            if(count($this->sessiondb->execute_return($sql)) < 1)
            {
                //User does not exist
                $this->response["response"] = "username-exist-false";
                $this->response["message"] = "Username does not exist";
            }
            else
            {
               $dbuserid = $this->sessiondb->execute_return($sql)[0]['id'];
               $email = $this->sessiondb->execute_return($sql)[0]['email'];
               $verified = $this->sessiondb->execute_return($sql)[0]['verified'];
               $enabled = $this->sessiondb->execute_return($sql)[0]['enabled'];
               $accounttype = $this->sessiondb->execute_return($sql)[0]['accounttype'];
               
               if($verified == "false")
               {
                    $this->response["response"] = "error-account-unverified";
                    $this->response["message"] = "Account unverified";
               }
               else if($enabled == "false"){
                    $this->response["response"] = "error-account-disabled";
                    $this->response["message"] = "Account disabled";
               }
               else{
                    //check if password hash is correct
                $securedetails = $this->sessiondb->execute_return($sql)[0];
                $dbpasskey = $securedetails['passkey'];
                $dbsalt = $securedetails['salt'];
                
                $generatedPasskey = md5($password)."-blark-".md5((md5("-blark-").$dbsalt)).md5($password);
                if($dbpasskey == $generatedPasskey)
                {
                    $this->deleteLoginSession($dbuserid);
                    $tcode = $this->token->getToken();
                     //update login token and ltimein
                    $upsql = "UPDATE `users` SET `token`='$tcode', `lastseencoords`='', `ltimein`=now(), `PNID`='$PNID'  WHERE email='$email'";
                    $this->sessiondb->execute_no_return($upsql);
                    $this->updateusersessionaction($dbuserid, $tcode, $this->defaults->getActionType()[0], '');
                    
                    $dbvalues = $this->sessiondb->execute_return($sql)[0];
                    $picturesql = "SELECT title, ext FROM gallery WHERE userid='$dbuserid' AND type='Image' AND isprofilepicture='true'";
                    $profileimage = $this->sessiondb->execute_return($picturesql);
                    if(is_array($profileimage) && count($profileimage) > 0)
                    {
                        $dbvalues['picture'] = $profileimage[0]['title'].".".$profileimage[0]['ext'];
                    }
                    else
                    {
                        $dbvalues['picture'] = "user.png";
                    }
                    $allpicturessql = "SELECT title, ext FROM gallery WHERE userid='$dbuserid' AND type='Image'";
                    $allpictures = $this->sessiondb->execute_return($allpicturessql);
                    if(count($allpictures) > 0)
                    {
                        foreach ($allpictures as $apictute => $value){
                            $dbvalues['gallery'][$apictute] = $value['title'].".".$value['ext'];
                        }
                    }
                    else
                    {
                        $dbvalues['gallery'] = array();
                    }
                    unset($dbvalues['salt']);
                    unset($dbvalues['id']);
                    unset($dbvalues['passkey']);
                    $this->response["response"] = true;
                    $this->response["message"] = "Signed in";
                    $this->response['data'] = $dbvalues;
                   
                    
                }
                else{
                    $this->response["response"] = "error-passkey-false";
                    $this->response["message"] = "Incorrect passkey";
                }
                
               }
              
                
            }
            
            return json_encode($this->response, 1);

    }
    
   

    public function verifyTokenValidity($username, $token)
    {
       //verify $token exist with user is valid
        $vsql = "SELECT id FROM users WHERE username='$username' AND token='$token'";
        if(count($this->sessiondb->execute_return($vsql)) > 0)
        {
            $dbuserid = $this->sessiondb->execute_return($vsql)[0]['id'];
            //Token is current in user profile, check if it is current is user session
            
            $sessionql = "SELECT created FROM usersession WHERE userid='$dbuserid' AND token='$token' AND validity='true'";
            $sessiondata = $this->sessiondb->execute_return($sessionql);
            if(count($sessiondata) > 0){
                
                //Token exist to be valid in DB
                //Verify token validity
                $mytimestamp = date_create($sessiondata[0]['created'])->getTimestamp() + $this->defaults->getSessionExpTime();
                if ($mytimestamp > time() ){
                    
                    //Token is verified
                    $this->response["response"] = "success";
                    $this->response["message"] = "Your session is still valid";
                    $this->response['userid'] = $username;
                    $this->response['token'] = $token;
                    
                }
                else
                {
                    //Token is expired, update login session
                    $this->deleteLoginSession($dbuserid);
                    $this->response["response"] = "error";
                    $this->response["message"] = "Token Expired, Sign in again to continue your session";
                    
                }
                
                
            }
            else
            {
                //Token not valid in DB
                    $this->response["response"] = "error";
                    $this->response["message"] = "Invalid token, action dismissed ";
                
            }   
            
        }
        else
        {
            //Token is not current in user profile
                    $this->response["response"] = "error";
                    $this->response["message"] = "Invalid token credentials ";
            
        }
        
        return json_encode($this->response, 1);
               
    }
    
    
    
       public function checkEmailNotVerified($username)
    {
                 if($this->sessiondb->execute_count_table_no_return("users") == 0)
                {   
                    $tablequery = "
                    CREATE TABLE `users` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `username` VARCHAR(160) NOT NULL ,
                        `email` VARCHAR(160) NOT NULL ,
                        `name` VARCHAR(160) NOT NULL ,
                        `gender` VARCHAR(160) NOT NULL ,
                        `married` VARCHAR(160) NOT NULL ,
                        `interestedin` VARCHAR(160) NOT NULL ,
                        `phone` VARCHAR(160) NOT NULL ,
                        `blooggroup` VARCHAR(160) NOT NULL ,
                        `lastseencountry` VARCHAR(160) NOT NULL ,
                        `lastseencity` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `accounttype` VARCHAR(160) NOT NULL ,
                        `gc` VARCHAR(160) NOT NULL ,
                        `verified` VARCHAR(160) NOT NULL ,
                        `enabled` VARCHAR(160) NOT NULL ,
                        `ltimein` VARCHAR(160) NOT NULL ,
                        `description` TEXT NOT NULL,
                        `dob` DATE NOT NULL,
                        `token` TEXT NOT NULL,
                        `pverified` VARCHAR(160) NOT NULL ,
                        `bverified` VARCHAR(160) NOT NULL ,
                        `passkey` VARCHAR(160) NOT NULL,
                        `PNID` VARCHAR(160) NOT NULL,
                        `salt` VARCHAR(160) NOT NULL,
                        `created` VARCHAR(160) NOT NULL,
                        PRIMARY KEY (`id`), 
                        UNIQUE (`username`, `email`, `phone`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }   
                
            $sql = "SELECT email, id, verified FROM `users` WHERE username='$username'";
            if(count($this->sessiondb->execute_return($sql)) > 0)
            {
                if($this->sessiondb->execute_return($sql)[0]["verified"] == "false"){
                    //check if code exist
                    $email = $this->sessiondb->execute_return($sql)[0]["email"];
                    $this->sendEamilVerificationMail($email, $email);   
                    $this->response["response"] = "success";
                    $this->response["data"] = $email;
                }
                else
                {
                    $this->response["response"] = "error";
                    $this->response["message"] = "User with placed ID has already been verified, try logging in or resetting your password.";
                }
            }
            else
            {
                    $this->response["response"] = "error";
                    $this->response["message"] = "User with placed ID does not exist";
            }
            
            return json_encode($this->response, 1);
        
    }
    
    
    
    
    
    public function verifyaccount($username, $code)
    {
                if($this->sessiondb->execute_count_table_no_return("codes") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `codes` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL , 
                        `action` VARCHAR(160) NOT NULL , 
                        `code` VARCHAR(160) NOT NULL ,
                        `created` VARCHAR(160) NOT NULL ,
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }   
                
            $sql = "SELECT email, id, verified FROM `users` WHERE username='$username'";
            if(count($this->sessiondb->execute_return($sql)) > 0)
            {
                if($this->sessiondb->execute_return($sql)[0]["verified"] == "false"){
                    $actiontype = $this->defaults->getVerificationCodeName();
                    $dbuserid = $this->sessiondb->execute_return($sql)[0]["id"];
                    //check if code exist
                        $email = $this->sessiondb->execute_return($sql)[0]["email"];
                        $check  = "SELECT * FROM codes WHERE userid='$dbuserid' AND action='$actiontype' AND code = '$code'";
                        if(count($this->sessiondb->execute_return($check)) > 0)
                        {
                            //check if code is valid
                            $codedata = $this->sessiondb->execute_return($check)[0];
                            $mytimestamp = date_create($codedata['created'])->getTimestamp() + $this->defaults->getVerificationExpTime();
                            
                            if($mytimestamp > time()){
                             //verify user
                             $verify = "UPDATE users SET verified='true' WHERE email='$email'";
                             $this->sessiondb->execute_no_return($verify);
                             //clear verification code
                             $usql = "DELETE FROM codes WHERE userid='$dbuserid' AND action='$actiontype'";
                             $this->sessiondb->execute_no_return($usql);
                             $this->response["response"] = "success";
                            }
                            else
                            {
                                $this->response["response"] = "error";
                                $this->response["message"] = "Verification code is expired, kindly request a new one";
                            }
                        }
                        else
                        {
                            $this->response["response"] = "error";
                            $this->response["message"] = "Invalid verification code";
                        }
                           
                    
                }
                else
                {
                    $this->response["response"] = "error";
                    $this->response["message"] = "User with placed ID has already been verified, try logging in";
                }
            }
            else
            {
                    $this->response["response"] = "error";
                    $this->response["message"] = "User with placed ID does not exist";
            }
            
            return json_encode($this->response, 1);
        
    }
    
    
          public function setPasscode($userid, $oldpassword, $newpassword){
       
       $details = $this->sessiondb->execute_return("SELECT passkey, salt, id FROM users WHERE username='$userid'")[0];
       $dbuserid = $details['id'];
       $dbpasskey = $details['passkey'];
       $dbsalt = $details['salt'];
       
       //verify old passkey
       $oldPasskey = md5($oldpassword)."-blark-".md5((md5("-blark-").$dbsalt)).md5($oldpassword);
       if($oldPasskey == $dbpasskey)
       {
            $salt = $this->vcode->getCode();
            $newpasskey = md5($newpassword)."-blark-".md5((md5("-blark-").$salt)).md5($newpassword);
            
            $this->sessiondb->execute_no_return("UPDATE users SET passkey='$newpasskey', salt='$salt' WHERE username='$userid'");
            $this->response["response"] = "success";
       }
       else
       {
            $this->response["response"] = "error";
            $this->response["message"] = "Incorrect account configuration, please browse the website properly";
       }
       
       
       return json_encode($this->response, 1);
   }
    
        public function recoverpassword($username, $code, $newpassword)
    {
                if($this->sessiondb->execute_count_table_no_return("codes") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `codes` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL , 
                        `action` VARCHAR(160) NOT NULL , 
                        `code` VARCHAR(160) NOT NULL ,
                        `created` VARCHAR(160) NOT NULL ,
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }   
                
            $sql = "SELECT email, id FROM `users` WHERE username='$username'";
            if(count($this->sessiondb->execute_return($sql)) > 0)
            {
                    $actiontype = $this->defaults->getVerificationCodeName();
                    $dbuserid = $this->sessiondb->execute_return($sql)[0]["id"];
                    //check if code exist
                        $email = $this->sessiondb->execute_return($sql)[0]["email"];
                        $check  = "SELECT * FROM codes WHERE userid='$dbuserid' AND action='$actiontype' AND code = '$code'";
                        if(count($this->sessiondb->execute_return($check)) > 0)
                        {
                            //check if code is valid
                            $codedata = $this->sessiondb->execute_return($check)[0];
                            $mytimestamp = date_create($codedata['created'])->getTimestamp() + $this->defaults->getVerificationExpTime();
                            
                            if($mytimestamp > time()){
                             $salt = $this->codegen->getCode();
                             $generatedPasskey = md5($newpassword)."-blark-".md5((md5("-blark-").$salt)).md5($newpassword);
                             $verify = "UPDATE users SET passkey='$generatedPasskey', salt='$salt' WHERE email='$email'";
                             $this->sessiondb->execute_no_return($verify);
                             //clear verification code
                             $usql = "DELETE FROM codes WHERE userid='$dbuserid' AND action='$actiontype'";
                             $this->sessiondb->execute_no_return($usql);
                             $this->response["response"] = "success";
                             $this->response["message"] = "Your password was successfully updated, you can now log in";
                            }
                            else
                            {
                                $this->response["response"] = "error";
                                $this->response["message"] = "Recovery code is expired, kindly request a new one";
                            }
                        }
                        else
                        {
                            $this->response["response"] = "error";
                            $this->response["message"] = "Invalid recovery code";
                        }
                           
                    
               
            }
            else
            {
                    $this->response["response"] = "error";
                    $this->response["message"] = "User with placed ID does not exist";
            }
            
            return json_encode($this->response, 1);
        
    }
    
        public function getEmailVericationcode($email)
    {
                if($this->sessiondb->execute_count_table_no_return("codes") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `codes` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL , 
                        `action` VARCHAR(160) NOT NULL , 
                        `code` VARCHAR(160) NOT NULL ,
                        `created` VARCHAR(160) NOT NULL ,
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
            $sql = "SELECT username, id, verified FROM `users` WHERE email='$email'";
            if(count($this->sessiondb->execute_return($sql)) > 0)
            {
                    $code = null;
                    $actiontype = $this->defaults->getVerificationCodeName();
                    $dbuserid = $this->sessiondb->execute_return($sql)[0]["id"];
                    $usql = "";
                    //check if code exist
                        $check  = "SELECT * FROM codes WHERE userid='$dbuserid' AND action='$actiontype'";
                        $codedata = $this->sessiondb->execute_return($check);
                        if(count($codedata) > 0)
                        {
                            
                            //check if code is expired
                            $mytimestamp = date_create($codedata[0]['created'])->getTimestamp() + $this->defaults->getVerificationExpTime();
                            
                            if($mytimestamp > time()){
                             //cupdate code and create field and send new code
                                $code = $codedata[0]['code'];
                                
                            }
                            else
                            {
                                $code = $this->vcode->getCode();
                                $usql = "UPDATE codes SET code = '$code', created=now() WHERE userid = '$dbuserid'";
                            }
                            
                            
                        }
                        else
                        {
                            $code = $this->vcode->getCode();
                            //inset new verification base
                            $usql = "INSERT INTO codes(userid, action, code, created) VALUES('$dbuserid', '$actiontype', '$code', now())";
                            
                        }
                        
                        if($usql != "")
                        {
                            $this->sessiondb->execute_no_return($usql);
                        }
                        
                        
                        return $code;
                           
                    
                }
            
            else
            {
                    return "Oops something went wrong, try again later";
            }
            
    }
    
    public function updateusersessionaction($dbuserid, $token, $action, $lastseencoords){
        if($this->sessiondb->execute_count_table_no_return("usersession") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `usersession` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL , 
                        `taction` VARCHAR(160) NOT NULL , 
                        `token` TEXT NOT NULL ,
                        `created` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `validity` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
        $sqs = "INSERT INTO usersession (userid, taction, token, created, lastseencoords, validity) VALUES('$dbuserid', '$action', '$token', now(), '$lastseencoords', 'true')";
        $this->sessiondb->execute_no_return($sqs); 
    }
    
    
    public function validateAge($birthday, $age = 18, $check=0)
        {
        //$check : 0 - minimun age, 1 for maximun
            // $birthday can be UNIX_TIMESTAMP or just a string-date.
            if(is_string($birthday)) {
                $birthday = strtotime($birthday);
            }

            // check
            // 31536000 is the number of seconds in a 365 days year.
            if($check == 0)
            {
                if(time() - $birthday < $age * 31536000)  {
                return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                if(time() - $birthday > $age * 31536000)  {
                return false;
                }
                else
                {
                    return true;
                }
            }

            
        }

    public function deleteLoginSession($dbuserid)
    {
        if($this->sessiondb->execute_count_table_no_return("usersession") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `usersession` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userid` VARCHAR(160) NOT NULL , 
                        `taction` VARCHAR(160) NOT NULL , 
                        `token` TEXT NOT NULL ,
                        `created` VARCHAR(160) NOT NULL ,
                        `lastseencoords` VARCHAR(160) NOT NULL ,
                        `validity` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
        //delete login session
        if($dbuserid != null && $dbuserid != "")
        {
            $sql = "UPDATE `usersession` set validity = 'false' WHERE userid='$dbuserid' AND validity ='true'";
            $this->sessiondb->execute_no_return($sql);
        }

    }

    
}