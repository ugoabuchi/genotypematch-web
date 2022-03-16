<?php

/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */

class defaults
{
    private $accounttypes = null;
    private $codetypes = null;
    private $actions = null;
    private $actiontypes = null;
    private $expiryTimes = null;
    private $imagekb = null;
    private $uploadsuburl = null;
    private $gender = null;
    private $agerange = null;
    private $genotypes = null;
    private $filter = null;
    private $regex = null;
    
    //lets add regex defaults
     function __construct()
    {
         $this->accounttypes = array("Basic", "Premium", "VIP");
         $this->ACCT = array("ALL", "Basic", "Premium", "VIP", "RANDOM");
         $this->codetypes = array("Verification", "Activation", "Recovery");
         $this->actiontypes = array(
             "Action to Login", 
             "Action to add to gallery", 
             "Action to remove from gallery",
             "Action to update profile",
             "Action to fetch matches",
             "Action to perform yup",
             "Action to perform gift",
             "Action to perform view gift",
             "Action to fetch user gifted items",
             "Action to fetch available gift items",
             "Action to perform nope",
             "Action to perform unmatch"
             );
         $this->expiryTimes = array(
             "verification" =>300, 
             "session" => 43200,
             "online" => 12000
             );
         $this->imagekb = 2000000; //in Bytes
         $this->uploadsuburl = "webapp/"; //Attach link to uploads locals
         $this->gender = array(
             "ALL", "Male", "Female", "RANDOM"
         );
         $this->agerange = array(
                "ALL",
                "18 to 24",
                "25 to 34",
                "35 to 44",
                "45 to 54",
                "55 to 64",
                "65 to 74",
                "RANDOM"
         );
         $this->genotypes = array(
                "ALL",
                "AA",
                "AS",
                "AC",
                "SS",
                "SC",
                "CC",
                "RANDOM"
         );
         $this->filter = array(
             10,
             0
            );
         $this->regex = array(
            "/^([\w]{3,})+\s+([\w]{3,})+$/", //0 - name regex
            "/^[a-z][\w\d]{3,18}\w$/", //1 -username regex
            "/^[1-9]{6}$/" //passkey
            );
         $this->actions = array(
           "login", //0 - Sign in user
           "checkUsernameExist", //1 - Check if username exist
           "updateMatches",  //2 - Update or Get User Matches
           "performYUP", //3 - Perform yup Request
           "performGift", //4 - Perform Gifting Request
           "performNope", //5 - Perform Gifting Request
           "performUnMatch", //6 - Perform Gifting Request
         );
    }
    public function getActions(){
        return $this->actions;
    }
    
    public function getRegex(){
        return $this->regex;
    }
    
    public function getFilter(){
        return $this->filter;
    }
    public function getGenotypes(){
        return $this->genotypes;
    }
    public function getAgeRanges(){
        return $this->agerange;
    }
    public function getGenders(){
        return $this->gender;
    }
    public function getActionType(){
        return $this->actiontypes;
    }
    
    public function getAddToGalleryActionKey(){
        return 1;
    }
    
    public function getRemoveFromGalleryActionKey(){
        return 2;
    }
    
    public function getACCTTypes(){
        return $this->ACCT;
    }
    
    public function getNormal(){
        return $this->accounttypes[0];
    }
    
    public function getPremium(){
        return $this->accounttypes[1];
    }
    
    public function getVIP(){
        return $this->accounttypes[2];
    }
    
    public function getVerificationCodeName(){
        return $this->codetypes[0];
    }
    
    public function getActivationCodeName(){
        return $this->codetypes[1];
    }
    
    public function getRecoveryCodeName(){
        return $this->codetypes[2];
    }
    
    public function getVerificationExpTime(){
        return $this->expiryTimes["verification"];
    }
    
    public function getSessionExpTime(){
        return $this->expiryTimes["session"];
    }
    public function getonlineExpTime(){
        return $this->expiryTimes["online"];
    }
    
    public function getImageUploadSize(){
        return $this->imagekb;
    }
    
    public function getUploadSubURL(){
        return $this->uploadsuburl;
    }
    
    
    public function verifyaccounttype($accounttype)
    {
        $found = false;
        
        if($accounttype != "" && $accounttype != null)
        {
            if(in_array($accounttype, $this->accounttypes, true))
            {
                $found = true;
            }
            else
            {
                $found = false;
            }
        }
        
        return $found;
    }
    
    
}

