<?php

namespace Bcash\Domain;

class DependentTransaction extends Model
{

	protected $email;

	protected $value;

	/**
	 * Email da conta dependente<br>
	 * <i>Tamanho máximo: 80 caracteres</i><br>
	 *
	 * @return email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Email da conta dependente<br>
	 * <i>Tamanho máximo: 80 caracteres</i><br>
	 *
	 * @param email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}
	
	/**
	 * Valor monetário da transação dependente.<br>
	 * 
	 * @return value
	 *			  , ex.: 10.95
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * Valor monetário da transação dependente.<br>
	 *
	 * @param value
	 * 			 , ex.: 10.95
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

}
