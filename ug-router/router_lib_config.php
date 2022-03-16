<?php
/*
router library configuration @ugoabuchi
 */
define("ug_default_main_uri", $_SERVER['REQUEST_URI']);
define("ug_https_security", false);
define("ug_default_link", "webapp/landing/index.php");
define("ug_404_link", "webapp/404/index.php");

class router_lib_config{

    private $links = array();

    function __construct()
    {
        $this->links = 
        array(

            //home links
             "webapp/landing/index.php" => "",
             "webapp/landing/index.php" => "home",
             "webapp/landing/index.php" => "comingsoon",
             "webapp/landing/subscribe.php" => "subscribe",
             "webapp/404/index.php" => "404",
            

        
            
            
            //pages
            //let default and login page
           

            //request links
            "webapp/App/API.php" => "API_REQUEST"

        );
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getDefaultMainURI()
    {
        return ug_default_main_uri;
    }

    public function getHTPSSecurity()
    {
        return ug_https_security;
    }

    public function getDefaultLink()
    {
        return ug_default_link;
    }

    public function getDefault404()
    {
        return ug_404_link;
    }
}
