<?php

namespace Bcash\Domain;

use Bcash\Domain\PaymentMethod;

/**
 * Objeto de requisição para serviços de transação.
 */
class TransactionRequest extends Model
{

	protected $sellerMail;

	protected $ipSeller;

	protected $orderId;

	protected $buyer;

	protected $free;

	protected $freight;

	protected $freightType;

	protected $discount;

	protected $addition;

	protected $paymentMethod;

	protected $urlReturn;

	protected $urlNotification;

	protected $products;
	
	protected $dependentTransactions;

	protected $installments;

	protected $creditCard;

	protected $currency;

	protected $acceptedContract;

	protected $viewedContract;

	protected $campaignId;

	protected $platformId;

	/**
     * Recupera o e-mail da loja cadastrada no Pagamento Digital.
     *
     * @return sellerMail
     */
	public function getSellerMail()
	{
		return $this->sellerMail;
	}


	/**
	 * E-mail da loja cadastrada no Pagamento Digital<br>
	 * <i>Tamanho máximo: 80 caracteres</i><br>
	 * <b>Campo obrigatório</b>
	 *
	 * @param sellerMail
	 *            , ex.: empresa@provedor.com
	 */
	public function setSellerMail($sellerMail)
	{
		$this->sellerMail = $sellerMail;
	}


	/**
	 * Código do pedido atribuído pela loja<br>
	 * <i>Tamanho máximo: 50 caracteres</i>
	 *
	 * @return orderId
	 *            , ex.: 123
	 */
	public function getOrderId()
	{
		return $this->orderId;
	}


