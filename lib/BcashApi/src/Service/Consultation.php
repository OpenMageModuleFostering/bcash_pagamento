<?php

namespace Bcash\Service;

use Bcash\Service\IEnvironmentManager;
use Bcash\Http\PostRequest;
use Bcash\Config\Config;
use Bcash\Http\Authentication\Basic;
use Bcash\Helper\HttpHelper;
use Bcash\Http\Connection;

/**
 * Cliente para consulta de transações.
 *
 */
class Consultation implements IEnvironmentManager
{
	const route = "/transacao/consulta";
	private $email;
	private $token;
	private $url;

	public function __construct($email, $token)
	{
		$this->email = $email;
		$this->token = $token;
		$this->url = Config::siteHost . self::route;
	}

	/**
	 * Busca os dados da transação pelo id da transação no Bcash.
	 *
	 * @param id_transacao
	 *           Id da transação no Bcash a ser consultada.
	 * @return Objeto que contém informações da busca
	 */
	public function searchByTransaction($id_transacao)
	{
		$request = $this->generateRequest("id_transacao", $id_transacao);
		$response = $this->send($request);

		return HttpHelper::fromJson($response->getContent());
	}

	/**
	 * Busca os dados da transação pelo id do pedido.
	 *
	 * @param id_pedido
	 *           Id do pedido a ser consultado.
	 * @return Objeto que contém informações da busca
	 */
	public function searchByOrder($id_pedido)
	{
		$request = $this->generateRequest("id_pedido", $id_pedido);
		$response = $this->send($request);

		return HttpHelper::fromJson($response->getContent());
	}

	private function generateRequest($param, $value)
	{
		$request = new PostRequest($this->url);

		$basic = new Basic();
		$request->addHeader($basic->generateHeader($this->email, $this->token));

		$parameters = array($param => $value, "codificacao" => Config::charset, "tipo_retorno" => 2);
		$request->setContent(HttpHelper::toQueryString($parameters));

		return $request;
	}

	private function send($request)
	{
		$connection = new Connection(Config::timeout);
		$response = $connection->post($request);

		return $response;
	}

	public function enableSandBox($bool)
	{
		$this->url = Config::siteHost . self::route;

		if ($bool) {
			$this->url = Config::siteHostSandBox .  self::route;
		}
	}

}
