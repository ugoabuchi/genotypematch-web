<?php
/*
 @ugoabuchi - @my github repo github.com/ugoabuchi
 */
include "router_lib_config.php";

class router_controller{

    private $router_config = null;
    private $route = "";
    private $route_include = "";

    function __construct($route)
    {
        $this->router_config = new router_lib_config();
        $this->route = $route;
        //check if route exist
        $links = $this->router_config->getLinks();
        $route_include = array_search($route, $links);

        //check if https is enabled from config
        if($this->router_config->getHTPSSecurity() == true)
        {
            if($_SERVER['REQUEST_SCHEME'] != "HTTPS")
            {
                die("Invalid connection scheme, only https is allowed");
            }
        }

        if($this->route != "")
        {
        if($route_include != "" && $route_include != null)
        {
            $this->route_include = $route_include;
            
        }
        else
        {
            //redirect to 404
            $this->route_include = $this->router_config->getDefault404();
        }
        }
        else
        {
            //set request method
            $this->request_method = $_SERVER['REQUEST_METHOD'];
            $this->route_include = $this->router_config->getDefaultLink();
        }

    }

    public function route()
    {
        //show content
        include "".$this->route_include;
    }

}

