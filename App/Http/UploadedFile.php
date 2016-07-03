<?php

namespace App\Http;

Class UploadedFile
{
	private $files;
	private $error = "";

	public function __construct()
	{
		$this->files = $_FILES;
	}

	public static function file($key = null)
	{
		$instance = new static;
		if($key==null)
			return $instance;

		if(array_key_exists($key, $instance->files)) {
			$args = [$key => $key];		
			$instance->files = array_intersect_key($instance->files, $args);
		} else {
			$instance->error = "No such file.";
			$instance->files = [];
		}

		return $instance;
	}

	public static function only()
	{
		$instance = new static;
		$args = [];
		foreach (func_get_args() as $key => $value)
			$args[$value] = $value;

		$instance->files = array_intersect_key($instance->files, $args);
		return $instance;
	}

	public function hasFile($key)
	{
		return array_key_exists($key, $this->files);
	}

	public function isValid()
	{
		if ($this->file == null)
			return false;

		switch ( $this->file['error']){
			case 0:
				return true;
				break;
			case 1:
				$this->error = "檔案大小超出了伺服器上傳限制 UPLOAD_ERR_INI_SIZE。";
				break;
			case 2:
				$this->error = "要上傳的檔案大小超出瀏覽器限制 UPLOAD_ERR_FORM_SIZE。";
				break;
			case 3:
				$this->error = "檔案僅部分被上傳 UPLOAD_ERR_PARTIAL。";
				break;
			case 4:
				$this->error = "沒有找到要上傳的檔案 UPLOAD_ERR_NO_FILE。";
				break;
			case 5:
				$this->error = "伺服器臨時檔案遺失。";
				break;
			case 6:
				$this->error = "檔案寫入到站存資料夾錯誤 UPLOAD_ERR_NO_TMP_DIR。";
				break;
			case 7:
				$this->error = "無法寫入硬碟 UPLOAD_ERR_CANT_WRITE。";
				break;
			case 8:
				$this->error = "UPLOAD_ERR_EXTENSION.";
				break;
		}

		return false;
	}

	public function failed()
	{
		if (count($this->files) == 0)
			return true;
		if ($this->error != "")
			return true;

		return false;
	}

	public function error()
	{
		return $this->error;
	}

	public function move($toPath, $filename = null)
	{
		if(count($this->files) == 0){
			$this->error = 'Empty file.';
			return $this;
		}

		foreach ($this->files as $key => $file) {
			if ($filename == null)
				// $filename = $key.".".$this->pathinfo($file['name'])['extension'];
				$filename = $this->pathinfo($file['name'])['basename'];
			
			// Filter for Japanese and Korean
			$newFilename = preg_replace('/[\x{AC00}-\x{D7A3}|\x{0800}-\x{4e00}]+/u', '-', $filename);
			$newFilename = iconv("utf-8", "big5//IGNORE", $newFilename);

			if ( !is_dir($toPath) )
				mkdir($toPath);

			if (move_uploaded_file($file["tmp_name"], $toPath. $newFilename)) {
				$this->files[$key]['name'] = iconv("big5", "utf-8", $newFilename);
			} else {
				$this->error .= "無法上傳檔案：". $filename. "。\n";
			}
		}

		return $this;
	}

	public function get()
	{
		return array_map(function($arr){
			return (Object)$arr;
		}, $this->files);
	}

	public function first()
	{
		return (Object)reset($this->files);
	}

 	// public function getMimeType()
 	// {
		// return array_map(function($arr){
		// 	return $arr->type;
		// }, $this->get());
 	// }

	private function pathinfo($path)
	{
		$dirname = '';
		$basename = '';
		$extension = '';
		$filename = '';

		$pos = strrpos($path, '/'); 

		if ($pos !== false) {
			$dirname = substr($path, 0, strrpos($path, '/'));
			$basename = substr($path, strrpos($path, '/') + 1);
		} else {
			$basename = $path;
		}

		$ext = strrchr($path, '.'); 
		if ($ext !== false) {
			$extension = substr($ext, 1);
		}

		$filename = $basename;
		$pos = strrpos($basename, '.');
		if ($pos !== false) {
			$filename = substr($basename, 0, $pos); 
		}

		return array (
			'dirname' => $dirname,
			'basename' => $basename,	// filename.ext
			'extension' => $extension,	// ext
			'filename' => $filename 	// filename
		);
	} 
}