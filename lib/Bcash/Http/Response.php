<?php

namespace Bcash\Http;

class Response
{
	private static $OK = 200;
	private static $BAD_REQUEST = 400;
	private $code;
	private $content;

	public function __construct($content, $code)
	{
		$this->content = $content;
		$this->code = $code;
	}
	
	public function getCode()
	{
		return $this->code;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function isOK()
	{
		return isset($this->code) && ($this->code == self::$OK);
	}

	public function isFail()
	{
		return $this->content === false;
	}
		
	public function isBadRequest()
	{
		return isset($this->code) && ($this->code == self::$BAD_REQUEST);
	}

}
