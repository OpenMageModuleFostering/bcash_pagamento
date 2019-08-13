<?php

namespace Bcash\Exception;

class ConnectionException extends BaseException 
{
	const MESSAGE = "Erro de conexão: ";
	
	public function __construct($errors, $message) 
	{
        parent::__construct($errors, self::MESSAGE . $message);
		$this->errors = $errors;
    }

}
