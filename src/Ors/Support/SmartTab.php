<?php namespace Ors\Support;
/**
 * SmartTab class prepares data to create a tabs in ORS Smart search page.
 * 
 * Tabs represent the following information:
 * - "id": tab unique id
 * - "title": tab title/name
 * - "icon" : an icon to display in the tab
 * - "color_class": a CSS class that represent a color of the class (optional)
 * - "search_id" : if a tab is saved in database, then you can put that search id here
 * - "extra_params" : this is an array of any additional values/parameters you wish to attach to this tab.
 * 
 * @author Gregor Flajs
 *
 */
class SmartTab {

    /**
     * tab id (ctype id)
     * @var string
     */
    public $id;

    /**
     * tab title
     * @var string
     */
    public $title;

    /**
     * tab icon (css class for icon)
     * @var string
     */
    public $icon = '';

    /**
     * tab color (bootstrap css color class)
     * @var string
     */
    public $color_class = 'bg-primary light';

    /**
     * Search id when a tab is saved
     * @var int
     */
    public $search_id = '';
    
    /**
     * This are some extra parameters that can be accessible from tab.
     * Use with() method to set this parameters.
     * @var array
     */
    public $extra_params;
    
    /**
     * Default action to execute after a tab is opened. 
     * Just enter a route name here and JS script will do the rest.
     * @var string
     */
    public $action;
    
    public function __construct($id, $title = '') {
        $this->id = $id;
        $this->title = $title;
        $this->extra_params = array();
    }

    /**
     * Constructor with JSON object
     * @param string|json $smart_tab
     * @return SmartTab
     */
    public static function withJson($smart_tab) {
    	return self::withArray(json_decode($smart_tab, true));
    }
    
    /**
     * Constructor with array object
     * @param array $smart_tab
     * @return SmartTab
     */
    public static function withArray($smart_tab) {
    	$instance = new self($smart_tab['id'], $smart_tab['title']);
    	if (!empty($smart_tab['color_class']))
	    	$instance->setColorClass($smart_tab['color_class']);
    	if (!empty($smart_tab['icon']))
    		$instance->setIcon($smart_tab['icon']);
    	if (!empty($smart_tab['action']))
    		$instance->setAction($smart_tab['action']);
    	if (!empty($smart_tab['extra_params']))
    		$instance->extra_params = $smart_tab['extra_params'];
    	return $instance;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function setColorClass($color_class) {
        $this->color_class = $color_class;
        return $this;
    }
    
    public function setSearchId($search_id) {
        $this->search_id = $search_id;
        return $this;
    }
    
    public function setAction($action) {
        $this->action = $action;
        return $this;
    }
    
    /**
     * Set SmartTab extra parameters
     * @param string $name
     * @param mixed $value
     * @return SmartTab
     */
    public function with($name, $value) {
        $this->extra_params[$name] = $value;
        return $this;
    }

    public function __toArray() {
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'color_class' => $this->color_class,
            'icon' => $this->icon,
        	'search_id' => $this->search_id,
        	'extra_params' => $this->extra_params,
        	'action' => $this->action,
        );
    }
    
    public function __toJson() {
    	return json_encode($this->__toArray());
    }
    
    public function __toString() {
        return $this->__toJson();
    }
}
?>