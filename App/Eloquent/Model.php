<?php

namespace App\Eloquent;

use App\Eloquent\DB;
use App\Http\Request;
require_once 'DB.php';

Class Model
{
	protected $table;
	protected $primaryKey;
	protected $fillable;
	private $objs;
	protected $error = [];

	public function __construct()
	{
		//
	}

	public function count()
	{
		return count($this->objs);
	}

	private function retrieve($results = [])
	{
		$this->objs = [];
		foreach ($results as $key => $value) {
			$this->objs[] = (object)$value;
		}
	}

	private function db()
	{
		return DB::table($this->table);
	}

	public static function select()
	{
		$args = func_get_args();
        // static::$select = join(",", $args);
        $instance = new static;
		$results = $instance->db()->select(join(',', $args))->get();
		$instance->retrieve($results);
		return $instance;
	}

	public static function all()
	{
		$instance = new static;
		$results = $instance->db()->get();
		$instance->retrieve($results);
		return $instance;
	}

	public static function where($colname = null, $operator = null, $value = null)
	{
		$instance = new static;
		if($colname==null or $operator==null) {
			$instance->error[] = "sytax error in command WHERE.";
			return $instance;
		}

		if($value==null) {
			$value = $operator;
			$operator = "=";
		}
		
		$results = $instance->db()->where($colname, $operator, $value)->get();

		if (!$results)
			return null;

		$instance->retrieve($results);
		return $instance;
	}

	public static function find($id)
	{
		if($id==null)
			return null;

		$instance = new static;
		return $instance->where($instance->primaryKey, $id);
	}

	/**
     * Insert a set to table
     *
     * @param Array $dataArr The query data array
     * @return Model with the created data
     */
	public static function create(Request $request = null)
	{
		$instance = new static;
		if ($request->invalid()){
			$instance->error[] = $request->error();
			return $instance;
		}

		$validFields = [];
		// if ($request == null)
		// 	$request = new Request;
	// print_r($request);

		foreach ($request->get() as $key => $value) {
			if (in_array($key, $instance->fillable)){
				$validFields[$key] = $value;
			}
		}

		$id = $instance->db()->insert($validFields);
		if(!$id)
			$instance->error[] = $instance->db()->error();
		else {
			$results = $instance->db()->where($instance->primaryKey, $id)->get();
			$instance->retrieve($results);
		}

		return $instance;
	}

	/**
     * Update a set in table
     *
     * @param Array $dataArr The query data array
     * @return Model with the created data
     */
	public function update(Request $request)
	{
		if ($request->invalid()){
			$this->error[] = $request->error();
			return $this;
		}

		$success = true;
		$primaryKey = $this->primaryKey;

		$validFields = [];
		foreach ($request->get() as $key => $value) {
			if (in_array($key, $this->fillable)){
				$validFields[$key] = $value;
			}
		}
		// print_r($validFields);

		foreach ($this->objs as $key => $value) {
			$updatedKey[] = $value->$primaryKey;
			if( !$this->db()->where($primaryKey, $value->$primaryKey)->update($validFields) ){
				// if failed ...
				$success = false;
				$this->error[] = $this->db()->error();
				return $this;
			}
		}
		if ($success){
			$results = $this->db()->orderBy('updated_at')->take($this->count())->get();
			$this->retrieve($results);
		} else {
			$instance->error[] = $instance->db()->error();
		}
		return $this;
	}

	public function delete()
	{
		$primaryKey = $this->primaryKey;

		$success = true;

		// print_r($this->objs);
		foreach ($this->objs as $key => $value) {
			if( !$this->db()->where($primaryKey, $value->$primaryKey)->delete() ) {
				$success = false;
			}
		}

		if( $success == false ) {
			$instance->error = $instance->db()->error();
			return false;
		}

		return true;
	}

	public static function destroy()
	{
		$args = func_get_args();
		
		$instance = new static;
		
		$success = true;
		foreach ($args as $key => $value) {
			if( !$instance->db()->where($instance->primaryKey, $value)->delete() )
				$success = false;
		}

		if( $success == false ) {
			$instance->error = $instance->db()->error();
			return false;
		}

		return true;
	}

	/**
     * Get the selected data
     *
     * @return Array The result of the select function
     */
	public function get()
	{
		if($this->count() == 1)
			return $this->objs[0];

		return $this->objs;
	}

	public function failed()
	{
		if ($this->error != [])
			return true;

		return false;
	}

	public function error()
	{
		return $this->error;
	}

	public static function test(Request $input)
	{
		return $input;
	}
}