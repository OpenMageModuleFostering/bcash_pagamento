<?php

namespace Bcash\Http\Authentication;

class Basic
{
	private $email;
	private $token;
	
	public function generateHeader($email, $token)
	{
		
		$this->email = $email;
		$this->token = $token;
		
		return "Authorization: Basic " . base64_encode($this->email . ":" . $this->token);
	}

}
