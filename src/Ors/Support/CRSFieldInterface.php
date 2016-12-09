<?php namespace Ors\Support;

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
?>