	/**
	 * Código do pedido atribuído pela loja<br>
	 * <i>Tamanho máximo: 50 caracteres</i>
	 *
	 * @param orderId
	 *            , ex.: 123
	 */
	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
	}


	/**
	 * Objeto {@link Customer}<br>
	 *
	 * @return buyer
	 */
	public function getBuyer()
	{
		return $this->buyer;
	}


	/**
	 * Objeto {@link Customer}<br>
	 * <b>Campo obrigatório</b>
	 *
	 * @param buyer
	 */
	public function setBuyer(Customer $buyer) {
		$this->buyer = $buyer;
	}


	/**
	 * Campo de Livre Digitação. Pode ser utilizado para algum parâmetro
	 * adicional de identificação da venda<br>
	 * <i>Tamanho máximo: 255 caracteres</i>
	 *
	 * @return free
	 *            , ex.: compra teste
	 */
	public function getFree() {
		return $this->free;
	}


	/**
	 * Campo de Livre Digitação. Pode ser utilizado para algum parâmetro
	 * adicional de identificação da venda<br>
	 * <i>Tamanho máximo: 255 caracteres</i>
	 *
	 * @param free
	 *            , ex.: compra teste
	 */
	public function setFree($free)
	{
		$this->free = $free;
	}


	/**
	 * Valor do frete<br>
	 *
	 * @return shipping
	 *            , ex.: 10.95
	 */
	public function getShipping()
	{
		return $this->freight;
	}


	/**
	 * Valor do frete<br>
	 *
	 * @param shipping
	 *            , ex.: 10.95
	 */
	public function setShipping($value)
	{
		$this->freight = $value;
	}


	/**
	 * Tipo do frete<br>
	 * <br>
	 * *Vide enum: {@link ShippingTypeEnum}
	 *
	 * @return shippingType
	 *            , ex.: ShippingTypeEnum::SEDEX
	 */
	public function getShippingType()
	{
		return $this->freightType;
	}


	/**
	 * Tipo do frete<br>
	 * <br>
	 * *Vide enum: {@link ShippingTypeEnum}
	 *
	 * @param shippingType
	 *            , ex.: ShippingTypeEnum::SEDEX
	 */
	public function setShippingType($type)
	{
		$this->freightType = $type;
	}


	/**
	 * Valor total do desconto atribuído pela loja<br>
	 *
	 *
	 * @return discount
	 *            , ex.: 2.25
	 */
	public function getDiscount()
	{
		return $this->discount;
	}


	/**
	 * Valor total do desconto atribuído pela loja<br>
	 *
	 *
	 * @param discount
	 *            , ex.: 2.25
	 */
	public function setDiscount($discount)
	{
		$this->discount = $discount;
	}


	/**
	 * Valor total do acréscimo feito pela loja
	 *
	 * @return addition
	 *            , ex.: 2.25
	 */
	public function getAddition()
	{
		return $this->addition;
	}


	/**
	 * Valor total do acréscimo feito pela loja
	 *
	 * @param addition
	 *            , ex.: 2.25
	 */
	public function setAddition($addition)
	{
		$this->addition = $addition;
	}


	/**
	 * Forma de pagamento<br>
	 * <br>
	 * *Vide enum: {@link PaymentMethodEnum}
	 *
	 * @return paymentMethod
	 *            , ex.: PaymentMethodEnum::VISA
	 */
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}


	/**
	 * Forma de pagamento<br>
	 * <br>
	 * *Vide enum: {@link PaymentMethodEnum}
	 *
	 * @param paymentMethod
	 *            , ex.: PaymentMethodEnum::VISA
	 */
	public function setPaymentMethod($paymentMethod)
	{
		$this->paymentMethod = new PaymentMethod($paymentMethod);
	}


	/**
	 * URL que direciona o usuário para a loja<br>
	 * <i>Tamanho máximo: 255 caracteres</i>
	 *
	 * @return urlreturn
	 *            , ex.: https://www.bcash.com.br/loja/retorno.php
	 */
	public function getUrlReturn()
	{
		return $this->urlReturn;
	}


	/**
	 * URL que direciona o usuário para a loja<br>
	 * <i>Tamanho máximo: 255 caracteres</i>
	 *
	 * @param urlreturn
	 *            , ex.: https://www.bcash.com.br/loja/retorno.php
	 */
	public function setUrlReturn($urlReturn)
	{
		$this->urlReturn = $urlReturn;
	}


	/**
	 * URL que a loja ir receber as informações de alteração de status da
	 * transação<br>
	 * <i>Tamanho máximo: 255 caracteres</i>
	 *
	 *
	 * @return urlNotification
	 *            , https://www.bcash.com.br/loja/aviso.php
	 */
	public function getUrlNotification()
	{
		return $this->urlNotification;
	}


	/**
	 * URL que a loja ir receber as informações de alteração de status da
	 * transação<br>
	 * <i>Tamanho máximo: 255 caracteres</i>
	 *
	 *
	 * @param urlNotification
	 *            , https://www.bcash.com.br/loja/aviso.php
	 */
	public function setUrlNotification($urlNotification)
	{
		$this->urlNotification = $urlNotification;
	}


	/**
	 * Lista de produtos.<br>
	 *
	 * *Obs.: Preencher a lista com o objeto {@link Product}
	 *
	 * @return products
	 */
	public function getProducts()
	{
		return $this->products;
	}


	/**
	 * Lista de produtos.<br>
	 * <b>Campo obrigatório</b><br>
	 *
	 * *Obs.: Preencher a lista com o objeto {@link Product}
	 *
	 * @param products
	 */
	public function setProducts(Array $products)
	{
		$this->products = $products;
	}

	/**
	 * Lista de transacoes dependentes.<br>
	 *
	 * *Obs.: Preencher a lista com o objeto {@link DependentTransaction}
	 *
	 * @return dependentTransactions
	 */
	public function getDependentTransactions()
	{
		return $this->dependentTransactions;
	}
	
	/**
	 * Lista de transacoes dependentes.<br>
	 * 
	 * *Obs.: Preencher a lista com o objeto {@link DependentTransaction}
	 *
	 * @param DependentTransaction
	 */
	public function setDependentTransactions(Array $dependentTransactions)
	{
		$this->dependentTransactions = $dependentTransactions;
	}
	
	/**
	 * Quantidade de Parcelas que a compra será processada<br>
	 *
	 *
	 * @return installments
	 *            , ex.: 5
	 */
	public function getInstallments()
	{
		return $this->installments;
	}

	/**
	 * Quantidade de Parcelas que a compra será processada<br>
	 *
	 *
	 * @param installments
	 *            , ex.: 5
	 */
	public function setInstallments($installments)
	{
		$this->installments = $installments;
	}


	/**
	 * Objeto {@link CreditCard}
	 *
	 * @return creditCard
	 */
	public function getCreditCard()
	{
		return $this->creditCard;
	}


	/**
	 * Objeto {@link CreditCard}
	 *
	 * @param creditCard
	 */
	public function setCreditCard(CreditCard $creditCard)
	{
		$this->creditCard = $creditCard;
	}


	/**
	 * Moeda utilizada para a transação<br>
	 * <i>Tamanho máximo: 3 caracteres</i><br>
	 * <br>
	 *
	 * *Vide enum: {@link CurrencyEnum}
	 *
	 * @return currency
	 *            , ex.: CurrencyEnum.REAL
	 */
	public function getCurrency()
	{
		return $this->currency;
	}


	/**
	 * Moeda utilizada para a transação<br>
	 * <i>Tamanho máximo: 3 caracteres</i><br>
	 * <br>
	 *
	 * *Vide enum: {@link CurrencyEnum}
	 *
	 * @param currency
	 *            , ex.: CurrencyEnum.REAL
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}


	/**
	 * Loja informa se o comprador aceitou os termos do contrato<br>
	 *
	 * @return acceptedContract
	 *            , ex.: "S"
	 */
	public function getAcceptedContract()
	{
		return $this->acceptedContract;
	}


	/**
	 * Loja informa se o comprador aceitou os termos do contrato<br>
	 *
	 * @param acceptedContract
	 *            , ex.: "S"
	 */
	public function setAcceptedContract($acceptedContract)
	{
		$this->acceptedContract = $acceptedContract;
	}


	/**
	 * Loja informa se o comprador visualizou os termos do contrato<br>
	 *
	 * @return viewedContract
	 *            , ex.: "S"
	 */
	public function getViewedContract()
	{
		return $this->viewedContract;
	}


	/**
	 * Loja informa se o comprador visualizou os termos do contrato<br>
	 *
	 * @param viewedContract
	 *            , ex.: "S"
	 */
	public function setViewedContract($viewedContract)
	{
		$this->viewedContract = $viewedContract;
	}


	/**
	 * Identificador da campanha da loja no Pagamento Digital
	 *
	 * @return campaignId
	 *            , ex.: 123
	 */
	public function getCampaignId()
	{
		return $this->campaignId;
	}


	/**
	 * Identificador da campanha da loja no Pagamento Digital
	 *
	 * @param campaignId
	 *            , ex.: 123
	 */
	public function setCampaignId($campaignId)
	{
		$this->campaignId = $campaignId;
	}


	/**
	 * Endereço de IP da loja<br>
	 * <i>Tamanho máximo: 40 caracteres</i><br>
	 *
	 * @return ipSeller
	 *            , ex.: 169.254.57.175
	 */
	public function getIpSeller()
	{
		return $this->ipSeller;
	}


	/**
	 * Endereço de IP da loja<br>
	 * <i>Tamanho máximo: 40 caracteres</i><br>
	 * <b>Campo obrigatório</b>
	 *
	 * @param ipSeller
	 *            , ex.: 169.254.57.175
	 */
	public function setIpSeller($ipSeller)
	{
		$this->ipSeller = $ipSeller;
	}


	/**
	 * Recupera identificador da plataforma cadastrada no Bcash<br>
	 *
	 * @return platformId
	 *            , ex.: 999
	 */
	public function getPlatformId()
	{
		return $this->platformId;
	}


	/**
	 * Atribui identificador da plataforma cadastrada no Bcash<br>
	 *
	 * @param platformId
	 *            , ex.: 999
	 */
	public function setPlatformId($platformId)
	{
		$this->platformId = $platformId;
	}

}
