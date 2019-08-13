<?php

namespace Bcash\Exception;

class ValidationException extends BaseException 
{
	const MESSAGE = "Solicitação Inválida";
	
	public function __construct($errors) 
	{
        parent::__construct($errors, self::MESSAGE);
		$this->errors = $errors;
    }

}
