<?php

namespace Bcash\Service;

use Bcash\Service\IEnvironmentManager;
use Bcash\Http\Authentication\Basic;
use Bcash\Http\PostRequest;
use Bcash\Helper\HttpHelper;
use Bcash\Http\Connection;
use Bcash\Config\Config;

/**
 * Cliente para o serviço de cancelamento de transação.
 *
 */
class Cancellation implements IEnvironmentManager
{
	const route = "/transactions/%s/cancel";
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
	 * Cancela a transação informada.
	 *
	 * @param id_transacao
	 *            Id da transação no Bcash a ser cancelada.
	 */
	public function execute($transactionId)
	{
		$request = $this->generateRequest($transactionId);
		$response = $this->send($request);

		return HttpHelper::fromJson($response->getContent());
	}

	private function generateRequest($transactionId)
	{
		$request = new PostRequest($this->url);

		$basic = new Basic();
		$request->addHeader($basic->generateHeader($this->email, $this->token));
		$request->addHeader("Content-Type:application/json;charset=".Config::charset);

		$request->setUrl(vsprintf($this->url, $transactionId));

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
