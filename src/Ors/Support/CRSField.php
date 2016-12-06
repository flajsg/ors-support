<?php namespace Ors\Support;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * If you are construction your own CRS Field handler, then it has to implement this interface.
 * 
 * Or you can use our default CRS Field handler SmartSearchParameters.
 * 
 * @author Gregor Flajs
 *
 */
interface CRSFieldInterface {
	
	/**
	 * Find and return specific CRS field by name
	 * @param string $name
	 * 		field name
	 * @return \Ors\Support\CRSField
	 */
	public function find($name);
	
	/**
	 * Return true if CRS field exists
	 * @param string $name
	 * 		field name
	 * @return boolean
	 */
	public function has($name);
	
	/**
	 * Return true if CRS field is null or an empty string
	 * @param string $name
	 * 		field name
	 * @return boolean
	 */
	public function isEmpty($name);
	
	/**
	 * Return all CRS fields
	 * @return Collection|CRSField
	 */
	public function all();
	
	/**
	 * Remove CRS field.
	 * @param string $name
	 * @return void
	 */
	public function forget($name);
	
	/**
	 * Return array representation of CRS fields.
	 * @return array
	 */
	public function __toArray();
	
	/**
	 * Return JSON representation of CRS fields.
	 * @return string
	 */
	public function __toJson();
	
	/**
	 * Return a number of adults from the search parameters
	 * @return int
	 */
	public function adults();
	
	/**
	 * Return a number of children (including infants)
	 * @return int
	 */
	public function children();
	
	/**
	 * Return a number of infants (use $inf_age to determine infant max age)
	 * @param int $inf_age
	 * 		max infant age.
	 * @return int
	 */
	public function infants($inf_age = 2);
}

/**
 * CRSField model for ORS CRS search parameters and their meta data.
 * 
 * Some parameters can have special "meta data" (descriptions/translation/...).
 * 
 * @author Gregor Flajs
 *
 */
class CRSField extends Eloquent{
	
	/**
	 * Attributes for this model
	 * @var array
	 */
	protected $fillable = [
		'name', 'value', 'meta'
	];
	
	/**
	 * Primary key
	 * @var int
	 */
	protected $primaryKey = 'name';
	
	/**
	 * Constructor from array
	 * @param array $obj
	 * 		array object with valid information to create new CRSField instance
	 */
	public static function withArray($array) {
		$instance = new self($array['name'], $array['value']);
		if (!empty($array['meta']))$instance->setMeta($array['meta']);
		return $instance;
	}
	
	/**
	 * Return true if value is an empty or null string.
	 * @return boolean
	 */
	public function isEmpty() {
		return $this->value == '' || is_null($this->value); 
	}
	
	/**
	 * Set meta data
	 * @param mixed $meta
	 * @return $this
	 */
	public function setMeta($meta){
		$this->meta = $meta;
		return $this;
	}
	
	public function __toArray() {
		return array(
			'name' => $this->name,	
			'value' => $this->value,	
			'meta' => $this->meta,	
		);		
	}
	
	public function __toJson() {
		return json_encode($this->__toArray());
	}
	
	public function __toString() {
		return $this->__toJson();
	}
}