<?php

namespace Bcash\Http;

class PostRequest
{
	private $url;
	private $headers;
	private $params;
	private $content;

	public function __construct($url) 
	{
		$this->headers = Array();
		$this->params = Array();
		$this->url = $url;
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function addHeader($header) 
	{
		array_push($this->headers, $header);
	}

	public function getHeaders() 
	{
		return $this->headers;
	}

	public function addParam($name, $value)
	{
		$this->params[$name] = $value;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function getContent()
	{
		return $this->content;
	}
}
