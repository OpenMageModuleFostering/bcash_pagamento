<?php

namespace Bcash\Service;

use Bcash\Service\Consultation;
use Bcash\Domain\NotificationContent;

class Notification implements IEnvironmentManager
{
	private $email;
	private $token;
	private $notificationContent;
	private $transaction;
	private $sandBox;

	/**
	 * @param email
	 *           Email da conta do lojista no bcash para autenticação.
	 * @param token
	 *           Token da conta do lojista no bcash para autenticação. 
	 * @param Array notificationContent
	 *           Conteúdo recebido na notificação
	 */
	public function __construct($email, $token, NotificationContent $notificationContent)
	{
		$this->email = $email;
		$this->token = $token;

		if (empty($notificationContent)) {
			$notificationContent = $_POST;
		}

		$this->sandBox = false;
		$this->notificationContent = $notificationContent;
	}

	/**
	 * Valida a notificação recebida
	 * @param transactionValue
	 *           Soma de todos os produtos + frete + acrescimos - descontos.
	 * @return boolean indicando se a notificação é ou não válida.
	 */
	public function verify($transactionValue)
	{
		$this->getTransaction();

		if (!$this->compareStatus() || !$this->compareValue($transactionValue) || !$this->compareOrder()) {
			return false;
		}

		return true;
	}

	private function compareValue($value)
	{
		$consultado = $this->transaction->valor_original;
		return $consultado == $value;
	}

	private function compareStatus()
	{
		$consultado = $this->transaction->cod_status;
		$recebido = $this->notificationContent->getStatus();
		return $consultado == $recebido;
	}

	private function compareOrder()
	{
		$consultado = $this->transaction->id_pedido;
		$recebido = $this->notificationContent->getPedido();
		return $consultado == $recebido;
	}

	private function getTransaction()
	{
		if (!empty($transaction)) {
			return;
		}

		$consultation = new Consultation($this->email, $this->token);
		$consultation->enableSandBox($this->sandBox);
		$this->transaction = $consultation->searchByTransaction($this->notificationContent->getIdTransaction());
		$this->transaction = $this->transaction->transacao;
	}

	public function enableSandBox($bool)
	{
		if ($bool) {
			$this->sandBox = true;
		}
	}

}
