<?php

namespace Omnipay\Realex\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Global Iris New Payer Request
 */
class PayerPurchaseRequest extends RemoteAbstractRequest
{
    protected $endpoint = 'https://remote.globaliris.com/realvault';


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

    public function getSequence(){
        return $this->getParameter('sequence');
    }

    public function setSequence($value){
        $seq = in_array($value,array('first','subsequent','last'))?$value:'subsequent';
        return $this->setParameter('sequence', $seq);
    }
    /**
     * Get the XML registration string to be sent to the gateway
     *
     * @return string
     */
    public function getData()
    {
        //$this->validate('amount', 'currency', 'transactionId');

        // Create the hash
        $timestamp = strftime("%Y%m%d%H%M%S");
        $merchantId = $this->getMerchantId();
        $orderId = $this->getTransactionId().'receipt-in';
        $secret = $this->getSecret();
        $amount = $this->getAmountInteger();
        $currency = $this->getCurrency();

        $payerRef = $this->getPayerRef();
        $cardRef = $this->getCardRef();
        $sequence = $this->getSequence();

        $tmp = "$timestamp.$merchantId.$orderId.$amount.$currency.$payerRef";
        $md5hash = md5($tmp);
        $tmp2 = "$md5hash.$secret";
        $md5hash = md5($tmp2);
        $domTree = new \DOMDocument('1.0', 'UTF-8');

        // root element
        $root = $domTree->createElement('request');
        $root->setAttribute('type', 'receipt-in');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $merchantEl = $domTree->createElement('merchantid', $merchantId);
        $root->appendChild($merchantEl);

        // account
        $merchantEl = $domTree->createElement('account', $this->getAccount());
        $root->appendChild($merchantEl);

        // order ID
        $merchantEl = $domTree->createElement('orderid', $orderId);
        $root->appendChild($merchantEl);

        $settleEl = $domTree->createElement('autosettle');
        $settleEl->setAttribute('flag', 1);
        $root->appendChild($settleEl);

        // amount
        $amountEl = $domTree->createElement('amount', $amount);
        $amountEl->setAttribute('currency', $this->getCurrency());
        $root->appendChild($amountEl);

        // payerRef
        $payerEl = $domTree->createElement('payerref', $payerRef);
        $root->appendChild($payerEl);

        // cardRef
        $cardEl = $domTree->createElement('paymentmethod', $cardRef);
        $root->appendChild($cardEl);

        $recurringEl = $domTree->createElement('recurring');
        $recurringEl->setAttribute('type', 'variable');
        $recurringEl->setAttribute('sequence', $sequence);
        $root->appendChild($recurringEl);

        $md5El = $domTree->createElement('md5hash', $md5hash);
        $root->appendChild($md5El);

        $xmlString = $domTree->saveXML($root);
        //error_log($xmlString);
        return $xmlString;
    }

    protected function createResponse($data)
    {
        $response =  $this->response = new RealVaultResponse($this, $data);
        return $response;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
