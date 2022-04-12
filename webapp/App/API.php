<?php
    //getIpAddress
    //$IPChecker = new requestlocation();
    $defaultRegex = new defaults();
   //change $IPChecker to true

    /*
    if($IPChecker->isValidIpAddress($IPChecker->getIpAddress()) == true)
 {
    /*$iPLocation = $IPChecker->getLocation($IPChecker->getIpAddress());
    $iPLocation['region'] = ($iPLocation['region'] == "" || empty($iPLocation['region']) == true || $iPLocation['region'] == null ? "none" : $iPLocation['region']);
    $lCode = array(
        $iPLocation['ip'],
        $iPLocation['country'],
        $iPLocation['country_code'],
        $iPLocation['country_flag'],
        $iPLocation['region'],
        $iPLocation['latitude'],
        $iPLocation['longitude'],
        );
    $locationcode = implode("blark", $lCode);
     */
    
    //$locationcode = "105.112.154.62blarkNigeriablarkNGblarkhttps:\/\/cdn.ipwhois.io\/flags\/ng.svgblarkLagosblark6.5243793blark3.3792057";
    if($_SERVER['REQUEST_METHOD'] == "POST"){
    //Handle Post Request
        if(isset($_POST['userid']) && $_POST['userid'] != "" && $_POST['userid'] != null && isset($_POST['action']) && $_POST['action'] != "" && $_POST['action'] != null){
       
            if(preg_match($defaultRegex->getRegex()[1], $_POST['userid']) == true){

                  $currentsession = new session();
                  $userid = strtolower($_POST['userid']);

                  if(!isset($_POST['token']) || $_POST['token'] == "" || $_POST['token'] == null)
                  {

                      //All request here are only handled before login

                      if($_POST['action'] == $defaultRegex->getActions()[0]){

                        if(isset($_POST['password']) && $_POST['password'] != "" && $_POST['password'] != null && isset($_POST['PNID']) && $_POST['PNID'] != "" && $_POST['PNID'] != null){

                            if(preg_match($defaultRegex->getRegex()[2], $_POST['password']) == true)
                            {
                                $password = $_POST['password'];
                                $PNID = $_POST['PNID'];
                                die($currentsession->setusersession($userid, $password, $PNID));
                            }
                            else{
                                
                                $response["response"] = "error-password-regex-false";
                                $response["message"] = "Passkey does not match rules";
                                 die(json_encode($response, 1));
                                
                            }

                        }
                        else
                        {
                                $response["response"] = "error-passkey-null";
                                $response["message"] = "Passkey is null";
                                 die(json_encode($response, 1));
                        }


                    }
                     else if($_POST['action'] == "sendemailverificationcode"){

                            die($currentsession->sendemailverificationcode($userid));

                    }
                    else if($_POST['action'] == "verifyaccount"){

                        if(isset($_POST['code']) && $_POST['code'] != ""){

                            $code = $_POST['code'];
                            die($currentsession->verifyaccount($userid, $code));

                        }
                        else
                        {
                                $response["response"] = "error";
                                $response["message"] = "Invalid credentials, action dismissed";

                                 die(json_encode($response, 1));
                        }


                    }
                    else if($_POST['action'] == "setpasscode"){

                        if(isset($_POST['oldpasscode']) && $_POST['oldpasscode'] != "" && isset($_POST['newpasscode']) && $_POST['newpasscode'] != ""){

                            $oldpasscode = $_POST['oldpasscode'];
                            $newpasscode = $_POST['newpasscode'];
                            die($currentsession->setPasscode($userid, $oldpasscode, $newpasscode));

                        }
                        else
                        {
                                $response["response"] = "error";
                                $response["message"] = "Invalid credentials, action dismissed";

                                 die(json_encode($response, 1));
                        }


                    }
                       else if($_POST['action'] == "recoverpassword"){

                        if(isset($_POST['code']) && $_POST['code'] != "" && isset($_POST['password']) && $_POST['password'] != "" ){

                            $code = $_POST['code'];
                            $newpassword = $_POST['password'];
                            die($currentsession->recoverpassword($userid, $code, $newpassword));

                        }
                        else
                        {
                                $response["response"] = "error";
                                $response["message"] = "Invalid credentials, action dismissed";

                                 die(json_encode($response, 1));
                        }


                    }
                    else if($_POST['action'] == "sendEmailVericationMail"){

                        if(isset($_POST['email']) && $_POST['email'] != "" && isset($_POST['name']) && $_POST['name'] != "" ){

                            $email = strtolower(trim($_POST['email']));
                            $name = ucfirst(trim($_POST['name']));
                            die($currentsession->sendEamilVerificationMail($email, $name));

                        }
                        else
                        {
                                $response["response"] = "error";
                                $response["message"] = "Invalid credentials, action dismissed";

                                 die(json_encode($response, 1));
                        }


                    }
                            else if($_POST['action'] == "checkEmailNotVerified"){

                            die($currentsession->checkEmailNotVerified($userid));


                    }
                    else if($_POST['action'] == "register"){

                        if(isset($_POST['email']) && $_POST['email'] != "" && isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['gender']) && $_POST['gender'] != "" && isset($_POST['married']) && $_POST['married'] != "" && isset($_POST['interestedin']) && $_POST['interestedin'] != "" && isset($_POST['phone']) && $_POST['phone'] != "" && isset($_POST['bloodgroup']) && $_POST['bloodgroup'] != "" && isset($_POST['description']) && $_POST['description'] != "" && isset($_POST['dob']) && $_POST['dob'] != "" && isset($_POST['password']) && $_POST['password'] != ""){

                            //validate login details
                            $email = strtolower(trim($_POST['email']));
                            $name = ucwords(trim($_POST['name']));
                            $gender = ucfirst(trim($_POST['gender']));
                            $married = ucfirst(trim($_POST['married']));
                            $interestedin = ucfirst(trim($_POST['interestedin']));
                            $phone = $_POST['phone'];
                            $bloodgroup = strtoupper(trim($_POST['bloodgroup']));
                            $description = trim($_POST['description']);
                            $dob = trim($_POST['dob']);
                            $password = $_POST['password'];
                            die($currentsession->registeruser($userid, $email, $name, $gender, $married, $interestedin, $phone, $bloodgroup, $password, $description, $dob));

                        }
                        else
                        {
                                $response["response"] = "error";
                                $response["message"] = "Invalid registration credentials, action dismissed";

                                 die(json_encode($response, 1));
                        }

                    }
                    else if($_POST['action'] == $defaultRegex->getActions()[1]){
                        die($currentsession->checkUsernameExist(strtolower(trim($userid))));

                    }
                    else
                    {
                                $response["response"] = "error-action-false";
                                $response["message"] = "Action not recognized, action dismissed";

                                die(json_encode($response, 1));
                    }




                  }
                  else
                  {

                      
                      //All request here are handled after login
                      $token = $_POST['token'];
                      $validity = json_decode($currentsession->verifyTokenValidity($userid, $token), 1);
                      if($validity['response'] == "success")
                      {
                          //Request Tokens are validated
                          $currentprofile = new profile();

                          if($_POST['action'] == "addtogallery")
                          {
                              if(isset($_FILES['images']) && $_FILES['images'] != null)
                              {
                                  //update usersession action
                                  die($currentprofile->addtogallery($userid, $token, $_FILES['images']));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Null image uploads, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }

                          else if($_POST['action'] == "deletefromgallery")
                          {
                              if(isset($_POST['image']) && $_POST['image'] != null)
                              {
                                  //update usersession action
                                  die($currentprofile->deletefromgallery($userid, $token, $_POST['image']));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Null image selections, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == "updatename")
                          {
                              if(isset($_POST['firstname']) && $_POST['firstname'] != null && isset($_POST['lastname']) && $_POST['lastname'] != null)
                              {
                                  $firstname = ucfirst($_POST['firstname']);
                                  $lastname = ucfirst($_POST['lastname']);
                                  //update usersession action
                                  die($currentprofile->updateName($userid, $token, $firstname, $lastname));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for profile update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == $defaultRegex->getActions()[2])
                          {
                              if(isset($_POST['coords']) && $_POST['coords'] != null && isset($_POST['country']) && $_POST['country'] != null && isset($_POST['city']) && $_POST['city'] != null && isset($_POST['reqcountry']) && $_POST['reqcountry'] != null && isset($_POST['reqcity']) && $_POST['reqcity'] != null && isset($_POST['gender']) && $_POST['gender'] != null && isset($_POST['bloodgroup']) && $_POST['bloodgroup'] != null && isset($_POST['agerange']) && $_POST['agerange'] != null && isset($_POST['account']) && $_POST['account'] != null && isset($_POST['limit']) && $_POST['limit'] != null && isset($_POST['offset']) && $_POST['offset'] != null)
                              {
                                  $account = trim($_POST['account']);
                                  $coords = $_POST['coords'];
                                  $country = strtoupper($_POST['country']);
                                  $city = ucwords(trim($_POST['city']));
                                  $reqcountry = strtoupper($_POST['reqcountry']);
                                  $reqcity = ucfirst(explode(' ', trim($_POST['reqcity']))[0]);
                                  $gender = ucfirst(trim($_POST['gender']));
                                  $blooggroup = strtoupper(trim($_POST['bloodgroup']));
                                  $agerange = $_POST['agerange'];
                                  $limit = $_POST['limit'];
                                  $offset = $_POST['offset'];
                                  //update usersession action
                                  die($currentprofile->loadMatches($userid, $account, $reqcountry, $reqcity, $gender, $blooggroup, $agerange, $coords, $country, $city, $limit, $offset, $token));
                              }
                              else
                              {
                                    $response["response"] = "error-invalid-request-params";
                                    $response["message"] = "Invalid criterias for getting matches, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == $defaultRegex->getActions()[3])
                          {
                              if(isset($_POST['matchuserdbID']) && $_POST['matchuserdbID'] != null && isset($_POST['coords']) && $_POST['coords'] != null){
                                  
                                  $matchuserdbID = (int)trim($_POST['matchuserdbID']);
                                  $coords = $_POST['coords'];
                                  die($currentprofile->Yup($userid, $matchuserdbID, $coords, $token));
                              }
                              else
                              {
                                    $response["response"] = "error-invalid-yup-request-params";
                                    $response["message"] = "Invalid criterias for performing YUP request, action dismissed";
                                    die(json_encode($response, 1));
                              }
                              
                          }
                          else if($_POST['action'] == $defaultRegex->getActions()[5])
                          {
                              if(isset($_POST['matchuserdbID']) && $_POST['matchuserdbID'] != null && isset($_POST['coords']) && $_POST['coords'] != null){
                                  
                                  $matchuserdbID = (int)trim($_POST['matchuserdbID']);
                                  $coords = $_POST['coords'];
                                  die($currentprofile->Nope($userid, $matchuserdbID, $coords, $token));
                              }
                              else
                              {
                                    $response["response"] = "error-invalid-nope-request-params";
                                    $response["message"] = "Invalid criterias for performing YUP request, action dismissed";
                                    die(json_encode($response, 1));
                              }
                              
                          }
                          else if($_POST['action'] == $defaultRegex->getActions()[4])
                          {
                              if(isset($_POST['matchuserdbID']) && $_POST['matchuserdbID'] != null && isset($_POST['coords']) && $_POST['coords'] != null){
                                  
                                  $matchuserdbID = (int)trim($_POST['matchuserdbID']);
                                  $coords = $_POST['coords'];
                                  $giftID = trim($_POST['giftID']);
                                  die($currentprofile->Gift($userid, $matchuserdbID, $giftID, $coords, $token));
                              }
                              else
                              {
                                    $response["response"] = "error-invalid-gift-request-params";
                                    $response["message"] = "Invalid criterias for performing GIFT request, action dismissed";
                                    die(json_encode($response, 1));
                              }
                              
                          }
                          else if($_POST['action'] == $defaultRegex->getActions()[7])
                          {
                              if(isset($_POST['coords']) && $_POST['coords'] != null)
                              {
                                $coords = $_POST['coords'];
                                die($currentprofile->loadAvailableGiftItems($userid, $coords, $token));
                              }
                              else
                              {
                                    $response["response"] = "error-invalid-gift-loading-request-params";
                                    $response["message"] = "Invalid criterias for performing load GIFT request, action dismissed";
                                    die(json_encode($response, 1));
                              }
                              
                          }
                          else if($_POST['action'] == "updatemarried")
                          {
                              if(isset($_POST['married']) && $_POST['married'] != null)
                              {
                                  $married = ucfirst($_POST['married']);
                                  //update usersession action
                                  die($currentprofile->updateMarried($userid, $token, $married));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for profile update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == "updatedescription")
                          {
                              if(isset($_POST['description']) && $_POST['description'] != null)
                              {
                                  $description = ucfirst($_POST['description']);
                                  //update usersession action
                                  die($currentprofile->updateDescription($userid, $token, $description));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for profile update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == "updateinterestedin")
                          {
                              if(isset($_POST['interestedin']) && $_POST['interestedin'] != null)
                              {
                                  $interestedin = ucfirst($_POST['interestedin']);
                                  //update usersession action
                                  die($currentprofile->updateInterestedIn($userid, $token, $interestedin));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for profile update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == "removeimageasmain")
                          {
                              if(isset($_POST['image']) && $_POST['image'] != null)
                              {
                                  $image = $_POST['image'];
                                  //update usersession action
                                  die($currentprofile->removeAsMainfromgallery($userid, $token, $image));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for profile update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == "setimageasmain")
                          {
                              if(isset($_POST['image']) && $_POST['image'] != null)
                              {
                                  $image = $_POST['image'];
                                  //update usersession action
                                  die($currentprofile->setAsMainfromgallery($userid, $token, $image));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for profile update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else if($_POST['action'] == "getgallery")
                          {
                                  die($currentprofile->getGallery($userid, $token));

                          }
                          else if($_POST['action'] == "updatepassword")
                          {
                              if(isset($_POST['oldpassword']) && $_POST['oldpassword'] != null && isset($_POST['newpassword']) && $_POST['newpassword'] != null)
                              {
                                  $oldpassword = $_POST['oldpassword'];
                                  $newpassword = $_POST['newpassword'];
                                  //update usersession action
                                  die($currentprofile->updatePassword($userid, $token, $oldpassword, $newpassword));
                              }
                              else
                              {
                                    $response["response"] = "error";
                                    $response["message"] = "Invalid criterias for password update, action dismissed";
                                    die(json_encode($response, 1));
                              }
                          }
                          else
                          {
                              $response["response"] = "error-action-false";
                              $response["message"] = "Action not recognized";

                              die(json_encode($response, 1));
                          }


                      }
                      else
                      {
                                $response["response"] = "error-token-false";
                                $response["message"] = $validity['message'];
                                die(json_encode($response, 1));
                      }


                  }
        

            }
            else
            {
                                $response["response"] = "error-username-regex-false";
                                $response["message"] = "Username does not match rules";
                                 die(json_encode($response, 1));
            }
      
      
        
    }
    else
    {
        
                    $response["response"] = "error-null-username-or-action";
                    $response["message"] = "Username or action is null";
                    
                    die(json_encode($response, 1));
    }
    
    
}
else if($_SERVER['REQUEST_METHOD'] == "GET")
{
                    $response["response"] = "error";
                    $response["message"] = "Request type not yet activated, action dismissed";
                    
                    die(json_encode($response, 1));
}
else{
                    $response["response"] = "error";
                    $response["message"] = "unauthorized access, action dismissed";
                    
                    die(json_encode($response, 1));
}

/*

}
else
{
                    $response["response"] = "error-insecure-connection";
                    $response["message"] = "Insecure connection";
                    
                    die(json_encode($response, 1));
}
 */
