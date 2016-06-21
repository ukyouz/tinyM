<?php

namespace App\Http;

use App\Http\Request;
require_once 'Request.php';

define( "UTF8_CHINESE_PATTERN", "/[\x{4e00}-\x{9fff}\x{f900}-\x{faff}]/u" );
define( "UTF8_SYMBOL_PATTERN", "/[\x{ff00}-\x{ffef}\x{2000}-\x{206F}]/u" );

Class Validator
{
	private $rules = [];
	private $errors = [];

	public function make(Request $request, $rules = null)
	{
		$rules = ($rules == null) ? $request->rules() : $rules;
		$this->rules = array_map(function($v){
			return explode('|', $v);
		}, $rules);
		$this->check($request);
		return $this;
	}

	private function check(Request $request)
	{	
				// echo "<pre>";
		// print_r($request->file()->get());
		foreach ($this->rules as $key => $value) {
			$input = null;
			if ($request->has($key))
				$input = $request->input($key);
			if ($request->hasFile($key)){
				$input = $request->file($key);
				// echo $key."<br>";
				// print_r($input);
			}

				// echo $key;
			if ($input == null)
				continue;

			foreach ($value as $k => $v) {
				$this->validate($key, $input, $v);
			}
		}
	}

	private function validate($key, $input, $rule)
	{
		$rule = explode(':', $rule);
		$message = '';
		switch ($rule[0]) {
			case 'required':
				if($input == '')
					$message = '必須';
				break;
			case 'min':
				if ($this->str_utf8_mix_word_count($input) < $rule[1])
					$message = '至少 '. $rule[1]. ' 個字';
				break;
			case 'max':
				if ($this->str_utf8_mix_word_count($input) > $rule[1])
					$message = '最多 '. $rule[1]. ' 個字';
				break;
			case 'email':
				if (!$this->regex_check($input, 'email'))
					$message = 'E-mail 格式不符';
				break;
			case 'date':
				if (!$this->regex_check($input, 'date'))
					$message = '日期格式不符';
				break;
			case 'boolean':
				if (!$this->check_bool($input))
					$message = '必須為 Boolean';
				break;
			case 'integer':
				if (!ctype_digit($input))
					$message = '必須為正整數';
				break;
			case 'numeric':
				if (!is_numeric($input))
					$message = '必須為數字';
				break;
			case 'mimes':
				if (!$this->check_mime($input->first()->type, $rule[1]))
					$message = '只能上傳以下副檔名：'. $rule[1];
				break;
			case 'size':
				// if ($input['size'])
				break;
			default:
				# code...
				break;
		}

		if ($message != '')
			$this->errors[$key][] = $message;
	}

	// count only chinese words
	private function str_utf8_chinese_word_count($str = ""){
	    $str = preg_replace(UTF8_SYMBOL_PATTERN, "", $str);
	    return preg_match_all(UTF8_CHINESE_PATTERN, $str, $arr);
	}

	// count both chinese and english
	private function str_utf8_mix_word_count($str = ""){
	    $str = preg_replace(UTF8_SYMBOL_PATTERN, "", $str);
	    return $this->str_utf8_chinese_word_count($str) + str_word_count(preg_replace(UTF8_CHINESE_PATTERN, "", $str));
	}

	private function regex_check($input, $type)
	{
		$pattern = "";
		switch ($type) {
			case 'email':
				$pattern = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
				break;
			case 'date':
				$pattern = '/^[0-9]{2,4}[-\/](0[1-9]|1[0-2])[-\/](0[1-9]|[1-2][0-9]|3[0-1])$/';
				break;
			default:
				# code...
				break;
		}
		return preg_match($pattern, $input);
	}

	private function check_bool($string){
		$string = strtolower($string);
		return (in_array($string, array("true", "false", "1", "0", "yes", "no"), true));
	}

	private function check_mime($input_mime_type, $allowed_exts){
		$mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

		foreach (explode(",", $allowed_exts) as $ext) {
			if ($mime_types[$ext] == $input_mime_type)
				return true;
		}
		
		return false;
	}

	public function failed()
	{
		if (count($this->errors))
			return true;

		return false;
	}

	public function errors()
	{
		return $this->errors;
	}
}