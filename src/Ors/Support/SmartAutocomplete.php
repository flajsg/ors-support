<?php namespace Ors\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * SmartAutocomplete class prepares a list of items for jquery-autocomplete function.
 * 
 * In order for SmartAutocomplete to work, objects in $collection must implement SmartAutocompleteInterface,
 * or you can use $callback to set the following values (value,label,icon,tab)
 * 
 * <code>
 * #example: how to use callback
 * new SmartAutocomplete($collection, function($item) {
 * 		return array(
 * 			'value' => $item->id
 * 			'label' => $item->id.' '.$item->name
 * 			'icon' => 'fa fa-tag',
 * 			'tab' => $item->name,
 * 		);
 * });
 * </code>
 * 
 * 
 * @author Gregor Flajs
 *
 */
class SmartAutocomplete implements ArrayableInterface {

    protected $list = [];
    
    /**
     * When there are no results this is an error that will be displayed
     * @var string
     */
    protected $error = '';

    /**
     * Items to display in Autocomplete
     * @var Collection
     */
    protected $items;
    
    /**
     * This callback function is used for mapping: 
     * array(icon, lable and value) for each member of $items.
     * Callback provides one attribute (Collection).
     * @var callable
     */
    protected $ac_callback;
    
    
    /**
     * Construct SmartAutocomplete object with Eloquent Collection
     * @param Collection $collection
     * @param callable $callback
     */
    public function __construct($collection, $callback = null) {
    	$this->list = array();
    	$this->items = $collection;
    	$this->ac_callback = $callback;
        $this->__makeAutocomplete($collection);
        
    }

    /**
     * Create Object with empty collection
     * @return \ORS\Helpers\SmartAutocomplete
     */
    public static function withNoResults($error = '') {
    	$instance = new self(new Collection());

    	if (!empty($error)) $instance->error = $error; 
    	
    	$instance->_makeErrorList();

    	return $instance;
    }
    
    /**
     * Set autocomplete callback
     * @param callable $callback
     * 		a callback function to map autocomplete values (icon, lable, value) agains each item
     */
    public function setACCallback($callback) {
        $this->ac_callback = $callback;
        $this->__makeAutocomplete($this->items);
    }
    
    private function _makeErrorList() {
    	$this->list= array([
    	    'value' => null,
    	    'error' => true,
    	    'label' => $this->error,
    	    'icon' => 'fa fa-warning',
    	    'tab' => null
    	]);
    }
    
    /**
     * This method is used to add Eloquent Collection items in private $list array
     * which is then used to return autocomplete data
     * @param Collection $collection
     */
    private function __makeAutocomplete($collection) {
    	if (empty($collection) || $collection->isEmpty()) {
    		return;
    	}
    	foreach ($collection as $model) {
    		$this->push($model);
    	}
    }
    
    /**
     * Push $model data into private $list array.
     * 
     * $model must have the following attributes in for autocomplete to work:
     * 
     * 	SmartAutocompleteKey : a key value
     * 
	 * 	SmartAutocompleteTitle : display item in the list
	 * 
	 *  SmartAutocompleteTabTitle : title for a tab (if this is empty then SmartAutocompleteTitle is used)
	 * 
 	 * 	SmartAutocompleteIcon : css class for icon to display in front of each item (this can be empty)
 	 * 
 	 * OR since 2016-07-14, this function will use ac_callback to set values
 	 * 
     * @param Eloquent|Object $model
     */
    public function push($model) {  

    	if (!empty($this->ac_callback) && $this->ac_callback instanceof \Closure) {
    		$callback = $this->ac_callback;
   		    $this->list[]= $callback($model);
    	}
    	elseif ($model instanceof SmartAutocompleteInterface) {
	        $this->list[]= array(
	            'value' => $model->smart_autocomplete_key,
	            'label' => $model->smart_autocomplete_title,
	            'icon' => $model->smart_autocomplete_icon,
	        	'tab' => !empty($model->smart_autocomplete_tab_title) ? $model->smart_autocomplete_tab_title : $model->smart_autocomplete_title
	        );
    	}
    }
    
    /**
     * Adds another collection data in the current AC list.
     * All information are appended to the end of the current list.
     * @param Collection $collection
     */
    public function add($collection) {
    	$this->__makeAutocomplete($collection);
    }
    
