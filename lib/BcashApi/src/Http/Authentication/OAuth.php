<?php

namespace Bcash\Http\Authentication;

use Bcash\Config\Config;

/**
 * Classe de Autorization padrão OAtuh 1.0
 *
 * @author bcash
 */
class OAuth {

	private $consumer_key;
	private $nonce;
	private $timestamp;

	/**
	 * Função para gerar o header
	 * @access public
	 * @return string
	 */
	public function generateHeader($consumer_key) {
				
		$this->consumer_key = $consumer_key; 
		$this->nonce = md5( microtime() . mt_rand() );
		$this->timestamp = urlencode(time()*1000);
		
		$header = "Authorization: OAuth realm=https://api.bcash.com.br," .
				"oauth_consumer_key=" .$this->consumer_key . "," .
				"oauth_nonce=" . $this->nonce . "," .
				"oauth_signature=" . $this->generateSignature() . "," .
				"oauth_signature_method=PLAINTEXT," .
				"oauth_timestamp=" . $this->timestamp . "," .
				"oauth_version=1.0" ;
		return $header;
	}

	/**
	 * Função para gerar o campo oauth_signature
	 * @access private
	 * @return string
	 */
	private function generateSignature() {
		$oauth_signature = "oauth_consumer_key=" . urlencode($this->consumer_key) . "&" .
				"oauth_nonce=" . urlencode($this->nonce) . "&" .
				"oauth_signature_method=" . urlencode("PLAINTEXT") . "&" .
				"oauth_timestamp=" . urlencode($this->timestamp) . "&" .
				"oauth_version=" . urlencode("1.0");

		$oauth_signature = base64_encode($oauth_signature);
		return $oauth_signature;
	}
}