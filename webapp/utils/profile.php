<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */

class profile
{
    private $sessiondb = null;
    private $response = null;
    private $defaults = null;
    private $token = null;
    private $vcode = null;
    private $currentsession = null;
    private $requestlocation = null;
    function __construct()
    {
        $this->sessiondb = new database("gm");
        $this->defaults = new defaults();
        $this->token = new tokengenerator();
        $this->vcode = new codegenerator(5);
        $this->request = new defaults();
        $this->requestlocation = new requestlocation();
        $this->response = [];

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
                        `locationcode` VARCHAR(160) NOT NULL ,
                        `validity` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
        $sqs = "INSERT INTO usersession (userid, taction, token, created, lastseencoords, validity) VALUES('$dbuserid', '$action', '$token', now(), '$lastseencoords', 'true')";
        $this->sessiondb->execute_no_return($sqs); 
    }
    
    public function addtogallery($userid, $token, $images){
        //check if gallery table exist
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
           
               $dbuserid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid' OR email='$userid'")[0]['id'];
               /*
                Must be of the following ext
                Must be of the following size
                Must be of portrait
                */
               $imageerror = 0;
               $ext = array("image/png", "image/jpeg", "image/jpg");
               $sizebytes = $this->defaults->getImageUploadSize(); //in bytes
               
                       $cext = $images['type'];
                     $csizebytes = $images['size'];
                    if(in_array($cext, $ext))
                    {
                        if((int)$csizebytes <= $sizebytes)
                        {
                           
                                //upload images
                                $dext = strtoupper(explode(".", $images['name'])[count(explode(".", $images['name'])) - 1]);
                                $title = $this->generateimagetitle();
                                move_uploaded_file($images['tmp_name'], $this->defaults->getUploadSubURL()."File_Uploads/Gallery/Images/".$title.".".$dext);
                                $sql = "INSERT INTO gallery(userid, title, type, ext, isprofilepicture, uploaded) VALUES('$dbuserid', '$title', 'Image', '$dext', 'false', now())";
                                $this->sessiondb->execute_no_return($sql);
                                
                           
                        }
                        else
                        {
                            $imageerror = 2;
                        }
                    }
                    else
                    {
                        $imageerror = 1;
                    }
                    
                   
                   
                
                $this->response['tokenisvalid'] = "true";
                
                if($imageerror == 0)
                {
                     //check if profile image exist
                    $cprodile = "SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image' AND isprofilepicture='true'";
                    if($this->sessiondb->execute_count_no_return($cprodile) == 0)
                    {
                        //make first upload profile picture
                        $profilepicture = "UPDATE gallery SET isprofilepicture='true' WHERE userid='$dbuserid' AND id = (SELECT id FROM gallery WHERE userid='$dbuserid' AND type='Image' ORDER BY id ASC LIMIT 1)";
                        $this->sessiondb->execute_no_return($profilepicture);
              
                    }
                    
                    $this->response["response"] = "success";
                    
                        $this->response["message"] = "Your upload was successful";
                    
                    $this->currentsession->updateusersessionaction($dbuserid, $token, "1");
                    $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
                    
                }
                else if($imageerror == 1)
                {
                    $this->response["response"] = "error";
                    $this->response["message"] = "Invaid image file extension";
                    $this->response['tokenisvalid'] = "true";
                }
                else
                {
                    $this->response["response"] = "error";
                    $this->response["message"] = "Image file is larger than 2000000 bytes";
                    $this->response['tokenisvalid'] = "true";
                }
                
               
          
           
           //update usersession and action
           
           return json_encode($this->response, 1);
        
    }
    
    
    public function deletefromgallery($userid, $token, $image){
        //check if gallery table exist
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
                
          
               $dbuserid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid' OR email='$userid'")[0]['id'];
               
               
                   $title = explode(".", $image)[0];
                   $ext = explode(".", $image)[1];
                   if($this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM gallery WHERE title='$title'") == 1)
                   {
                       unlink($this->defaults->getUploadSubURL()."File_Uploads/Gallery/Images/".$title.".".$ext);
                       $this->sessiondb->execute_no_return("DELETE FROM gallery WHERE title = '$title'");
                   }
               
               if($this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image'") > 0)
               {
                   $cprodile = "SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image' AND isprofilepicture='true'";
                    if($this->sessiondb->execute_count_no_return($cprodile) == 0)
                    {
                        //make first upload profile picture
                        $profilepicture = "UPDATE gallery SET isprofilepicture='true' WHERE userid='$dbuserid' AND id = (SELECT id FROM gallery WHERE userid='$dbuserid' AND type='Image' ORDER BY id ASC LIMIT 1)";
                        $this->sessiondb->execute_no_return($profilepicture);
              
                    }
               }
                
               $this->response["response"] = "success";
               $this->response["message"] = "Your profile was successfully updated";
               $this->currentsession->updateusersessionaction($dbuserid, $token, "2");
               $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
               $this->response['tokenisvalid'] = "true";
          
           
           //update usersession and action
           
           return json_encode($this->response, 1);
        
    }
    
    
    public function removeAsMainfromgallery($userid, $token, $image){
        //check if gallery table exist
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
                
               
           
               $dbuserid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid' OR email='$userid'")[0]['id'];
              
               
               if($this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image' AND title='$image'") > 0)
               {
                   $cprodile = "SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image' AND title='$image' AND isprofilepicture='true'";
                    if($this->sessiondb->execute_count_no_return($cprodile) == 1)
                    {
                        //make first upload profile picture
                        $profilepicture = "UPDATE gallery SET isprofilepicture='false' WHERE userid='$dbuserid' AND title='$image'";
                        $this->sessiondb->execute_no_return($profilepicture);
              
                    }
               }
                
               $this->response["response"] = "success";
               $this->response["message"] = "Your profile was successfully updated";
               $this->currentsession->updateusersessionaction($dbuserid, $token, "2");
               $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
               $this->response['tokenisvalid'] = "true";
               
           
           
           //update usersession and action
           
           return json_encode($this->response, 1);
        
    }
    
        
    public function setAsMainfromgallery($userid, $token, $image){
        //check if gallery table exist
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
                
               
           
               $dbuserid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid' OR email='$userid'")[0]['id'];
              
               
               if($this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image' AND title='$image'") > 0)
               {
                   $cprodile = "SELECT COUNT(*) FROM gallery WHERE userid='$dbuserid' AND type='Image' AND title='$image' AND isprofilepicture='false'";
                    if($this->sessiondb->execute_count_no_return($cprodile) == 1)
                    {
                        //make first upload profile picture
                        $profilepicture1 = "UPDATE gallery SET isprofilepicture='false' WHERE userid='$dbuserid' AND type='Image'";
                        $this->sessiondb->execute_no_return($profilepicture1);
                        $profilepicture = "UPDATE gallery SET isprofilepicture='true' WHERE userid='$dbuserid' AND title='$image'";
                        $this->sessiondb->execute_no_return($profilepicture);
              
                    }
               }
                
               $this->response["response"] = "success";
               $this->response["message"] = "Your profile was successfully updated";
               $this->currentsession->updateusersessionaction($dbuserid, $token, "2");
               $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
               $this->response['tokenisvalid'] = "true";
               
           
           
           //update usersession and action
           
           return json_encode($this->response, 1);
        
    }
 
    
    
    public function generateimagetitle(){
        
        $isValid = false;
        $title = "";
        while($isValid == false)
        {
            $title = $this->token->getFileToken();
            $sql = "SELECT COUNT(*) FROM gallery WHERE title='$title'";
            if($this->sessiondb->execute_count_no_return($sql) == 0)
            {
                $isValid = true;
            }
            else
            {
                continue;
            }
                
        }
        
        return $title;
        
    }
   
    
   public function updateName($userid, $token, $firstname, $lastname){
       
       $this->sessiondb->execute_no_return("UPDATE users SET firstname='$firstname', lastname='$lastname' WHERE username='$userid' OR email='$userid'");
       $this->response["response"] = "success";
       $this->response["message"] = "Your profile was successfully updated";
       $dbdata = $this->sessiondb->execute_return("SELECT `id` FROM users WHERE username='$userid' OR email='$userid'")[0];
       $this->currentsession->updateusersessionaction($dbdata['id'], $token, "3");
       $this->response['tokenisvalid'] = "true";
       $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
       return json_encode($this->response, 1);
   } 
   
      public function updateMarried($userid, $token, $value){
       
       $this->sessiondb->execute_no_return("UPDATE users SET married='$value' WHERE username='$userid' OR email='$userid'");
       $this->response["response"] = "success";
       $this->response["message"] = "Your profile was successfully updated";
       $dbdata = $this->sessiondb->execute_return("SELECT `id` FROM users WHERE username='$userid' OR email='$userid'")[0];
       $this->currentsession->updateusersessionaction($dbdata['id'], $token, "3");
       $this->response['tokenisvalid'] = "true";
       $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
       return json_encode($this->response, 1);
   }
   
   
      
   
       public function updateInterestedIn($userid, $token, $value){
       
       $this->sessiondb->execute_no_return("UPDATE users SET interestedin='$value' WHERE username='$userid' OR email='$userid'");
       $this->response["response"] = "success";
       $this->response["message"] = "Your profile was successfully updated";
       $dbdata = $this->sessiondb->execute_return("SELECT `id` FROM users WHERE username='$userid' OR email='$userid'")[0];
       $this->currentsession->updateusersessionaction($dbdata['id'], $token, "3");
       $this->response['tokenisvalid'] = "true";
       $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
       return json_encode($this->response, 1);
   }
   
       public function updateDescription($userid, $token, $value){
       
       $this->sessiondb->execute_no_return("UPDATE users SET description='$value' WHERE username='$userid' OR email='$userid'");
       $this->response["response"] = "success";
       $this->response["message"] = "Your profile was successfully updated";
       $dbdata = $this->sessiondb->execute_return("SELECT `id` FROM users WHERE username='$userid' OR email='$userid'")[0];
       $this->currentsession->updateusersessionaction($dbdata['id'], $token, "3");
       $this->response['tokenisvalid'] = "true";
       $this->response['data'] = json_decode($this->getUserDetails($userid), 1);
       return json_encode($this->response, 1);
   }
   
   public function getGallery($userid, $token){
       $dbuserid = $this->sessiondb->execute_return("SELECT `id` FROM users WHERE username='$userid' OR email='$userid'")[0]['id'];
       $this->currentsession->updateusersessionaction($dbuserid, $token, "3");
       
       $images = $this->sessiondb->execute_return("SELECT title, ext FROM gallery WHERE userid='$dbuserid' AND type='Image'");
       if(count($images) > 0)
       {
           $uimages = null;
           foreach ($images as $image => $value) {
               $uimages[$image] = $value['title'].".".$value['ext'];
           }
           $this->response['response'] = "success";
           $this->response['data'] = $uimages;
           $this->response['message'] = "Gallery successfully loaded";
       }
       else
       {
           $this->response['response'] = "error";
           $this->response['message'] = "You have no images yet, use the upload button to get started";
       }
       $this->response['tokenisvalid'] = "true";
       
       return json_encode($this->response, 1);
   }
   
      public function updatePassword($userid, $token, $oldpassword, $newpassword){
       
       $details = $this->sessiondb->execute_return("SELECT passkey, salt, id FROM users WHERE username='$userid' OR email='$userid'")[0];
       $dbuserid = $details['id'];
       $dbpasskey = $details['passkey'];
       $dbsalt = $details['salt'];
       
       //verify old passkey
       $oldPasskey = md5($oldpassword)."-gm-".md5((md5("-gm-").$dbsalt)).md5($oldpassword);
       if($oldPasskey == $dbpasskey)
       {
            $salt = $this->vcode->getCode();
            $newpasskey = md5($newpassword)."-gm-".md5((md5("-gm-").$salt)).md5($newpassword);
            
            $this->sessiondb->execute_no_return("UPDATE users SET passkey='$newpasskey', salt='$salt' WHERE username='$userid' OR email='$userid'");
            $this->response["response"] = "success";
            $this->response["message"] = "Password successfully updated";
            $this->currentsession->deleteLoginSession($dbuserid);
            $this->response['tokenisvalid'] = "false";
       }
       else
       {
            $this->response["response"] = "error";
            $this->response["message"] = "Your previous password is incorrect";
            $this->response['tokenisvalid'] = "true";
       }
       
       
       $this->currentsession->updateusersessionaction($dbuserid, $token, "3");
       $this->currentsession->deleteLoginSession($dbuserid);
       
       return json_encode($this->response, 1);
   }
   
   public function getUserDetails($userid){
       $dbdata = $this->sessiondb->execute_return("SELECT `id`, `username`, `email`, `name`, `gender`, `mode`, `married`, `interestedin`, `phone`, `blooggroup`, `description`,`dob`, `locationcode`, `accounttype`, `verified` FROM users WHERE username='$userid' OR email='$userid'")[0];
       $dbuserid = $dbdata['id'];
       $picturesql = "SELECT title, ext FROM gallery WHERE userid='$dbuserid' AND type='Image' AND isprofilepicture='true'";
                    $profileimage = $this->sessiondb->execute_return($picturesql);
                    if(count($profileimage) > 0)
                    {
                        $dbdata['picture'] = $profileimage[0]['title'].".".$profileimage[0]['ext'];
                    }
                    else
                    {
                        $dbdata['picture'] = "user.png";
                    }
        $allpicturessql = "SELECT title, ext FROM gallery WHERE userid='$dbuserid' AND type='Image'";
        $allpictures = $this->sessiondb->execute_return($allpicturessql);
        if(count($allpictures) > 0)
                    {
                        foreach ($allpictures as $apictute => $value){
                            $dbdata['gallery'][$apictute] = $value['title'].".".$value['ext'];
                        }
                    }
                    else
                    {
                        $dbdata['gallery'] = array();
                    }
       unset($dbdata['id']);
       
       return json_encode($dbdata, 1);
   }
   
       public function loadMatches($userid, $reqAccount, $reqCountry, $reqCity, $reqGender, $reqBlooggroup, $reqAgeRange, $userCoords, $userCountry, $userCity, $reqLimit, $reqOffset, $token)
    {
           
           //VIP users are allowed to search by all available methods
           //Premium users are not allowed to search by blooggroups & accounttypes
           ////Normal users are not allowed to search by any methods
        //confirm users mode
       
       
        $udetails = $this->sessiondb->execute_return("SELECT id, username, accounttype, gender, blooggroup, dob FROM users WHERE username='$userid' OR email='$userid'")[0];
        if(is_array($udetails) && count($udetails) > 0)
        {
            $defaultCountry = $userCountry;
            $defaultCity = $userCity;
            $lat1 = explode("BLARK", $userCoords)[0];
            $long1 = explode("BLARK", $userCoords)[1];
            $defaultGender = $udetails['gender'];
            $defaultBlooggroup = $udetails['blooggroup'];
            $defaultAccount = $udetails['accounttype'];
            
           
            $nAccount = null;
            $nCountry = null;
            $nCity = null;
            $nGender = null;
            $nBlooggroup = null;
            $nStartAgeRange = null;
            $nStopAgeRange = null;
            $limit = 0;
            $offset = 0;
            //Check account type
            if($udetails['accounttype'] == $this->defaults->getVIP())
            {
                 //Set Random Gender
            $randGender = $this->defaults->getGenders()[random_int(1,count($this->defaults->getGenders()) - 2)];
            //Set Random Account
            $randAccount = $this->defaults->getACCTTypes()[random_int(1,count($this->defaults->getACCTTypes()) - 2)];
            //set Random Blooggroup
            $randBlooggroupValue = $this->defaults->getGenotypes()[random_int(1,count($this->defaults->getGenotypes()) - 2)];
            //Set Random age
            $randStartAgeRange = random_int(18, 44);
            $randStopAgeRange =  random_int(45, 74);
                
                //setup agerange
                if($this->defaults->getAgeRanges()[(int)$reqAgeRange] != $this->defaults->getAgeRanges()[0] && $this->defaults->getAgeRanges()[(int)$reqAgeRange] != $this->defaults->getAgeRanges()[7])
                {
                        //checl if all is set and random
                        $nStartAgeRange = (int)explode(" to ", $this->defaults->getAgeRanges()[(int)$reqAgeRange])[0];
                        $nStopAgeRange = (int)explode(" to ", $this->defaults->getAgeRanges()[(int)$reqAgeRange])[1]; 
                }
                else
                {
                    if($this->defaults->getAgeRanges()[(int)$reqAgeRange] == "ALL")
                    {
                        $nStartAgeRange = (int)explode(" to ",$this->defaults->getAgeRanges()[1])[0];
                        $nStopAgeRange = (int)explode(" to ",$this->defaults->getAgeRanges()[6])[1];
                    }
                    else
                    {
                        $nStartAgeRange = $randStartAgeRange;
                        $nStopAgeRange = $randStopAgeRange;
                    }
                        
                }
                $nAccount = $this->defaults->getACCTTypes()[(int)$reqAccount];
                $nCountry = $reqCountry;
                $nCity = $reqCity;
                $nGender = $this->defaults->getGenders()[(int)$reqGender];
                $nBlooggroup = $this->defaults->getGenotypes()[(int)$reqBlooggroup];
                $limit = (int)$reqLimit;
                $offset = (int)$reqOffset;
                //get Matches
                $removemainuserfrommatch = "(username != '$userid')";
                $countryconcatsql = "(`lastseencountry` = '$nCountry')";
                $cityconcatsql =    "(`lastseencity` LIKE '%$nCity%')";
                $genderconcatsql = ($nGender == "ALL" ? "(`gender` = 'Male' OR `gender` = 'Female')" : ($nGender == "Male" || $nGender == "Female" ? "(`gender` = '$nGender')" : "(`gender` = '".$randGender."')"));
                $accountconcatsql = ($nAccount == "ALL" ? "(`accounttype` = 'Basic' OR `accounttype` = 'Premium' OR `accounttype` = 'VIP')" : ($nAccount == "Basic" || $nAccount == "Premium" || $nAccount == "VIP" ? "(`accounttype` = '$nAccount')" : "(`accounttype` = '".$randAccount."')"));
                $ageconcatsql = "(DATEDIFF(SYSDATE(), `dob`)/365 >= '$nStartAgeRange' AND DATEDIFF(SYSDATE(), `dob`)/365 <= '$nStopAgeRange')";
                $blooggroupconcatsql = ($nBlooggroup == "ALL" ? "(`blooggroup` = 'AA' OR `blooggroup` = 'AS' OR `blooggroup` = 'AC' OR `blooggroup` = 'SS' OR `blooggroup` = 'SC' OR `blooggroup` = 'CC')" : ($nBlooggroup == "AA" || $nBlooggroup == "AS" || $nBlooggroup == "AC" || $nBlooggroup == "SS" || $nBlooggroup == "SC" || $nBlooggroup == "CC" ? "(`blooggroup` = '$nBlooggroup')" : "(`blooggroup` = '$randBlooggroupValue')"));
                $randsql = "ORDER BY RAND()";
                $limitsql = ("LIMIT ".$limit);
                $offsetsql = ("OFFSET ".$offset);
                $matchsql = "SELECT `id`, `username`, `name`, `lastseencountry`, `lastseencity`, `lastseencoords`, `gender`, `blooggroup`, `accounttype`, `description`, `pverified`, `bverified`, `dob` FROM `users` WHERE ".$removemainuserfrommatch." AND ".$countryconcatsql." AND ".$cityconcatsql." AND ".$accountconcatsql." AND ".$genderconcatsql." AND ".$blooggroupconcatsql." AND ".$ageconcatsql." ".$randsql." ".$limitsql." ".$offsetsql;
                
                //perform query
                $matchData = $this->sessiondb->execute_return($matchsql);
                if(is_array($matchData) == true && count($matchData) > 0)
                {
                    
                    $newData = $matchData;
                    foreach ($matchData as $matcher => $value){
                        $muserid = $value['username'];
                        $mcoords = explode("BLARK", $value['lastseencoords']);
                        
                        $lat2 = $mcoords[0];
                        $long2 = $mcoords[1];
                        //set key
                        $newData[$matcher]['key'] = $matcher;
                        //set id
                        $newData[$matcher]['id'] = $value['id'];
                        //set distance
                        $newData[$matcher]['distance'] = $this->requestlocation->distance($lat1, $long1, $lat2, $long2);
                        //set image
                        $picturesql = "SELECT title, ext FROM gallery WHERE userid='$muserid' AND type='Image' AND isprofilepicture='true'";
                        $profileimage = $this->sessiondb->execute_return($picturesql);
                        if(is_array($profileimage) && count($profileimage) > 0)
                        {
                            $newData[$matcher]['url'] = $profileimage[0]['title'].".".$profileimage[0]['ext'];
                        }
                        else
                        {
                            $newData[$matcher]['url'] = "user.png";
                        }

                        //get online status
                        $newData[$matcher]['online'] = ($this->isOnline($muserid) == true ? "true" : "false");
                        //remove ptivate fields
                        unset($newData[$matcher]['username']);

                        //convert arrar to json 
                        
                        }
                    $dbuserid = $udetails['id'];
                    $this->updateusersessionaction($dbuserid, $token, $this->defaults->getActionType()[4], $userCoords);
                    $this->response['response'] = true;
                    $this->response['data'] = $newData;
                    
                }
                else
                {
                    $this->response['data'] = [];
                    $this->response['response'] = true;
                    $this->response['message'] = "No results available, please try adjusting the filter options";
                }
                
                //update user lastseen location data
                
                $updateUserLastSQL = "UPDATE `users` SET `lastseencountry` = '$defaultCountry', `lastseencity` = '$defaultCity', `lastseencoords` = '$userCoords'  WHERE username='$userid' OR email='$userid'";
                $this->sessiondb->execute_no_return($updateUserLastSQL);

            }
            else if($udetails['accounttype'] === $this->defaults->getPremium())
            {
               //Set Random Gender
            $randGender = $this->defaults->getGenders()[random_int(1,count($this->defaults->getGenders()) - 2)];
            //Set Random Account
            $randAccount = $this->defaults->getACCTTypes()[random_int(1,count($this->defaults->getACCTTypes()) - 2)];
            //set Random Blooggroup
            $randBlooggroupValue = $this->defaults->getGenotypes()[random_int(1,count($this->defaults->getGenotypes()) - 2)];
            //Set Random age
            $randStartAgeRange = random_int(18, 44);
            $randStopAgeRange =  random_int(45, 74);
                
                //setup agerange
                if($this->defaults->getAgeRanges()[(int)$reqAgeRange] != $this->defaults->getAgeRanges()[0] && $this->defaults->getAgeRanges()[(int)$reqAgeRange] != $this->defaults->getAgeRanges()[7])
                {
                        //checl if all is set and random
                        $nStartAgeRange = (int)explode(" to ", $this->defaults->getAgeRanges()[(int)$reqAgeRange])[0];
                        $nStopAgeRange = (int)explode(" to ", $this->defaults->getAgeRanges()[(int)$reqAgeRange])[1]; 
                }
                else
                {
                    if($this->defaults->getAgeRanges()[(int)$reqAgeRange] == "ALL")
                    {
                        $nStartAgeRange = (int)explode(" to ",$this->defaults->getAgeRanges()[1])[0];
                        $nStopAgeRange = (int)explode(" to ",$this->defaults->getAgeRanges()[6])[1];
                    }
                    else
                    {
                        $nStartAgeRange = $randStartAgeRange;
                        $nStopAgeRange = $randStopAgeRange;
                    }
                        
                }
                $nAccount = $nAccount = (random_int(0, 10) % 2 == 0 ? $defaultAccount : $randAccount);
                $nCountry = $reqCountry;
                $nCity = $reqCity;
                $nGender = $this->defaults->getGenders()[(int)$reqGender];
                $nBlooggroup = (random_int(0, 10) % 2 ? $defaultBlooggroup : $randBlooggroupValue);
                $limit = (int)$reqLimit;
                $offset = (int)$reqOffset;
                //get Matches
                $removemainuserfrommatch = "(username != '$userid')";
                $countryconcatsql = "(`lastseencountry` = '$nCountry')";
                $cityconcatsql =    "(`lastseencity` LIKE '%$nCity%')";
                $genderconcatsql = ($nGender == "ALL" ? "(`gender` = 'Male' OR `gender` = 'Female')" : ($nGender == "Male" || $nGender == "Female" ? "(`gender` = '$nGender')" : "(`gender` = '".$randGender."')"));
                $accountconcatsql = ($nAccount == "ALL" ? "(`accounttype` = 'Basic' OR `accounttype` = 'Premium' OR `accounttype` = 'VIP')" : ($nAccount == "Basic" || $nAccount == "Premium" || $nAccount == "VIP" ? "(`accounttype` = '$nAccount')" : "(`accounttype` = '".$randAccount."')"));
                $ageconcatsql = "(DATEDIFF(SYSDATE(), `dob`)/365 >= '$nStartAgeRange' AND DATEDIFF(SYSDATE(), `dob`)/365 <= '$nStopAgeRange')";
                $blooggroupconcatsql = ($nBlooggroup == "ALL" ? "(`blooggroup` = 'AA' OR `blooggroup` = 'AS' OR `blooggroup` = 'AC' OR `blooggroup` = 'SS' OR `blooggroup` = 'SC' OR `blooggroup` = 'CC')" : ($nBlooggroup == "AA" || $nBlooggroup == "AS" || $nBlooggroup == "AC" || $nBlooggroup == "SS" || $nBlooggroup == "SC" || $nBlooggroup == "CC" ? "(`blooggroup` = '$nBlooggroup')" : "(`blooggroup` = '$randBlooggroupValue')"));
                $randsql = "ORDER BY RAND()";
                $limitsql = ("LIMIT ".$limit);
                $offsetsql = ("OFFSET ".$offset);
                $matchsql = "SELECT `id`, `username`, `name`, `lastseencountry`, `lastseencity`, `lastseencoords`, `gender`, `blooggroup`, `accounttype`, `description`, `pverified`, `bverified`, `dob` FROM `users` WHERE ".$removemainuserfrommatch." AND ".$countryconcatsql." AND ".$cityconcatsql." AND ".$accountconcatsql." AND ".$genderconcatsql." AND ".$blooggroupconcatsql." AND ".$ageconcatsql." ".$randsql." ".$limitsql." ".$offsetsql;
                
                //perform query
                $matchData = $this->sessiondb->execute_return($matchsql);
                
                if(is_array($matchData) == true && count($matchData) > 0)
                {
                    
                    $newData = $matchData;
                    foreach ($matchData as $matcher => $value){
                        $muserid = $value['username'];
                        $mcoords = explode("BLARK", $value['lastseencoords']);
                        
                        $lat2 = $mcoords[0];
                        $long2 = $mcoords[1];
                        //set key
                        $newData[$matcher]['key'] = $matcher;
                        //set id
                        $newData[$matcher]['id'] = $value['id'];
                        //set distance
                        $newData[$matcher]['distance'] = $this->requestlocation->distance($lat1, $long1, $lat2, $long2);
                        //set image
                        $picturesql = "SELECT title, ext FROM gallery WHERE userid='$muserid' AND type='Image' AND isprofilepicture='true'";
                        $profileimage = $this->sessiondb->execute_return($picturesql);
                        if(is_array($profileimage) && count($profileimage) > 0)
                        {
                            $newData[$matcher]['url'] = $profileimage[0]['title'].".".$profileimage[0]['ext'];
                        }
                        else
                        {
                            $newData[$matcher]['url'] = "user.png";
                        }

                        //get online status
                        $newData[$matcher]['online'] = ($this->isOnline($muserid) == true ? "true" : "false");
                        //remove ptivate fields
                        unset($newData[$matcher]['username']);

                        //convert arrar to json 
                        
                        }
                    $dbuserid = $udetails['id'];
                    $this->updateusersessionaction($dbuserid, $token, $this->defaults->getActionType()[4], $userCoords);
                    $this->response['response'] = true;
                    $this->response['data'] = $newData;
                    
                }
                else
                {
                    $this->response['data'] = [];
                    $this->response['response'] = true;
                    $this->response['message'] = "No results available, please try adjusting the filter options";
                }
                
                //update user lastseen location data
                
                $updateUserLastSQL = "UPDATE `users` SET `lastseencountry` = '$defaultCountry', `lastseencity` = '$defaultCity', `lastseencoords` = '$userCoords'  WHERE username='$userid' OR email='$userid'";
                $this->sessiondb->execute_no_return($updateUserLastSQL);
                
            }
            else if($udetails['accounttype'] === $this->defaults->getNormal())
            {
               //Set Random Gender
            $randGender = $this->defaults->getGenders()[random_int(1,count($this->defaults->getGenders()) - 2)];
            //Set Random Account
            $randAccount = $this->defaults->getACCTTypes()[random_int(1,count($this->defaults->getACCTTypes()) - 2)];
            //set Random Blooggroup
            $randBlooggroupValue = $this->defaults->getGenotypes()[random_int(1,count($this->defaults->getGenotypes()) - 2)];
            //Set Random age
            $randStartAgeRange = random_int(18, 44);
            $randStopAgeRange =  random_int(45, 74);
                
                
                $nStartAgeRange = $randStartAgeRange;
                $nStopAgeRange = $randStopAgeRange;
                $nAccount = (random_int(0, 10) % 2 == 0 ? $defaultAccount : $randAccount);
                $nCountry = $defaultCountry;
                $nCity = $defaultCity;
                $nGender = (random_int(0, 10) % 2 == 0 ? $defaultGender : $randGender);
                $nBlooggroup = (random_int(0, 10) % 2 ? $defaultBlooggroup : $randBlooggroupValue);
                $limit = (int)$reqLimit;
                $offset = (int)$reqOffset;
                
                //get Matches
                $removemainuserfrommatch = "(username != '$userid')";
                $countryconcatsql = "(`lastseencountry` = '$nCountry')";
                $cityconcatsql =    "(`lastseencity` LIKE '%$nCity%')";
                $genderconcatsql = ($nGender == "ALL" ? "(`gender` = 'Male' OR `gender` = 'Female')" : ($nGender == "Male" || $nGender == "Female" ? "(`gender` = '$nGender')" : "(`gender` = '".$randGender."')"));
                $accountconcatsql = ($nAccount == "ALL" ? "(`accounttype` = 'Basic' OR `accounttype` = 'Premium' OR `accounttype` = 'VIP')" : ($nAccount == "Basic" || $nAccount == "Premium" || $nAccount == "VIP" ? "(`accounttype` = '$nAccount')" : "(`accounttype` = '".$randAccount."')"));
                $ageconcatsql = "(DATEDIFF(SYSDATE(), `dob`)/365 >= '$nStartAgeRange' AND DATEDIFF(SYSDATE(), `dob`)/365 <= '$nStopAgeRange')";
                $blooggroupconcatsql = ($nBlooggroup == "ALL" ? "(`blooggroup` = 'AA' OR `blooggroup` = 'AS' OR `blooggroup` = 'AC' OR `blooggroup` = 'SS' OR `blooggroup` = 'SC' OR `blooggroup` = 'CC')" : ($nBlooggroup == "AA" || $nBlooggroup == "AS" || $nBlooggroup == "AC" || $nBlooggroup == "SS" || $nBlooggroup == "SC" || $nBlooggroup == "CC" ? "(`blooggroup` = '$nBlooggroup')" : "(`blooggroup` = '$randBlooggroupValue')"));
                $randsql = "ORDER BY RAND()";
                $limitsql = ("LIMIT ".$limit);
                $offsetsql = ("OFFSET ".$offset);
                $matchsql = "SELECT `id`, `username`, `name`, `lastseencountry`, `lastseencity`, `lastseencoords`, `gender`, `blooggroup`, `accounttype`, `description`, `pverified`, `bverified`, `dob` FROM `users` WHERE ".$removemainuserfrommatch." AND ".$countryconcatsql." AND ".$cityconcatsql." AND ".$accountconcatsql." AND ".$genderconcatsql." AND ".$blooggroupconcatsql." AND ".$ageconcatsql." ".$randsql." ".$limitsql." ".$offsetsql;
                
                //perform query
                $matchData = $this->sessiondb->execute_return($matchsql);
                if(is_array($matchData) == true && count($matchData) > 0)
                {
                    
                    $newData = $matchData;
                    foreach ($matchData as $matcher => $value){
                        $muserid = $value['username'];
                        $mcoords = explode("BLARK", $value['lastseencoords']);
                        
                        $lat2 = $mcoords[0];
                        $long2 = $mcoords[1];
                        //set key
                        $newData[$matcher]['key'] = $matcher;
                        //set id
                        $newData[$matcher]['id'] = $value['id'];
                        //set distance
                        $newData[$matcher]['distance'] = $this->requestlocation->distance($lat1, $long1, $lat2, $long2);
                        //set image
                        $picturesql = "SELECT title, ext FROM gallery WHERE userid='$muserid' AND type='Image' AND isprofilepicture='true'";
                        $profileimage = $this->sessiondb->execute_return($picturesql);
                        if(is_array($profileimage) && count($profileimage) > 0)
                        {
                            $newData[$matcher]['url'] = $profileimage[0]['title'].".".$profileimage[0]['ext'];
                        }
                        else
                        {
                            $newData[$matcher]['url'] = "user.png";
                        }

                        //get online status
                        $newData[$matcher]['online'] = ($this->isOnline($muserid) == true ? "true" : "false");
                        //remove ptivate fields
                        unset($newData[$matcher]['username']);

                        //convert arrar to json 
                        
                        }
                    $dbuserid = $udetails['id'];
                    $this->updateusersessionaction($dbuserid, $token, $this->defaults->getActionType()[4], $userCoords);
                    $this->response['response'] = true;
                    $this->response['data'] = $newData;
                    
                }
                else
                {
                    $this->response['data'] = [];
                    $this->response['response'] = true;
                    $this->response['message'] = "No results available, please try adjusting the filter options";
                }
                
                //update user lastseen location data
                
                $updateUserLastSQL = "UPDATE `users` SET `lastseencountry` = '$defaultCountry', `lastseencity` = '$defaultCity', `lastseencoords` = '$userCoords'  WHERE username='$userid' OR email='$userid'";
                $this->sessiondb->execute_no_return($updateUserLastSQL);
                
            }
            else
            {
                $this->response['data'] = [];
                $this->response['response'] = "invalid-account-selection-type";
                $this->response['message'] = "Invalid account type, please use the app properly";
            }
        }
        else
        {
            $this->response['data'] = [];
            $this->response['response'] = "username-exist-false";
            $this->response['message'] = "Invalid user, please use the app properly";
        }
        
       
        return json_encode($this->response, 1);
        
    }

    public function chekIfItsAMatvh($userdbid, $matchuserdbid){
        
        
        //check if match table exist
                if($this->sessiondb->execute_count_table_no_return("matches") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `matches` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userdbid` VARCHAR(160) NOT NULL ,
                        `matchdbid` VARCHAR(160) NOT NULL ,
                        `timestamp` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }

                //check if user already sent a like
                $checkuserlikerequest = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM matches WHERE userdbid='$userdbid' AND matchdbid='$matchuserdbid'");
                    
                if($checkuserlikerequest == 1)
                {
                    //chck if match already sent a like
                    $checkmatchlikerequst = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM matches WHERE userdbid='$matchuserdbid' AND matchdbid='$userdbid'");
                    
                    if($checkmatchlikerequst == 1)
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }


    }


    public function sendNotification($MACTHDBID, $USERDBID, $responseTag,  $notificationtype = 0){
        /*
            0 -> General Notification
            1 -> Like Notification
            2 -> Match Notification
            3 -> Gift Notification
            4 -> Like Error Notification
            5 -> Gift Error Notification

        */

        if($this->sessiondb->execute_count_table_no_return("notification") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `notifications` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userdbid` VARCHAR(160) NOT NULL ,
                        `matchdbid` VARCHAR(160) NOT NULL ,
                        `type` INT(16) NOT NULL ,
                        `sent` VARCHAR(160) NOT NULL ,
                        `seen` VARCHAR(160) NOT NULL ,
                        `response` VARCHAR(160) NOT NULL ,
                        `timestamp` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
            //check if notification data already exist
            if($this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM notifications WHERE userdbid='$USERDBID' AND matchdbid='$MACTHDBID' AND type='$notificationtype' AND response='$responseTag'") == 0){
                
                //add new notification data
                $timestamp = date("Y-m-d, H-i-s");
                $this->sessiondb->execute_no_return("INSERT INTO `notifications`(`userdbid`, `matchdbid`, `type`, `sent`, `seen`, `response`, `timestamp`) VALUES ('$USERDBID', '$MACTHDBID', '$notificationtype', 'false', 'false', '$responseTag', '$timestamp')");
                
            }
            
    }


    public function isOnline($username)
    {
       //verify $token exist with user is valid
        $vsql = "SELECT id FROM users WHERE username='$username'";
        if(count($this->sessiondb->execute_return($vsql)) > 0)
        {
            $dbuserid = $this->sessiondb->execute_return($vsql)[0]['id'];
            //Token is current in user profile, check if it is current is user session
            
            $sessionql = "SELECT created FROM usersession WHERE userid='$dbuserid' AND validity='true'";
            $sessiondata = $this->sessiondb->execute_return($sessionql);
            if(count($sessiondata) > 0){
                
                //Token exist to be valid in DB
                //Verify token validity
                $mytimestamp = date_create($sessiondata[0]['created'])->getTimestamp() + $this->defaults->getonlineExpTime();
                if ($mytimestamp > time() ){
                    
                    //User is Online
                    return true;
                    
                }
                else
                {
                    //User is Offline
                    return false;
                    
                }
                
                
            }
            else
            {
                //User is offline
                return false;
                
            }   
            
        }
        else
        {
            //User is offline
            return false;
            
        }
        
               
    }


    public function Yup($userid, $matchuserdbid, $lastseencoords, $token){
        
        //check if match table exist
                if($this->sessiondb->execute_count_table_no_return("matches") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `matches` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userdbid` VARCHAR(160) NOT NULL ,
                        `matchdbid` VARCHAR(160) NOT NULL ,
                        `timestamp` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
               //check if matchuser exist
                $museridcheck = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) from users WHERE id = '$matchuserdbid'");
                if($museridcheck ==1)
                {
                    //get initiator dbid
                    $userdbid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid'")[0]['id'];
                    //check if not already liked
                    
                    $checkifalreadyliked = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM matches WHERE userdbid='$userdbid' AND matchdbid='$matchuserdbid'");
                    
                    if($checkifalreadyliked == 0)
                    {
                        //save new like
                        $timestamp = date("Y-m-d, H-i-s");
                        $this->sessiondb->execute_no_return("INSERT INTO `matches`(`userdbid`, `matchdbid`, `timestamp`) VALUES ('$userdbid','$matchuserdbid', '$timestamp')");
                        
                        //check if match has occured
                        if($this->chekIfItsAMatvh($userdbid, $matchuserdbid) == true)
                        {
                            //send like notification
                            $this->sendNotification($matchuserdbid, $userdbid,"user-match-success", 2);

                        }
                        
                        //send Yup notification
                        $this->sendNotification($matchuserdbid, $userdbid, "yup-user-success", 1);
                        $this->updateusersessionaction($userdbid, $token, $this->defaults->getActionType()[5], $lastseencoords);
                        $this->response['response'] = true;

                    }
                    else
                    {
                        $this->sendNotification($matchuserdbid, $userdbid, "yup-already-liked", 4);
                        $this->response['response'] = "already-liked";
                    }
                
                }
                else
                {
                    $this->sendNotification($matchuserdbid, $userdbid, "yup-invalid-match-user", 4);
                    $this->response['response'] = "invalid-match-user";
                }
               
        return json_encode($this->response, 1);
    }
    
    public function Gift($userid, $matchuserdbid, $giftidentifier, $lastseencoords, $token){
        //check if match table exist
                if($this->sessiondb->execute_count_table_no_return("matches") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `matches` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userdbid` VARCHAR(160) NOT NULL ,
                        `matchdbid` VARCHAR(160) NOT NULL ,
                        `timestamp` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
                    //check if matchuser exist
                        $museridcheck = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) from users WHERE id = '$matchuserdbid'");
                        if($museridcheck ==1)
                        {
                            $checkgiftidentifier = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) from gifts WHERE identifier = '$giftidentifier'");
                            if($checkgiftidentifier == 1)
                            {
                                //get initiator dbid
                                $gidbdetails = $this->sessiondb->execute_return("SELECT id, gc FROM users WHERE username='$userid'")[0];
                                
                                $userdbid = $gidbdetails['id'];
                                $gc= (double)$gidbdetails['gc'];
                                //get Default gift price
                                $giftdbprice = $this->sessiondb->execute_return("SELECT `value` FROM `generalstatus` WHERE `identifier`='giftcharge'")[0]['value'];
                                $giftprice = (double)$giftdbprice;
                                if($gc >= $giftprice)
                                {
                                    //check if not already liked

                                        $checkifalreadyliked = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM matches WHERE userdbid='$userdbid' AND matchdbid='$matchuserdbid'");
                                        
                                        if($checkifalreadyliked == 0)
                                        {
                                            //save new like
                                            $timestamp = date("Y-m-d, H-i-s");
                                            $this->sessiondb->execute_no_return("INSERT INTO `matches`(`userdbid`, `matchdbid`, `timestamp`) VALUES ('$userdbid','$matchuserdbid', '$timestamp')");
                                            
                                            //check if match has occured
                                            if($this->chekIfItsAMatvh($userdbid, $matchuserdbid) == true)
                                            {
                                                //send like notification
                                                $this->sendNotification($matchuserdbid, $userdbid, "user-match-success", 2);

                                            }

                                        }
                                        
                                        //deduct user's gc
                                        $newgc = $gc - $giftprice;
                                        $this->sessiondb->execute_no_return("UPDATE `users` SET `gc`='$newgc' WHERE `username`='$userid'");
                                        //insert new giftpayment
                                        $timestamp = date("Y-m-d, H-i-s");
                                        $giftpaymenttablesql = "
                                        CREATE TABLE `giftpayments` (
                                            `id` int(16) NOT NULL AUTO_INCREMENT,
                                            `identifier` varchar(160) NOT NULL,
                                            `giftidentifier` varchar(160) NOT NULL,
                                            `userdbid` int(16) NOT NULL,
                                            `matchuserdbid` int(16) NOT NULL,
                                            `timestamp` varchar(160) NOT NULL,
                                            PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                                        $identifier = $this->sessiondb->execute_unique_id_return("giftpayments");
                                        $this->sessiondb->execute_no_return("INSERT INTO `giftpayments`(`identifier`, `giftidentifier`, `userdbid`, `matchuserdbid`, `timestamp`) VALUES ('$identifier', '$giftidentifier', '$userdbid', '$matchuserdbid', '$timestamp')");
                                        $this->updateusersessionaction($userdbid, $token, $this->defaults->getActionType()[6], $lastseencoords);
                                        //send gift notification
                                        $this->sendNotification($matchuserdbid, $userdbid, "gift-user-success", 3);
                                        $this->response['response'] = true;
                                        $this->response['message'] = "Successfully Gifted";
                                }
                                else
                                {
                                    //user has insufficient gc
                                    $this->sendNotification($matchuserdbid, $userdbid, "gift-insufficient-gc", 5);
                                    $this->response['response'] = "insufficient-gc";
                                    $this->response['message'] = "You have insufficient Gold-Credit (GC), top-up your GC to enable you gift this item.";
                                }

                        }
                        else
                        {
                            //user has insufficient gc
                            $this->sendNotification($matchuserdbid, $userdbid, "gift-selected-gift-item-unavailable", 5);
                            $this->response['response'] = "selected-gift-item-unavailable";
                            $this->response['message'] = "The selected gift item is unavailable, please use the app properly.";
                        }
                
                }
                else
                {
                    $this->sendNotification($matchuserdbid, $userdbid, "gift-Invalid-match-user", 5);
                    $this->response['response'] = "Invalid-match-user";
                    $this->response['message'] = "Invalid Match User ID";
                }
               
        return json_encode($this->response, 1);
    }


    public function viewGift($userid, $giftpaymentidentifier, $lastseencoords, $token){
        //check if match table exist
                if($this->sessiondb->execute_count_table_no_return("matches") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `matches` ( 
                        `id` INT(16) NOT NULL AUTO_INCREMENT , 
                        `userdbid` VARCHAR(160) NOT NULL ,
                        `matchdbid` VARCHAR(160) NOT NULL ,
                        `timestamp` VARCHAR(160) NOT NULL , 
                        PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }

                if($this->sessiondb->execute_count_table_no_return("giftpayments") == 0)
                {
                    $tablequery = "
                                            CREATE TABLE `giftpayments` (
                                            `id` int(16) NOT NULL AUTO_INCREMENT,
                                            `identifier` varchar(160) NOT NULL,
                                            `giftidentifier` varchar(160) NOT NULL,
                                            `userdbid` int(16) NOT NULL,
                                            `matchuserdbid` int(16) NOT NULL,
                                            `timestamp` varchar(160) NOT NULL,
                                            PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
               //get userdbid
               $userdbid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid'")[0]['id']; 

               //verify giftpaymentidentifier
               $giftpaydata = $this->sessiondb->execute_return("SELECT giftidentifier, userdbid FROM giftpayments WHERE matchuserdbid='$userdbid' AND identifier='$giftpaymentidentifier'"); 
                if(is_array($giftpaydata) && count($giftpaydata) > 0)
                {
                    $matchuserdbid = $giftpaydata[0]['userdbid'];
                    $checkifalreadyliked = $this->sessiondb->execute_count_no_return("SELECT COUNT(*) FROM matches WHERE userdbid='$userdbid' AND matchdbid='$matchuserdbid'");
                                        
                    if($checkifalreadyliked == 1)
                    {
                        //send view details
                        $giftidentifier = $giftpaydata[0]['giftidentifier'];
                        //get giftextension
                        $dbdata = $this->sessiondb->execute_return("SELECT identifier, ext FROM gifts WHERE identifier='$userid'")[0]; 
                        $this->updateusersessionaction($userdbid, $token, $this->defaults->getActionType()[7], $lastseencoords);
                        $this->response['response'] = true;
                        $this->response['data'] = $dbdata;
                    }
                    else
                    {
                        $this->response['response'] = "not-a-match";
                        $this->response['message'] = "You have to give a Yup to this user to view gift sent.";
                    }
                }
                else
                {
                    $this->response['response'] = "invalid-gift-id";
                    $this->response['message'] = "The selected gift item is unavailable for your view";
                }
               
        return $this->response;
    }




    public function getUserGiftedItems($userid, $lastseencoords, $token){

                if($this->sessiondb->execute_count_table_no_return("giftpayments") == 0)
                {
                    $tablequery = "
                                            CREATE TABLE `giftpayments` (
                                            `id` int(16) NOT NULL AUTO_INCREMENT,
                                            `identifier` varchar(160) NOT NULL,
                                            `giftidentifier` varchar(160) NOT NULL,
                                            `userdbid` int(16) NOT NULL,
                                            `matchuserdbid` int(16) NOT NULL,
                                            `timestamp` varchar(160) NOT NULL,
                                            PRIMARY KEY (`id`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
               //get userdbid
               $userdbid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid'")[0]['id']; 

               $usergifteditems = $this->sessiondb->execute_return("SELECT identifier FROM giftpayments WHERE matchuserdbid='$userdbid'"); 
                if(is_array($usergifteditems) == true && count($usergifteditems) > 0)
                {
                    $this->response['data'] = $usergifteditems;
                }
                else
                {
                    $this->response['data'] = [];
                }
        $this->updateusersessionaction($userdbid, $token, $this->defaults->getActionType()[8], $lastseencoords);        
        $this->response['response'] = "success";
        $this->response['message'] = "The selected gift item is unavailable for your view";
               
        return $this->response;
    }
    
    
     public function loadAvailableGiftItems($userid, $lastseencoords, $token){

                if($this->sessiondb->execute_count_table_no_return("giftpayments") == 0)
                {
                    $tablequery = "
                    CREATE TABLE `giftpayments` (
                        `id` int(16) NOT NULL AUTO_INCREMENT,
                        `identifier` varchar(160) NOT NULL,
                        `giftidentifier` varchar(160) NOT NULL,
                        `userdbid` int(16) NOT NULL,
                        `matchuserdbid` int(16) NOT NULL,
                        `timestamp` varchar(160) NOT NULL,
                        PRIMARY KEY (`id`), 
                        UNIQUE (`identifier`)) ENGINE = InnoDB;";
                    $this->sessiondb->execute_no_return($tablequery);
                }
                
               //get userdbid
               $userdbid = $this->sessiondb->execute_return("SELECT id FROM users WHERE username='$userid'")[0]['id']; 

               $giftitems = $this->sessiondb->execute_return("SELECT identifier, ext FROM gifts WHERE 1"); 
                if(is_array($giftitems) == true && count($giftitems) > 0)
                {
                    $this->response['data'] = $giftitems;
                }
                else
                {
                    $this->response['data'] = [];
                }
        $this->updateusersessionaction($userdbid, $token, $this->defaults->getActionType()[9], $lastseencoords);        
        $this->response['response'] = "success";
        $this->response['message'] = "The selected gift item is unavailable for your view";
               
        return $this->response;
    }
    
    
   
    
    
}