    /**
     * @return array
     */
    public function __toArray() {
    	if (empty($this->list)) {
    		$this->error = 'N results';
    		$this->_makeErrorList();
    	}
        return $this->list;
    }
    
    /**
     * @return array
     */
    public function toArray() {
        return $this->__toArray();
    }

    /**
     * @return string
     */
    public function __toJson() {
        return json_encode($this->__toArray());
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->__toJson();
    }
    
    public function isEmpty() {
    	return empty($this->list);
    }
}


/**
 * Smart Autocomplete Tab information.
 * 
 * Use this model when creating those little tabs with autocomplete function. 
 * This model has all the required information, so you can display a JQuery autocomplete tab.
 * 
 * For instance if you wish to create tabs using a collection of objects, you can do something like this:
 * 
 * <code>
 *  // In PHP create a collection of tabs
 *  $tabs = \ORS\Helpers\SmartAutocompleteTab::withCallback(User::all(), function($item){
 *  	return array('value' => $item->id, 'label' => $item->name, 'tab' => '');
 *  });
 *  
 *  // In HTML/JS
 *  var ac = new SmartAutocomplete('#users', {
 *    		ajaxUrl : 'url',
 *    		spinner: true,
 *    		hidden : true,
 *    		hiddenName : 'users',
 *    		tags : true,
 *    		tagsContainer: '#users-tags',
 *    		defaultTags : <?=$tabs->__toString()?>
 *    	});
 * </code>
 * 
 * @author Gregor Flajs
 */

class SmartAutocompleteTab implements ArrayableInterface {
	
	// tab value
	private $value;
	
	// tab title
	private $label;
	
	// tab alt title
	private $tab;
	
	public function __construct($value, $label, $tab = '') {
		$this->value = $value;
		$this->label = $label;
		$this->tab = $tab;
	}
	
	/**
	 * Create a collection of tabs, with callback function.
	 * 
	 * @param \Illuminate\Support\Collection $collection
	 * @param callable $callback
	 * 		if callback is null, then SmartAutocompleteTab::withModel() is used to create objects
	 * 		else SmartAutocompleteTab::withArray($callback($item)) is used to create objects.
	 * @return \Illuminate\Support\Collection
	 */
	public static function withCallback($collection, $callback = null) {
		$items = new \Illuminate\Support\Collection();
		
		if ($collection)
		foreach ($collection as $c) {
			if (is_callable($callback))
		    	$items->push(static::withArray($callback($c)));
			else
				$items->push(static::withModel($c));
		}
		
		return $items;
	}
	
	/**
	 * Constructor from model object.
	 * Model must implement SmartAutocompleteInterface.
	 * @param SmartAutocompleteInterface $model
	 * @return \ORS\Helpers\SmartAutocompleteTab
	 */
	public static function withModel($model) {
		$instance = new self($model->smart_autocomplete_key, $model->smart_autocomplete_title, !empty($model->smart_autocomplete_tab_title) ? $model->smart_autocomplete_tab_title : $model->smart_autocomplete_title);
		return $instance;
	}
	
	/**
	 * Constructor from array.
	 * You need the following properties: value, label, tab.
	 * @param array $arr
	 * @return \ORS\Helpers\SmartAutocompleteTab
	 */
	public static function withArray($arr) {
		$instance = new self($arr['value'], $arr['label'], $arr['tab']);
		return $instance;
	}
	
	/**
	 * Constructor from JSON object.
	 * You need the following properties: value, label, tab.
	 * @param json $json
	 * @return \ORS\Helpers\SmartAutocompleteTab
	 */
	public static function withJson($json) {
		return self::withArray(json_decode($json, true));
	}
	
	/**
	 * @return array
	 */
	public function __toArray() {
	    return array(
	        'value' => $this->value,
	        'label' => $this->label,
	        'tab' => $this->tab,
	    );
	}
	
	public function toArray() {
	    return $this->__toArray();
	}

	/**
	 * @return string
	 */
	public function __toJson($options = 0) {
	    return json_encode($this->__toArray(), $options);
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
	    return $this->__toJson();
	}
}
?>