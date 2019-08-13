<?php

namespace Bcash\Exception;

class BaseException extends \Exception
{
	private $errors;
	
	public function __construct($errors, $message = "")
	{
		parent::__construct($message);
		$this->errors = $errors;
	}
	
	public function getErrors()
	{
		return $this->errors;
	}

}
