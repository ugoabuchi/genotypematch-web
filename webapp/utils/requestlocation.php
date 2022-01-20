<?php
class requestlocation
{

    private $http_proxy_header = null;
    private $ports = null;
    private $invalidIPs = null;
    
    function __construct()
    {
        $this->http_proxy_header = array(
	'HTTP_VIA',
	'VIA',
	'Proxy-Connection',
	'HTTP_X_FORWARDED_FOR',  
	'HTTP_FORWARDED_FOR',
	'HTTP_X_FORWARDED',
	'HTTP_FORWARDED',
	'HTTP_CLIENT_IP',
	'HTTP_FORWARDED_FOR_IP',
	'X-PROXY-ID',
	'MT-PROXY-ID',
	'X-TINYPROXY',
	'X_FORWARDED_FOR',
	'FORWARDED_FOR',
	'X_FORWARDED',
	'FORWARDED',
	'CLIENT-IP',
	'CLIENT_IP',
	'PROXY-AGENT',
	'HTTP_X_CLUSTER_CLIENT_IP',
	'FORWARDED_FOR_IP',
	'HTTP_PROXY_CONNECTION');
        $this->ports = array(80,81,8080,443,1080,6588,3128);
        $this->invalidIPs = array('192.168.1.1', '1.1.1.1', '5.5.5.5', '192.168.8.1');
    }
    
    public function getIpAddress()
    {
        $ipAddress = '';
        if (! empty($_SERVER['HTTP_CLIENT_IP']) && $this->isValidIpAddress($_SERVER['HTTP_CLIENT_IP'])) {
            // check for shared ISP IP
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check for IPs passing through proxy servers
            // check if multiple IP addresses are set and take the first one
            $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipAddressList as $ip) {
                if ($this->isValidIpAddress($ip)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        } else if (! empty($_SERVER['HTTP_X_FORWARDED']) && $this->isValidIpAddress($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->isValidIpAddress($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } else if (! empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->isValidIpAddress($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (! empty($_SERVER['HTTP_FORWARDED']) && $this->isValidIpAddress($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (! empty($_SERVER['REMOTE_ADDR']) && $this->isValidIpAddress($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipAddress;
    }

    public function isValidIpAddress($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        else
        {
            //validate header
            foreach($this->http_proxy_header as $header){
		if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
			return false;
		}
               }
             // validate ports
             foreach($this->ports as $test_port) {
		if(@fsockopen($_SERVER['REMOTE_ADDR'], $test_port, $errno, $errstr, 5)) {
			return false;
		}
               }
               
             if(isset($_SERVER['REMOTE_ADDR']) && is_array($this->invalidIPs)) {
		if(in_array($_SERVER['REMOTE_ADDR'], $ip_blacklist)) {
			return false;
		}
               }
        }
        
        return true;
    }

    public function getLocation($ip)
    {
        $headers = [
    'X-Apple-Tz: 0',
    'X-Apple-Store-Front: 143444,12',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: en-US,en;q=0.5',
    'Cache-Control: no-cache',
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
    'X-MicrosoftAjax: Delta=true'
    ];
        
        $ch = curl_init('http://ipwhois.app/json/' . $ip);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        // Decode JSON response
        $ipWhoIsResponse = json_decode($json, true);
        // Country code output, field "country_code"
        return $ipWhoIsResponse;
    }
    
    //K - Kilometers
    //M - Miles
    //N - Nautical Miles
    
    //default unit is K
    public function distance($lat1, $lon1, $lat2, $lon2, $unit="K") {
            if (((double)$lat1 == (double)$lat2) && ((double)$lon1 == (double)$lon2)) {
              return 0;
            }
            else {
                    $theta = (double)$lon1 - (double)$lon2;
                    $dist = sin(deg2rad((double)$lat1)) * sin(deg2rad((double)$lat2)) +  cos(deg2rad((double)$lat1)) * cos(deg2rad((double)$lat2)) * cos(deg2rad((double)$theta));
                    $dist = acos($dist);
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    $unit = strtoupper($unit);

                    if ($unit == "K") {
                      return round(($miles * 1.609344), 2);
                    } else if ($unit == "N") {
                      return round(($miles * 0.8684), 2);
                    } else {
                      return round($miles, 2);
                    }
          }
        }
}