<?php

namespace Bcash\Domain;

class NotificationContent 
{
	protected $idTransaction;

	protected $pedido;

	protected $status;


	public function __construct($idTransaction, $pedido, $status)
	{
		$this->idTransaction = $idTransaction;
		$this->pedido = $pedido;
		$this->status = $status;
	}

	public function getIdTransaction()
	{
		return $this->idTransaction;
	}

	public function setIdTransaction($idTransaction)
	{
		$this->idTransaction = $idTransaction;
	}
	
	public function getPedido()
	{
		return $this->pedido;
	}
	
	public function setPedido($pedido)
	{
		$this->pedido = $pedido;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function setStatus($status)
	{
		$this->status = $status;
	}

}
