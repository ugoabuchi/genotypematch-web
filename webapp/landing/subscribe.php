<?php
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $resp = null;

    if(!isset($_POST['email']) || $_POST['email'] == "" || $_POST['email'] == null)
    {
    $resp["error"] =  true;
    $resp["message"] = "Your forgot to place your email";
    }
    else
    {
        $email = $_POST['email'];
        $db = new database("gm");
        if($db->execute_count_no_return("SELECT COUNT(*) FROM subscribers WHERE email='$email'") == 1){

            $resp["error"] =  true;
            $resp["message"] = "Your email has previously been added";

        }
        else
        {
            $db->execute_no_return("INSERT INTO `subscribers`(`email`, `dos`) VALUES ('$email',now())");
            $resp["error"] =  false;
            $resp["message"] = "Your email has been successfully added";
        }
    }
    
    die(json_encode($resp));
}