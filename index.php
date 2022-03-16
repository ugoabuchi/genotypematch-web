<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
session_start();
date_default_timezone_set("Africa/Lagos");

 //call global libraries here
include "ug-router/router_controller.php";
include "webapp/utils/database.php";
include "webapp/utils/tokengenerator.php";
include "webapp/utils/session.php";
include "webapp/utils/profile.php";
include "webapp/utils/codegenerator.php";
include "webapp/utils/defaults.php";
include "webapp/utils/mailer.php";
include "webapp/utils/mailmanager.php";
include "webapp/utils/requestlocation.php";

/*
include "../webapp/utils/logwriter.php";
*/

/*
//call interface
include "../webapp/interface/hmiinterface.php";
include "../webapp/interface/questioninterface.php";
include "../webapp/interface/liveclassinterface.php";
include "../webapp/interface/reginterface.php";

//call controllers
include "../webapp/controller/hmicontroller.php";
include "../webapp/controller/questioncontroller.php";
include "../webapp/controller/liveclasscontroller.php";
include "../webapp/controller/regcontroller.php";
*/

//start routing
$URI = $_SERVER['REQUEST_URI'];
$BURI = explode("/", $URI);
$routename = "";

foreach($BURI as $LURI)
{
$routename = $LURI;
}

$router = new router_controller($routename);
$router->route();