<?php namespace Ors\Support;

/**
 * Models that you want to use for autocomplete functionallity must implement this interface.
 *
 * You can also use a callback when creating SmartAutocomplete object, if you don't wish to use this interface.
 *
 * @author Gregor Flajs
 *
 */
interface SmartAutocompleteInterface {
	
	/**
	 * a key value
	 * @return string
	 */
	public function getSmartAutocompleteKeyAttribute();
	
	/**
	 * display item in the list
	 * @return string
	 */
	public function getSmartAutocompleteTitleAttribute();
	
	/**
	 * title for a tab (if this is empty then SmartAutocompleteTitle is used)
	 * @return string
	 */
	public function getSmartAutocompleteTabTitleAttribute();
	
	/**
	 * css class for icon to display in front of each item (this can be empty)
	 * @return string
	 */
	public function getSmartAutocompleteIconAttribute();
	
}