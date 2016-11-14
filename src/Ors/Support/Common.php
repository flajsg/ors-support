<?php namespace Ors\Support;

define('COMMON_DEBUG_TRACE', 100);
define('ISOLD_DATETIME_FORMAT', 1);
define('ISOLD_MIDNIGHT_EARLY', 2);
define('ISOLD_MIDNIGHT_LATE', 3);

use PhoneNumberUtil;
use Date;
use DateTime;
use DateTimeZone;
use Lang;
use Carbon;

/**
 * ORS Common class. 
 * 
 * This class has common static methods that are used through entire framework.
 * 
 * For example: phone, price, date, dateTime, pre, ...
 * 
 * @author Gregor Flajs
 *
 */
class Common {
	
	/**
	 * Show phone number in predefined format
	 *
	 * @param string $phone
	 * @param string $country_iso
	 * 		country iso code (2)
	 * @return string
	 */
	public static function phone($phone, $country_iso = 'SI') {
	    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
	    try {
	        $phoneProto = $phoneUtil->parse($phone, strtoupper($country_iso));
	        return $phoneUtil->format($phoneProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
	    }
	    catch (\libphonenumber\NumberParseException $e) {
	        return $phone;
	    }
	    return $phone;
	}
	
	/**
	 * Display price in fix format
	 * @param float $price
	 */
	public static function price($price, $decimals = 2, $dec_point = '.'){
		$thousands_sep = '';
		$price = empty($price) ? 0 : $price;
		$nbr = number_format($price, $decimals, $dec_point, $thousands_sep);
		return $nbr;
	}
	
	/**
	 * Return a number as a currency using local format (EUR).
	 * 
	 * @param float $number
	 * @param bool $national
	 * 		if true then this will return format with national symbol (€), 
	 * 		if false then this will return format with international currency sign (EUR).
	 * @return float
	 */
	public static function priceEuro($number, $national = true){
		setlocale(LC_MONETARY, 'sl_SI.UTF-8');
		return $national ? money_format("%n", $number) : money_format("%i", $number);
	}
	
	/**
	 * Converts string to a float value.
	 * Commas are replaced with dots.
	 * @example
	 * 		toFloat(1,1, 5) => will return 1.10000
	 *  
	 * @param string $string
	 * @param int $decimals
	 * 		number of decimals to round the number
	 */
	public static function toFloat($string, $decimals = 5) {
		return round(str_replace(',', '.', $string), $decimals);
	}
	
	/**
	 * Show date in predefined format
	 * @param string $date
	 * @param string $format
	 * 		datetime format string
	 * @return string
	 */
	public static function date($date, $format = 'd.m.Y') {
		if (strlen($date) == 6){
		    preg_match('/(?<d>\d\d)(?<m>\d\d)(?<y>\d\d)/', $date, $matches);
		    $y = $matches['y'] > 64 ? $matches['y'] + 1900 : $matches['y'] + 2000;
		    $date = sprintf("%s.%s.%s", $matches['d'], $matches['m'], $y);
		} 
		elseif (strlen($date) == 8 && !preg_match('/\./', $date)){
		    preg_match('/(?<d>\d\d)(?<m>\d\d)(?<y>\d\d\d\d)/', $date, $matches);
		    $date = sprintf("%s.%s.%s", $matches['d'], $matches['m'], $matches['y']);
		}
		
	    return strtotime($date) <= 0 ? '' : Date::make($date)->format($format);
	}
	
	/**
	 * Show date-time in predefined format
	 * @param string $date
	 * @param string $format
	 * 		datetime format string
	 * @return string
	 */
	public static function dateTime($date, $format = 'd.m.Y H:i:s') {
	    return strtotime($date) <= 0 ? '' : Date::make($date)->format($format);
	}
	
	/**
	 * Convert date to age.
	 * @param string $date
	 * 		any date format
	 */
	public static function date2age($date) {
		$tz  = new DateTimeZone('Europe/Brussels');
		return DateTime::createFromFormat('d.m.Y', Common::date($date), $tz)
			->diff(new DateTime('now', $tz))
			->y;
	}
	
	/**
	 * Format time string to proper H:i format
	 * @param string $time
	 */
	public static function toTime($time) {
		if (strlen($time) == 4)
			return substr($time, 0, 2).':'.substr($time,2,4);
		if (strlen($time) == 6)
			return substr($time, 0, 2).':'.substr($time,2,4).':'.substr($time,4,6);
		return $time;
	}
	
	/**
	 * Get GMT timestamp from datetime
	 * @param string $date
	 * @return string
	 */
	public static function GMTDateTime($date) {
		return gmdate("Y-m-d\TH:i:s\Z", strtotime($date));
	}
	
	/**
	 * Calculate how many days are between two dates
	 * @return int
	 */
	public static function daysBetween($date_from, $date_to) {
		$date_from = date('Y-m-d H:i:s', strtotime($date_from));
		$date_to = date('Y-m-d H:i:s', strtotime($date_to));
		$dt_start = Carbon::createFromFormat('Y-m-d H:i:s', $date_from);
		$dt_end   = Carbon::createFromFormat('Y-m-d H:i:s', $date_to);
		return $dt_start->diffInDays($dt_end, false);
		/*
		// previous method to calc days
		$date1 = new DateTime($date_from);
		$date2 = new DateTime($date_to);
		
		$diff = $date2->diff($date1)->format("%a");
		
		return $date2 < $date1 ? -$diff : $diff;
		*/
	}
	
	/**
	 * Calculate how many hours are between two dates
	 * @return int
	 */
	public static function hoursBetween($date_from, $date_to) {
		$date_from = date('Y-m-d H:i:s', strtotime($date_from));
		$date_to = date('Y-m-d H:i:s', strtotime($date_to));
	    $dt_start = Carbon::createFromFormat('Y-m-d H:i:s', $date_from);
	    $dt_end   = Carbon::createFromFormat('Y-m-d H:i:s', $date_to);
	    return $dt_start->diffInHours($dt_end, false);
	}
	
	/**
	 * Return TRUE if $date is in the past.
	 * If today = $date then this method returns FALSE.
	 * @param string $date
	 * 		a date in any format
	 * @param int $options
	 * 		additional options to check:
	 * 		- ISOLD_DATETIME_FORMAT : check $date date&time values
	 * 		- ISOLD_MIDNIGHT_EARLY : check if $date < then midnight today (00:00:00)
	 * 		- ISOLD_MIDNIGHT_LATE : check if $date < then late midnight today (23:59:59) 
	 * @return boolean
	 */
	public static function isOldDate($date, $options = 0){
		if ($options == ISOLD_DATETIME_FORMAT)
			return date('Y-m-d H:i:s', strtotime($date)) < date('Y-m-d H:i:s');
		if ($options == ISOLD_MIDNIGHT_EARLY)
			return date('Y-m-d H:i:s', strtotime($date)) < date('Y-m-d 00:00:00');
		if ($options == ISOLD_MIDNIGHT_LATE)
			return date('Y-m-d H:i:s', strtotime($date)) < date('Y-m-d 23:59:59');
		return date('Y-m-d', strtotime($date)) < date('Y-m-d');
	}
	
	/**
	 * Increase date by the number of days/months/years.
	 * If you put negative number, a date will be decreased.
	 * @param string $date
	 * @param int $d
	 * @param int $m
	 * @param int $y
	 * @param string $format
	 * @return string
	 */
	public static function incDate($date, $d = 0, $m = 0, $y = 0, $format = 'd.m.Y') {
		return date($format, mktime(23,59,59, date('m', strtotime($date)) + $m, date('d', strtotime($date)) + $d, date('Y', strtotime($date)) + $y));
	}
	
	/**
	 * Return TRUE if keywords ($keywords) are found in string ($str). 
	 * FALSE is returned if keywords are not found. 
	 * 
	 * @param string $str
	 * 		haystack string
	 * @param string|array $keywords
	 * 		a list of keywords. If this is string then a $delimiter is used to make a list.
	 * @param string $delimiter
	 * 		a keywords delimiter when $keywords is string 
	 * @param string $operator
	 * 		(OR|AND) logic operator (default: 'OR'). 
	 * 		When operator 'OR' is used, then function returns TRUE if at least one keyword is found. 
	 * 		When operator 'AND' is used, then function returns TRUE if all keywords are found. 
	 * @return boolean
	 */
	public static function containsKeywords($str, $keywords, $delimiter = ' ', $operator = 'OR') {
		// array of keywords
		$keywords = is_array($keywords) ? $keywords : explode($delimiter, trim($keywords));
		array_walk($keywords, create_function('&$val', '$val = trim($val);')); 
		
		$operator = strtoupper($operator);
		
		if ($operator == 'OR') {
			foreach($keywords as $key) {
			    if (mb_stripos($str, $key, 0, 'UTF-8') !== false) return true;
			}
			return false;
		} 
		elseif ($operator == 'AND') {
			$trues = 0;
			foreach($keywords as $key) {
			    if (mb_stripos($str, $key, 0, 'UTF-8') !== false) $trues++;
			}
			return $trues == count($keywords);
		}
		
		return false;
	}
	
	/**
	 * Return $data in &lt;PRE&gt; tags
	 * @param string|array $data
	 * 		mixed data to display with print_r()
	 * @param string $title
	 * 		optional title to display at the top
	 * @param int $options
	 * 		COMMON_DEBUG_TRACE : if true, then a stack trace is also displayed
	 * @return string
	 */
	public static function pre($data, $title = '', $options = 0) {
		if (!empty($title))
		    $title = sprintf("[%s]:\n", $title);
		$divider = str_repeat('=', 80)."\n";
		
		$trace = '';
		if ($options == COMMON_DEBUG_TRACE) {
			$trace_array = Common::debug_string_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4, 2);
			$trace = implode("\n", $trace_array)."\n";
		}
		
		return sprintf("<pre>%s%s%s%s%s</pre>", $divider, $trace,$title, print_r($data, true), $divider);
	}
	
