<?php

namespace App\Http;

use App\Http\Request;
require_once 'Request.php';

Class StudentRequest extends Request
{
	public function rules()
	{
		return [
			'stu_name' => 'required|max:10',
			// 'stu_series_num' => 'required',
			'stu_email' => 'required|email',
			'stu_birthday' => 'date',
			'stu_phone' => 'integer',
			'stu_autobiography' => 'mimes:pdf,jpg'
		];
	}
}