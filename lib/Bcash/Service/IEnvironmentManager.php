<?php

namespace Bcash\Service;

/**
 * 
 * Interface para gerenciar o ambiente de execução dos serviços
 * 
 * @author Leonardo Tonin Neto
 *
 */
interface IEnvironmentManager 
{
	public function enableSandBox($bool);
}
