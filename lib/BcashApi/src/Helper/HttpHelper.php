<?php

namespace Bcash\Helper;

class HttpHelper
{
	public static function toQueryString($parameters)
	{
		return http_build_query($parameters, '', '&');
	}

	public static function fromJson($json) 
	{
		return json_decode($json);
	}

}
