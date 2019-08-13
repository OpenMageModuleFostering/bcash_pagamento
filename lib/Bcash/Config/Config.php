<?php

namespace Bcash\Config;
/**
 * Classe de configuração responsável por armazenar as informações de conexão do cliente com a API </br>
 * Responsável também por armazenar as configurações de utilização da API desejadas, como por exemplo a versão.
 *
 */
class Config
{
	const host = "https://api.bcash.com.br/service";
	const hostSandBox = "https://sandbox-api.bcash.com.br/service";

	const siteHost = "https://www.bcash.com.br";
	const siteHostSandBox = "https://sandbox.bcash.com.br";

	const charset =  "UTF-8"; // UTF-8 or ISO-8859-1
	const timeout = 20;
}
