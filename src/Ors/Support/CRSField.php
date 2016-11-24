<?php namespace Ors\Support;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * CRSField model for ORS CRS search parameters and their meta data.
 * 
 * Some parameters have special meta data, which stores parameter descriptions/translation/...
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
	public static function withArray($obj) {
		$instance = new self($obj['name'], $obj['value']);
		if (!empty($obj['meta']))
			$instance->setMeta($obj['meta']);
		return $instance;
	}
	
	/**
	 * Set meta data
	 * @param mixed $meta
	 * @return \CRS\CRSField
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