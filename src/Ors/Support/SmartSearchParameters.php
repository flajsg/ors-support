<?php namespace Ors\Support;

use Illuminate\Database\Eloquent\Collection;

/**
 * SmartSearchParameters class handles search input parameters that are send to search form
 * when user tries to search for particular ORS content.
 * 
 * Parameters are prefiltered and an array is returned for ORS API.
 * 
 * Class also defines some debug defines that can be used for debugging.
 * 
 * 	 SEARCH_DEBUG : (bool) if debug mode is on
 * 
 * 	 SEARCH_TEST : (bool) if test mode is on
 * 
 * 	 SEARCH_DEBUG_OPTS : (string) debug options
 * 
 * 	 SEARCH_TEST_URL : (string) test url
 * 
 * @author Gregor Flajs
 *
 */
class SmartSearchParameters implements CRSFieldInterface {
	
	/**
	 * All valid CRS search fields
	 * @var array
	 */
	public static $valid_fields = [
		// header (ibeid, ctype_id, tab, uniqid, debug, test, debug_opts, test_url)
		'header_*',	
	
		// subaccount id
		'ibeid', 'agid',
		
		// mask attributes
		'ctype_id', 'tab', 'uniqid',
		
		// basic search
		'epc', 'ka1', 'ka2', 'ka3', 'ka4', 'ka5', 'vnd', 'bsd', 'tmin', 'tmax', 'tdc', 'rgcs', 'htn', 'gid', 'stc', 'hon', 'zhc', 'toc', 'ahc', 'zac', 'vpc', 'ctyiso', 'htc', 'lang', 'hsc', 'sid',
	
		// old_ppc
		'old_ppc',
		
		// debug
		'debug', 'test', 'debug_opts', 'test_url',
		
		// facts
		//'bea','ben','air','pet','park','chf','yun','sen','cup','wifi','chl','clb','ani','spt','pol','ipl','sws','spa','wel','wms','way','wth','wcu','wsn','wdt','waa','wbf','wac','wap','sbs','shb','sgl','srd','sae','sfr','stn','sdv','sth','ski',
		'fct_*', 'fct',
		
		// subs (Stypes)
		'sub_*', 'sub',
				
		// filters
		'filter',
		
		// sorting
		'sort',
		
		// offsets
		'offset', 'toffset',
		
		// extras
		'extras',
	];
	
	/**
	 * Valid header fields
	 * @var array
	 */
	protected static $header_fields = [
		'ibeid', 'ctype_id', 'tab', 'uniqid', 'debug', 'test', 'debug_opts', 'test_url'
	];
	
	/**
	 * search parameters
	 * @var array
	 */ 
	private $params = [];
	
	/**
	 * CRS fields
	 * @var Collection|CRSField[]
	 */ 
	protected $crsf;
	
	/**
	 * Header fields
	 * @var array
	 */
	protected $header = [];
	
	/**
	 * Constructor
	 * @param array|string $data
	 * 		Input search parameters
	 */
	public function __construct($attributes) {
		
		// check if $data is json
		if (Common::isJson($attributes))
		    $attributes = json_decode($attributes, true);
		
		$this->params = $this->__parseInputSearchParams($attributes);
		
		$this->crsf = $this->_makeCRSFields($this->params);
		
		$this->header = $this->_makeHeaderFields($this->params);
	}
	
	/**
	 * Static constructor from array
	 * @param array $data
	 * @return \ORS\Helpers\SmartSearchParameters
	 */
	public static function withArray($data) {
		return new self($data); 
	} 
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::find()
	 */
	public function find($name) {
		if ($this->crsf->contains($name))
		    return $this->crsf->find($name);
		return null;
	}

	/**
	 * @see \Ors\Support\CRSFieldInterface::has()
	 */
	public function has($name) {
		return $this->crsf->contains($name);
	}
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::isEmpty()
	 */
	public function isEmpty($name) {
	    return !$this->has($name) || $this->find($name)->isEmpty();
	}
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::all()
	 */
	public function all() {
		return $this->crsf;
	}
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::forget()
	 */
	public function forget($name) {
		unset($this->params[$name]);
		$this->crsf->forget($name);
		return;
	}
	
	/**
	 * Return CRSField object
	 * @param string $name
	 * @return \Ors\Support\CRSField|NULL
	 * @deprecated soon this method will be removed from class
	 */
	public function getCrsf($name) {
		return $this->find($name);
	}
	
	/**
	 * Return true if $name is set in crsf array
	 * @param string $name
	 * 		CRS field name
	 * @return boolean
	 * @deprecated soon this method will be removed from class
	 */
	public function hasCrsf($name) {
		return $this->has($name);
	}
	
	/**
	 * Return a list of CRSFields
	 * @return Collection|CRSField[]
	 * @deprecated soon this method will be removed from class
	 */
	public function getCrsfs() {
		return $this->all();
	}
	
	/**
	 * defaultCrsfs() alias.
	 * @return Collection|CRSField[]
	 */
	public static function defaults() {
		$crsfs = new Collection();
		foreach (self::$valid_fields as $tag)
		    $crsfs->push(new CRSField(array('name' => $tag, 'value' => '')));
		return $crsfs;
		
	}
	
