<?php

namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
		return view('welcome_message');
	}

	public function coba($nama = " ")
	{
		echo "INI PERCOBAAN FUNGSI COBA DI CONTROLLER HOME, data pass nya $nama";
	}

	//--------------------------------------------------------------------

}
