<?php

namespace Omnipay\Realex\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Realex Purchase Request
 */
abstract class RemoteAbstractRequest extends AbstractRequest
{
    protected $cardBrandMap = array(
        'mastercard' => 'mc',
        'diners_club' => 'diners'
    );

    /**
     * Override some of the default Omnipay card brand names
     *
     * @return mixed
     */
    protected function getCardBrand()
    {
        $brand = $this->getCard()->getBrand();

        if (isset($this->cardBrandMap[$brand])) {
            $brand = $this->cardBrandMap[$brand];
        }

        return strtoupper($brand);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getAccount()
    {
        return $this->getParameter('account');
    }

    public function setAccount($value)
    {
        return $this->setParameter('account', $value);
    }

    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }

    public function getAdditional()
    {
        return $this->getParameter('additional');
    }

    public function setAdditional($value)
    {
        return $this->setParameter('additional', $value);
    }

    public function getPayerRef(){
        return $this->getParameter('payerRef');
    }

    public function setPayerRef($value){
        if(preg_match('/^'.$this->getAccount().'-/',$value)) {
            return $this->setParameter('payerRef', $value);
        }else{
            return $this->setParameter('payerRef', $this->getAccount() .'-' . $value);
        }
    }

    public function getCardRef(){
        return $this->getParameter('cardRef');
    }

    public function setCardRef($value){
        return $this->setParameter('cardRef', $value);
    }

    public function sendData($data)
    {
        // register the payment
        $this->httpClient->setConfig(array(
            'curl.options' => array(
                'CURLOPT_SSLVERSION'     => 1,
                'CURLOPT_SSL_VERIFYPEER' => false
            )
        ));
        $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $data)->send();

        return $this->createResponse($httpResponse->getBody(true));
    }

    abstract public function getEndpoint();

    abstract protected function createResponse($data);
}
