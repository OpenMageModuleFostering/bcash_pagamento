<?php

namespace Bcash\Domain;

class PaymentMethod extends Model
{

	protected $code;
	
	public function __construct($code)
	{
		$this->code = $code;
	}

	/**
	 * Meio de Pagamento utilizado para processar a transação<br>
	 * *Vide enum: {@link PaymentMethodEnum}
	 *
	 * @return code
	 *            , ex.: PaymentMethodEnum::VISA
	 */
	public function getCode()
	{
		return $this->code;
	}

}
