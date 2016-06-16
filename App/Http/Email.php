<?php

namespace App\Http;

include_once 'Pear/Mail.php';
include_once 'Pear/PEAR.php';

Class Email
{
	private $result = null;
	private $subject = null;
	private $to = null;
	private $error = null;

	public function __construct()
	{
		mb_internal_encoding('utf-8'); // 指定編碼格式
	}

	private function params()
	{
		return [
			'host' => 'ssl://smtp.gmail.com',
		    'port' => '465',
		    'auth' => true,
		    // 這邊設定對的話，可以寄信。
		    'username' => 'nctu.apply.ee@gmail.com',
		    'password' => 'ETTM8wxRRS'
		];
	}

	public function to($value = null)
	{
		$this->to = $value;
		return $this;
	}

	public function subject($value = null)
	{
		$this->subject = $value;
		return $this;
	}

	private function headers()
	{
		// 設定檔頭資訊
		return [
			// 用 mb_encode_mimeheader() 將寄件人中的字串 
			// 轉成符合 SMTP 通訊協定要求的格式
			'From'         => "from.gmail.com",
			'To'           => $this->to,
			// 用 mb_encode_mimeheader() 將郵件標題 
			// 轉成符合 SMTP 通訊協定要求的格式
			'Subject'      => mb_encode_mimeheader($this->subject),  
			'Content-Type' => 'text/html; charset="UTF-8"',
			'Content-Transfer-Encoding' => '8bit'
		];
	}

	public function factory()
	{
		// 建立使用SMTP的物件
		return \Mail::factory('smtp', $this->params());
	}

	public function send($content)
	{
		if($this->to == null or $this->subject == null)
			return false;

		 // 呼叫 send() 方法寄出郵件, 並取得其傳回值
		$this->result = $this->factory()->send($this->to, $this->headers(), $content);
		$this->error = \PEAR::isError($this->result);
		return $this;
	}

	public function failed()
	{
		if($this->result == null or $this->result == true)
			return false;

		return true;

		// 由 send() 傳回值判斷結果並顯示對應訊息
    	// 如果寄件失敗, $result 將會是 PEAR_:Error 的物件 
    	// 如果寄件成功, 則傳回值為 true
		return !\PEAR::isError($this->result);
	}

	public function error()
	{
		if($this->error == null)
			return '未設定對方信箱或主旨。';

		// 呼叫 getMessage() 方法取得錯誤訊息字串
		return $this->error->getMessage();
	}
}