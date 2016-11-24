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
class SmartSearchParameters {
	
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
		'epc', 'ka1', 'ka2', 'ka3', 'vnd', 'bsd', 'tmin', 'tmax', 'rgcs', 'htn', 'gid', 'stc', 'hon', 'zhc', 'toc', 'ahc', 'zac', 'vpc', 'ctyiso', 'htc', 'lang', 'hsc', 'sid',
	
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
	public static $header_fields = [
		'ibeid', 'ctype_id', 'tab', 'uniqid', 'debug', 'test', 'debug_opts', 'test_url'
	];
	
	/**
	 * search parameters
	 * @var array
	 */ 
	private $params = [];
	
	/**
	 * meta search data
	 * @var SmartAutocompleteTab[]
	 */
	private $meta = [];
	
	/**
	 * CRS fields
	 * @var Collection|CRSField[]
	 */ 
	public $crsf;
	
	/**
	 * Header fields
	 * @var array
	 */
	public $header = [];
	
	/**
	 * Constructor
	 * @param array|json $data
	 * 		Input search parameters
	 */
	public function __construct($data) {
		
		// check if $data is json
		if (Common::isJson($data))
		    $data = json_decode($data, true);
		
		$this->params = $this->__parseInputSearchParams($data);
		
		$this->meta = $this->_makeMeta($this->params);
		
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
	 * Return CRSField object
	 * @param string $field
	 * @return CRSField|NULL
	 */
	public function getCrsf($field) {
		if ($this->crsf->contains($field)) 
			return $this->crsf->find($field);
		return null;
	}
	
	/**
	 * Return true if $field is set in crsf array
	 * @param string $field
	 * 		CRS field name
	 * @return boolean
	 */
	public function hasCrsf($field) {
		return $this->crsf->contains($field);
	}
	
	/**
	 * Rturn a list of CRSFields
	 * @return Collection|CRSField[]
	 */
	public function getCrsfs() {
		return $this->crsf;
	}
	
	
	/**
	 * Make default CRSFields with empty values
	 * @return Collection|CRSField[]
	 */
	public static function defaultCrsfs() {
		$crsfs = new Collection();
		foreach (self::$valid_fields as $tag)
			$crsfs->push(new CRSField(array('name' => $tag, 'value' => '')));
		return $crsfs;
	}
	
	/**
	 * Return a number of adults
	 * @return int
	 */
	public function adults() {
		return $this->getCrsf('epc')->value;
	}
	
	/**
	 * Return a number of children (including infants)
	 * @return int
	 */
	public function children() {
		$count = 0;
		if ($this->getCrsf('ka1')->value > 0)
			$count ++;
		if ($this->getCrsf('ka2')->value > 0)
			$count ++;
		if ($this->getCrsf('ka3')->value > 0)
			$count ++;
		return $count;
	}
	
	/**
	 * Return a number of infants (use $inf_age to determine infant max age)
	 * @return int
	 */
	public function infants($inf_age = 2) {
		$count = 0;
		if ($this->getCrsf('ka1')->value > 0 && $this->getCrsf('ka1')->value < $inf_age)
			$count ++;
		if ($this->getCrsf('ka2')->value > 0 && $this->getCrsf('ka1')->value < $inf_age)
			$count ++;
		if ($this->getCrsf('ka3')->value > 0 && $this->getCrsf('ka1')->value < $inf_age)
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
	 * Handler for creation of meta datas
	 * @param array $params
	 * 		filtered search parameters
	 * @return array
	 */
	private function _makeMeta($params) {
		$meta = array();
		foreach ($params as $tag => $val) {
			if (method_exists($this, '_meta_'.$tag))
				$meta[$tag] = $this->{'_meta_'.$tag}($val);
			else
				$meta[$tag] = array();
		}
		return $meta;
	}
	
	/**
	 * Create CRSField objects from search parameters
	 * @param array $params
	 * 		filtered search parameters
	 * @return Collection|CRSField[]
	 */
	private function _makeCRSFields($params) {
		$crsf = new Collection();
		foreach ($params as $tag => $val){
			$crsf->push(new CRSField(array('name' => $tag, 'value' => $val, 'meta' => $this->meta[$tag])));
		}
		return $crsf;
	}
	
	private function _makeHeaderFields($params) {
		$header = [];
		foreach ($params as $tag => $val){
			if (in_array($tag, self::$header_fields))
			$header[$tag]= $val;
		}
		return $header;
	}
	
	/**
	 * Return Meta data
	 * @return array
	 */
	public function getMeta() {
		return $this->meta;
	}
	
	/**
	 * Return params
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}
	
	public function forget($name) {
		echo $name;
		unset($this->params[$name]);
		$this->crsf->forget($name);
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