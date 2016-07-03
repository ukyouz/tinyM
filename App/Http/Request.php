<?php

namespace App\Http;

use App\Http\UploadedFile;
use App\Http\Validator;
require_once 'UploadedFile.php';
require_once 'Validator.php';

Class Request
{
	private $inputs;
	private $files;
	protected $error;

	public function __construct()
	{
		$this->inputs = array_merge($_POST, $_GET);
		$this->files = new UploadedFile;
	}

	public static function all()
	{
		$instance = new static;
		return $instance;
	}

	public function set()
	{
		$args = func_get_args();
		$arr = [];
		if (count($args) == 1)
			$arr = $args[0];
		else
			$arr[$args[0]] = $args[1];

		$_POST = array_merge($arr, $_POST);
		$this->inputs = array_merge($arr, $this->inputs);
		
		return $this;
	}

	public function invalid()
	{
		$v = new Validator;
		$v->make($this);
		if ($v->failed()) {
			$this->error = $v->errors();
			return true;
		}

		return false;
	}

	public static function input($key, $value = null)
	{
		$instance = new static;

		$keys = explode('.', $key);
		$arr = $instance->inputs;
		foreach ($keys as $k => $v) {
			if (array_key_exists($v, $arr)) {
				$value = $arr[$v];
				if (gettype($arr[$v]) == 'array')
					$arr = $arr[$v];
			}
		}

		return $value;
	}

	public static function file($key = null)
	{
		$instance = new static;

		return $instance->files->only($key);
	}

	public function hasFile($key = null)
	{
		$instance = new static;
		// echo $key."<br>";
		return $instance->files->hasFile($key);
	}

	public static function except()
	{
		$instance = new static;
		$args = [];
		foreach (func_get_args() as $key => $value)
			$args[$value] = $value;

		$instance->inputs = array_diff_key($instance->inputs, $args);
		return $instance;
	}

	public static function only()
	{
		$instance = new static;
		$args = [];
		foreach (func_get_args() as $key => $value)
			$args[$value] = $value;

		$instance->inputs = array_intersect_key($instance->inputs, $args);
		return $instance;
	}

	public static function has($key)
	{
		$instance = new static;
		return array_key_exists($key, $instance->inputs);
	}

	public function get()
	{
		return $this->inputs;
	}

	public function error()
	{
		return $this->error;
	}


	public function rules()
	{
		return [
			// 'name' => 'required|min:4'
		];
	}
}