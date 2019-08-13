<?php

namespace Bcash\Service;

use Bcash\Service\IEnvironmentManager;
use Bcash\Http\Authentication\OAuth;
use Bcash\Http\PostRequest;
use Bcash\Helper\HttpHelper;
use Bcash\Http\Connection;
use Bcash\Domain\TransactionRequest;
use Bcash\Config\Config;

/**
 * Cliente para serviços de criação de transação.
 *
 */
class Payment implements IEnvironmentManager
{
	const route = "/createTransaction/json";
	private $consumer_key;
	private $url;
	
	public function __construct($consumer_key)
	{
		$this->consumer_key = $consumer_key;
		$this->url = Config::host . self::route;
	}
	
	/**
	 * Cria uma transação a partir dos dados informados.
	 *
	 * @param request
	 *            Objeto que contém informações para a criação da transação
	 */
	public function create(TransactionRequest $transactionRequest)
	{
		$request = $this->generateRequest($transactionRequest);
		$response = $this->send($request);
		
		return HttpHelper::fromJson($response->getContent());
	}
	
	private function generateRequest(TransactionRequest $transactionRequest)
	{
		$request = new PostRequest($this->url);
		
		$oAuth = new OAuth();
		$request->addHeader($oAuth->generateHeader($this->consumer_key));
		$request->addHeader("Content-Type:application/x-www-form-urlencoded;charset=".Config::charset);
		$parameters = array("data"=> json_encode($transactionRequest->toArray()), "version" => "1.0");
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
			$this->url = Config::hostSandBox  . self::route;
		}
	}
}
