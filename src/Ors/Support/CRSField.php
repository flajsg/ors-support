<?php namespace Ors\Support;

use Illuminate\Database\Eloquent\Model as Eloquent;

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