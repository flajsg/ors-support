<?php namespace Ors\Support\Traits;

use	Ors\Support\Common;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

/**
 * This trait uses Curl to send requests
 * 
 * @author Gregor Flajs
 *
 */
trait CurlPost {

	public function getSessionCookie() {return $_COOKIE;}
	
	/**
	 * JSON request with Curl.
	 * @param string $url
	 * @param string $request
	 * @param int $timeOut
	 * @param array $cookies
	 * 		cookies to send in header
	 * 
	 * @return json|bool
	 *	FALSE is returned if Curl failed
	 */
	public function JSONCurlPost($url , $request = '', $timeOut = 60, $cookies = null) {
	
	    if($ch = curl_init()) {
	
	    	$header = array(
	        	'Content-Type: application/json;charset=UTF-8'
	        );
	    	
	    	if ($cookies && is_array($cookies)) {
	    		$tmp = array();
	    		foreach ($cookies as $ck => $cv) $tmp[]=sprintf("%s=%s", $ck, $cv);
	    		$header[]= sprintf("Cookie: %s", implode('; ', $tmp));
	    		//curl_setopt($ch, CURLOPT_COOKIE, implode(';', $tmp));
	    	}
	    	
	        // you need to send JSON object
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	        if($timeOut != -1) {
	            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeOut/2)	;
	            curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
	        }
	        //curl_setopt($ch, CURLOPT_VERBOSE, true);
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	        $content = curl_exec($ch);
	
	        curl_close($ch);
	
	        return $content;
	    }
	    else return false;
	}
	
	/**
	 * Regular post request with Curl.
	 * @param string $url
	 * @param string $request
	 * @param int $timeOut
	 * @param array $cookies
	 * 		cookies to send in header
	 *
	 * @return string|bool
	 *	FALSE is returned if Curl failed
	 */
	public function curlPost($url , $request, $timeOut = 60, $cookies = null, $show_headers = false) {
	    
	    if($ch = curl_init()) {
	        
	        if ($cookies && is_array($cookies)) {
	            $tmp = array();
	            foreach ($cookies as $ck => $cv) $tmp[]=sprintf("%s=%s;", $ck, $cv);
	            $header[]= sprintf("Cookie: %s", implode(' ', $tmp));
	        }
	        
	        if ($header)
	        	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	        else
	        	curl_setopt($ch, CURLOPT_HEADER, (int)$show_headers);
	        
	        
	        if($timeOut != -1) {
	            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeOut/2)	;
	            curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
	        }
	        
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024);
	        //curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	        curl_setopt ($ch, CURLOPT_POST, 1);
	        curl_setopt ($ch, CURLOPT_POSTFIELDS, $request);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_VERBOSE, 0);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	        
	        $content = curl_exec($ch);
	        
	        
	        if ($show_headers) {
		        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		        $header = substr($content, 0, $header_size);
		        $body = substr($content, $header_size);
		        
		        var_dump($header);
		        var_dump($body);
	        }
	        
	        /*
	        if($content === false) {
	        	var_dump( curl_error($ch));
	        }
	        */
	        
	        curl_close($ch);
	        
	        
	        return $content;
	    }
	    else return false;
	}
}