	/**
	 * Make default CRSFields with empty values
	 * @return Collection|CRSField[]
	 * @deprecated soon this method will be removed from class
	 */
	public static function defaultCrsfs() {
		return self::defaults();
	}
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::adults()
	 */
	public function adults() {
		return !$this->has('epc') ? 0 : $this->find('epc')->value;
	}
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::children()
	 */
	public function children() {
		$count = 0;
		if ($this->has('ka1') && $this->find('ka1')->value > 0)
			$count ++;
		if ($this->has('ka2') && $this->find('ka2')->value > 0)
			$count ++;
		if ($this->has('ka3') && $this->find('ka3')->value > 0)
			$count ++;
		
		// sometime soon we will enable more then 3 children to search for, so lets make sure we handle this now :) 
		if (!$this->isEmpty('ka4') && $this->find('ka4')->value > 0)
			$count ++;
		if (!$this->isEmpty('ka5') && $this->find('ka5')->value > 0)
			$count ++;
		return $count;
	}
	
	/**
	 * @see \Ors\Support\CRSFieldInterface::infants()
	 */
	public function infants($inf_age = 2) {
		$count = 0;
		if ($this->has('ka1') && $this->find('ka1')->value > 0 && $this->find('ka1')->value < $inf_age)
			$count ++;
		if ($this->has('ka2') && $this->find('ka2')->value > 0 && $this->find('ka1')->value < $inf_age)
			$count ++;
		if ($this->has('ka3') && $this->find('ka3')->value > 0 && $this->find('ka1')->value < $inf_age)
			$count ++;
		if ($this->has('ka4') && $this->find('ka4')->value > 0 && $this->find('ka4')->value < $inf_age)
			$count ++;
		if ($this->has('ka5') && $this->find('ka5')->value > 0 && $this->find('ka5')->value < $inf_age)
			$count ++;
		return $count;
	}
	
	/**
	 * Parse search Input parameters and return only search parameters.
	 * 
	 * Method also sets few defines for debugging:
	 * 
	 * 	 SEARCH_DEBUG : (bool) if debug mode is on
	 * 
	 * 	 SEARCH_TEST : (bool) if test mode is on
	 * 
	 * 	 SEARCH_DEBUG_OPTS : (string) debug options
	 * 
	 * 	 SEARCH_TEST_URL : (string) test url
	 *
	 * @return array
	 * 		search parameters
	 */
	private function __parseInputSearchParams($params) {
        $data = $params;
        $filtered = array();
        
        if (empty($data)) return $filtered;
        
        // filter the parameters
        foreach ($data as $tag => $value) {
        	
        	if (preg_match('/([a-z]+)\_(.+)/i', $tag, $matches)) {
        		
        		$tag_part = $matches[1];
        		$value_part = $matches[2];
        		
        		if (in_array($tag_part.'_*', $this::$valid_fields))
        			$filtered[$tag_part][]=$value_part;
        		elseif (in_array($tag, $this::$valid_fields))
	        		$filtered[$tag]= $value;
        		
        	}
        	else {
	        	if (in_array($tag, $this::$valid_fields))
	        		$filtered[$tag]= $value;
        	}
        }
        
        // check for tdc
        if (empty($filtered['tdc']) && !empty($filtered['tmin']) && !empty($filtered['tmax']))
        	$filtered['tdc'] = sprintf("%d-%d", $filtered['tmin'], $filtered['tmax']);
        
        // check ka1, ka2, ka3
        if (empty($filtered['ka1']))
        	$filtered['ka1'] = '';
        if (empty($filtered['ka2']))
        	$filtered['ka2'] = '';
        if (empty($filtered['ka3']))
        	$filtered['ka3'] = '';
	
	    // debug/test params
	    $debug = !empty($data['debug']) ? (bool)$data['debug'] : false;
	    $debug_opts = !empty($data['debug_opts']) ? $data['debug_opts'] : '';
	    $test = !empty($data['test']) ? (bool)$data['test'] : false;
	    $test_url = !empty($data['test_url']) ? $data['test_url'] : '';
	
	    // debug mode ON/OFF
	    if (!defined('SEARCH_DEBUG'))
	        define('SEARCH_DEBUG', $debug);
	
	    // debug options
	    if (!defined('SEARCH_DEBUG_OPTS'))
	        define('SEARCH_DEBUG_OPTS', $debug_opts);
	
	    // test mode ON/OFF
	    if (!defined('SEARCH_TEST'))
	        define('SEARCH_TEST', $test);
	
	    // test url
	    if (!defined('SEARCH_TEST_URL'))
	        define('SEARCH_TEST_URL', $test_url);
	    
	    return $filtered;
	}
	
	/**
	 * Create CRSField objects from search parameters
	 * @param array $params
	 * 		filtered search parameters
	 * @return Collection|CRSField[]
	 */
	protected function _makeCRSFields($params) {
		$crsf = new Collection();
		foreach ($params as $tag => $val){
			$crsf->push(new CRSField(array('name' => $tag, 'value' => $val)));
		}
		return $crsf;
	}
	
	protected function _makeHeaderFields($params) {
		$header = [];
		foreach ($params as $tag => $val){
			if (in_array($tag, self::$header_fields))
			$header[$tag]= $val;
		}
		return $header;
	}
	
	/**
	 * Return params
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}
	
	
	/**
	 * @return array
	 */
	public function __toArray() {
		return $this->getParams();
	}
	
	/**
	 * @return string
	 */
	public function __toJson(){
		return json_encode($this->__toArray());
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->__toJson();
	}
}

?>