	/**
	 * Output $data in &lt;PRE&gt; tags
	 * @param string|array $data
	 * 		mixed data to display with print_r()
	 * @param string $title
	 * 		optional title to display at the top
	 * @param int $options
	 * 		COMMON_DEBUG_TRACE : if true, then a stack trace is also displayed
	 * @return string
	 */
	public static function ppre($data, $title = '', $options = 0) {
		if (!empty($title))
		    $title = sprintf("[%s]:\n", $title);
		$divider = str_repeat('=', 80)."\n";
		
		$trace = '';
		if ($options == COMMON_DEBUG_TRACE) {
			$trace_array = Common::debug_string_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4, 2);
			$trace = implode("\n", $trace_array)."\n";
		}
		
		printf("<pre>%s%s%s%s%s</pre>", $divider, $trace,$title, print_r($data, true), $divider);
	}
	
	/**
	 * Return $data in &lt;PRE&gt; tags only if SEARCH_DEBUG=TRUE
	 * @param string|array $data
	 * 		mixed data to display with print_r()
	 * @param string $title
	 * 		optional title to display at the top
	 * @return string
	 */
	public static function preDebug($data, $title = '') {
		if (defined('SEARCH_DEBUG') && SEARCH_DEBUG)
			return Common::pre($data, $title, COMMON_DEBUG_TRACE);
		return '';
	}
	
	/**
	 * Output $data in &lt;PRE&gt; tags only if SEARCH_DEBUG=TRUE
	 * @param string|array $data
	 * 		mixed data to display with print_r()
	 * @param string $title
	 * 		optional title to display at the top
	 * @return string
	 */
	public static function ppreDebug($data, $title = '') {
		if (defined('SEARCH_DEBUG') && SEARCH_DEBUG)
			Common::ppre($data, $title, COMMON_DEBUG_TRACE);
	}
	
	/**
	 * Return debug stack trace (without first element, which is redundant)
	 * @return array
	 */
	public static function debug_string_backtrace($options = 0, $limit = 0, $reduntant = 1) {
		$trace_array = debug_backtrace($options, $limit);
		$trace_ret = array();
	
		foreach ($trace_array as $k => $tr) {
			if ($k > $reduntant-1) {
				$trace_ret []= sprintf("#[%d] %s\%s called at %s:%d", $k, @$tr['class'], @$tr['function'], @$tr['file'], @$tr['line']);
			}
		}
		
		return $trace_ret;
	}
	
	/**
	 * Test if string is JSON format
	 * @param mixed $string
	 * @return boolean
	 */
	public static function isJson($string) {
		if (is_array($string))
			return false;
	    json_decode($string);
	    return (json_last_error() == JSON_ERROR_NONE);
	}
	
	/**
	 * Decode Json strings. 
	 * This function also does some cleaning: removes trailing commas, removes newlines, ... 
	 * @param string $json
	 * @param bool $assoc
	 * @return array
	 */
	public static function json_decode_nice($json, $assoc = FALSE){
	    $json = str_replace(array("\n","\r"),"",$json);
	    $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json);
	    $json = preg_replace('/(,)\s*}$/','}',$json);
	    return json_decode($json,$assoc);
	}
	
	/**
	 * Explode and trim string to array
	 * @param string $string
	 * @param string $delimiter
	 * 		delimiter in $string (default: ',' )
	 * @return array
	 */
	public static function extrim($string, $delimiter = ',') {
		if (empty($string))
			return array();
	    $arr = array_map('trim', explode($delimiter, $string));
	    $arr = array_filter($arr);
	    return $arr;
	}

	/**
	 * This function return values of array, where a value key starts with $prefix.
	 * @param array $array
	 * 		associated input array
	 * @param string $prefix
	 * 		ie: "data_"
	 * @return array
	 * 		only values that have a key with prefix $prefix are returned.
	 */
	public static function mapPreffixedArray($array, $prefix = '') {
		$ret = array();
		
		foreach ($array as $k => $v) {
			if (preg_match('/^'.$prefix.'/', $k))
				$ret[str_replace($prefix, "", $k)] = $v;
		}
		return $ret;
	}
	
	/**
	 * Create time elapsed string from timestamp
	 * @param int $ptime
	 * 		date timestamp
	 * @return string
	 * 		return "time ago" string
	 */
	public static function time_elapsed_string($ptime) {
	    $etime = time() - $ptime;
	
	    if ($etime < 1)
	    {
	        return '0 '.Lang::get('base.seconds');
	    }
	
	    $a = array( 365 * 24 * 60 * 60  =>  Lang::get('base.year'),
	        30 * 24 * 60 * 60  =>  Lang::get('base.month'),
	        24 * 60 * 60  =>  Lang::get('base.day'),
	        60 * 60  =>  Lang::get('base.hour'),
	        60  =>  Lang::get('base.minute'),
	        1  =>  Lang::get('base.second')
	    );
	    $a_plural = array( Lang::get('base.year')   => Lang::get('base.years'),
	        Lang::get('base.month')  => Lang::get('base.months'),
	        Lang::get('base.day')  => Lang::get('base.days'),
	        Lang::get('base.hour')  => Lang::get('base.hours'),
	        Lang::get('base.minute')  => Lang::get('base.minutes'),
	        Lang::get('base.second')  => Lang::get('base.seconds'),
	    );
	
	    foreach ($a as $secs => $str)
	    {
	        $d = $etime / $secs;
	        if ($d >= 1)
	        {
	            $r = round($d);
	            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str);
	        }
	    }
	}
	
	/**
	 * Create an unique hash string with md5() + uniqid()
	 * @return string
	 * 		md5 unique hash is returned
	 */
	public static function makeUniqueHash(){
		return md5(uniqid('', true));
	}
	
	/**
	 * Convert percent to rating scale
	 * @param float $percent
	 * @param int $scale
	 * 		upper scale value
	 * @return int
	 */
	public static function percent2rating($percent, $scale = 5) {
		return ceil($percent / 100 * $scale);
	}

	/**
	 * Convert rating back to percent range
	 * @param int $rating
	 * @param int $scale
	 * 		upper scale value
	 * @return string
	 * 		a percent range is returned. Ie. for rating=2 > "21-40
	 */
	public static function rating2percent($rating, $scale = 5) {
		$min = 100/$scale * ($rating-1) + 1;
		$max = 100/$scale * ($rating);
		return sprintf("%d-%d", $min, $max);
	}
	
	/**
	 * Return bootstrap color class depending on rating (1-5).
	 * @param int $real_rating
	 * @return string|NULL
	 * 		NULL is returned if rating is not set or if it is greater then 5
	 */
	public static function ratingColor($real_rating) {
	    if (!empty($real_rating)) {
	        switch ($real_rating) {
	        	case 1:
	        	case 2:
	        	    return 'danger';
	        	case 3:
	        	case 4:
	        	    return 'warning';
	        	case 5:
	        	    return 'system';
	        }
	    }
	    return null;
	}
	
	/**
	 * Check if remote file exist.
	 * @param string $url
	 * @param int $timeOut
	 * @return boolean
	 */
	public static function isRemoteFile($url, $timeOut = 5) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    // don't download content
	    curl_setopt($ch, CURLOPT_NOBODY, 1);
	    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeOut/2)	;
        curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
	    return curl_exec($ch)!==FALSE;
	}
	
	/**
	 * Return ORS Icon representing Giata Fact.
	 * @param string $code
	 * 		giata fact code (air, wifi, bea, ...)
	 * @return \OAM\OAMIcon|NULL
	 * 		NULL is returned if there is no Icon available for $code
	 */
	public static function factIcon($code) {
	    switch ($code) {
	    	case 'air':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-snowflake', 'name' => Lang::get('oam_objects.air:object')));
	    	case 'wifi':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-wifi', 'name' => Lang::get('oam_objects.wifi:object')));
	    	case 'bea':
	    	case 'ben':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-beach_umbrella', 'name' => Lang::get('oam_objects.bea:object')));
	    	case 'pol':
	    	case 'ipl':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-pool', 'name' => Lang::get('oam_objects.pol:object')));
	    	case 'whc':
	    	    return new \OAM\OAMIcon(array('icon' => 'fa fa-wheelchair', 'name' => Lang::get('oam_objects.whc:object')));
	    	case 'spt':
	    	case 'sws':
	    	case 'shb':
	    	case 'sgl':
	    	case 'srd':
	    	case 'sae':
	    	case 'sfr':
	    	case 'stn':
	    	case 'sdv':
	    	case 'sth':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-soccer_ball', 'name' => Lang::get('oam_objects.spt:object')));
	    	case 'spa':
	    	case 'wel':
	    	case 'wms':
	    	case 'way':
	    	case 'wth':
	    	case 'wcu':
	    	case 'wsn':
	    	case 'wdt':
	    	case 'waa':
	    	case 'wbf':
	    	case 'wac':
	    	case 'wap':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-heart_empty', 'name' => Lang::get('oam_objects.wel:object')));
	    	case 'pet':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-dog', 'name' => Lang::get('oam_objects.pet:object')));
	    	case 'park':
	    	    return new \OAM\OAMIcon(array('icon' => 'glyphicons glyphicons-car', 'name' => Lang::get('oam_objects.park:object')));
	    	case 'chf':
	    	    return new \OAM\OAMIcon(array('icon' => 'fa fa-smile-o', 'name' => Lang::get('oam_objects.chf:object')));
	
	    	default:
	    	    return null;
	    }
	}
	
	/**
	 * Pad array with zeros if needed so it is easier to iterate trough
	 *  @param $input
	 *  @return Fixed array
	 *  @Author Uroš Knupleš
	 **/
	public static function padZeroArray($array) {
	    if (is_array($array))
	    {
	        reset($array);
	        if (key($array) === 0) return $array;
	    }
	    return array($array);
	}
	
	/**
	 * Remove category (stars) from object name.
	 * 
	 * @example 
	 * 		$name = "Hotel Name 4*";
	 * 		output = "Hotel Name"
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function removeObjectCategory($name) {
		return preg_replace('/\s+\d\*+/', '', $name);
	}
	
	/**
	 * This method replaces dashes with underscores
	 * @param array $data
	 * @return array
	 */
	public static function _dashToUnderscore($data) {
	    $res = array();
	    foreach ($data as $tag => $val)
	        $res[str_replace('-', '_', $tag)] = $val;
	    return $res;
	}
	
	/**
	 * This method replaces underscores with dashes
	 * @param array $data
	 * @return array
	 */
	public static function _underscoreToDash($data) {
	    $res = array();
	    foreach ($data as $tag => $val)
	        $res[str_replace('_', '-', $tag)] = $val;
	    return $res;
	}
	
	/**
	 * Filter an array by keys.
	 * 
	 * Return only key-value pairs, where keys are found in $only_keys array. 
	 * 
	 * @param array $array
	 * 		source array
	 * @param array $only_keys
	 * 		filtering keys
	 * @return array
	 */
	public static function array_only($array, $only_keys) {
		$ret_array = array();
		foreach($array as $k => $v) {
			if (in_array($k, $only_keys)) $ret_array[$k] = $v; 
		}
		return $ret_array;
	}
}

?>