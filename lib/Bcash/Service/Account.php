<?php

namespace Bcash\Service;

use Bcash\Service\IEnvironmentManager;
use Bcash\Http\PostRequest;
use Bcash\Config\Config;
use Bcash\Http\Authentication\Basic;
use Bcash\Helper\HttpHelper;
use Bcash\Http\Connection;

/**
 * Cliente para busca de contas
 *
 */
class Account implements IEnvironmentManager
{
	const route = "/searchAccount/json";
	private $email;
	private $token;
	private $url;
	
	public function __construct($email, $token)
	{
		$this->email = $email;
		$this->token = $token;
		$this->url = Config::host . self::route;
	}
	
	/**
	 * Busca todas a contas vinculadas com o CPF informado.
	 *
	 * @param cpf
	 *           CPF utilizado para a busca.
	 * @return Objeto que contém informações da busca e uma lista de contas
	 * @throws AccountException
	 *             exceção em caso de de erro na busca da conta.
	 */
	public function searchBy($cpf)
	{
		$request = $this->generateRequest($cpf);
		$response = $this->send($request);
		
		return HttpHelper::fromJson($response->getContent());
	}
	
	private function generateRequest($cpf)
	{
		$request = new PostRequest($this->url);
	
		$basic = new Basic();
		$request->addHeader($basic->generateHeader($this->email, $this->token));
		$request->addHeader("Content-Type:application/x-www-form-urlencoded;charset=" . Config::charset);
		
		$obj = new \stdClass;
		$obj->cpf = $cpf;
		
		$parameters = array("data"=> json_encode($obj), "version" => "1.0");
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
		$this->url = Config::host . self::route;

		if ($bool) {
			$this->url = Config::hostSandBox .  self::route;
		}
	}
	
}
