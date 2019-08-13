<?php

namespace Bcash\Test;

use Bcash\Http\PostRequest;
use Bcash\Http\Connection;
use Bcash\Helper\HttpHelper;
use Bcash\Config\Config;
use Bcash\Domain\NotificationStatusEnum;

/**
 * Classe para simular a notificação do bcash.
 *
 */
class NotificationSimulator
{

	public static $STATUS_DESCRIPTION = array(
		1 => "Em andamento",
		3 => "Aprovada",
		4 => "Concluída",
		5 => "Disputa",
		6 => "Devolvida",
		7 => "Cancelada",
		8 => "Chargeback"
	);

	public static function test($urlAviso, $id_transacao, $id_pedido, $id_status)
	{
		$request = new PostRequest($urlAviso);
		$parameters = array(
			"transacao_id" => $id_transacao,
			"pedido" => $id_pedido,
			"status_id" => $id_status,
			"status" => self::$STATUS_DESCRIPTION[$id_status]
		);

		$request->setContent(HttpHelper::toQueryString($parameters));

		$response = self::send($request);
		return $response;
	}

	private static function send($request)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $request->getUrl());
		curl_setopt($ch, CURLOPT_POST, count($request->getContent()));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		$response = new \StdClass;

		if ($result === false) {
			$response->message = 'Curl error: ' . curl_error($ch);
			return $response;
		}

		$response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($response->code == 200) {
			$response->message = "Request successfully delivered: " . $request->getContent() . " for URL " . $request->getUrl(); 
			return $response;
		}

		if ($response->code == 404) {
			$response->message = "Url not found: " . $request->getUrl();
			return $response;
		}

		return $response;
	}

}

