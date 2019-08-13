<?php

namespace Bcash\Http;

use Bcash\Http\PostRequest;
use Bcash\Http\Response;
use Bcash\Helper\HttpHelper;
use Bcash\Exception\ValidationException;
use Bcash\Exception\ConnectionException;

class Connection
{

	const USER_AGENT = "bcash-php-sdk-2.0.0";
	private $timeout;

	public function __construct($timeout = 60)
	{
		if (!function_exists('curl_init')) {
            throw new Exception('BcashLibrary: cURL library is required.');
        }

        $this->timeout = $timeout;
	}

	public function post(PostRequest $request)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $request->getUrl());
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());
		curl_setopt($ch, CURLOPT_POST, count($request->getContent()));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());

		return $this->send($ch);
	}

	public function get(GetRequest $request)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $request->getUrl());
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());

		return $this->send($ch);
	}

	public function put(PutRequest $request)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $request->getUrl());
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getContent());

		return $this->send($ch);
	}

	
	private function send($ch)
	{
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

		$response = new Response(curl_exec($ch), curl_getinfo($ch, CURLINFO_HTTP_CODE));
		$response = $this->responseResolver($response, $ch);

		curl_close($ch);

		return $response;
	}

	private function responseResolver(Response $response, $ch)
	{
		if ($response->isOK()) {
			return $response;
		}

		if ($response->isBadRequest()) {
			throw new ValidationException(HttpHelper::fromJson($response->getContent()));
		}

		throw new ConnectionException(HttpHelper::fromJson($response->getContent()), curl_error($ch));
	}

}
