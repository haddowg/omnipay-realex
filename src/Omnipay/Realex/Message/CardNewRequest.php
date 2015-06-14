<?php

namespace Omnipay\Realex\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Global Iris New Payer Request
 */
class CardNewRequest extends RemoteAbstractRequest
{
    protected $endpoint = 'https://remote.globaliris.com/realvault';

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
        $orderId = $this->getTransactionId().'card-new';
        $secret = $this->getSecret();
        $payerRef = $this->getPayerRef();
        $cardRef = $this->getCardRef();
        $card = $this->getCard();
        $chname = $card->getBillingName();
        $number = $card->getNumber();
        $tmp = "$timestamp.$merchantId.$orderId...$payerRef.$chname.$number";
        $md5hash = md5($tmp);
        $tmp2 = "$md5hash.$secret";
        $md5hash = md5($tmp2);
        $domTree = new \DOMDocument('1.0', 'UTF-8');

        // root element
        $root = $domTree->createElement('request');
        $root->setAttribute('type', 'card-new');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $merchantEl = $domTree->createElement('merchantid', $merchantId);
        $root->appendChild($merchantEl);

        // order ID
        $merchantEl = $domTree->createElement('orderid', $orderId);
        $root->appendChild($merchantEl);

        //payer
        $cardEl = $domTree->createElement('card');

        $refEl = $domTree->createElement('ref',$cardRef);
        $cardEl->appendChild($refEl);

        $payerRefEl = $domTree->createElement('payerref',$payerRef);
        $cardEl->appendChild($payerRefEl);

        $cardNumberEl = $domTree->createElement('number', $number);
        $cardEl->appendChild($cardNumberEl);

        $expiryEl = $domTree->createElement('expdate', $card->getExpiryDate("my")); // mmyy
        $cardEl->appendChild($expiryEl);

        $cardNameEl = $domTree->createElement('chname', $chname);
        $cardEl->appendChild($cardNameEl);

        $cardTypeEl = $domTree->createElement('type', $this->getCardBrand());
        $cardEl->appendChild($cardTypeEl);

        $issueEl = $domTree->createElement('issueno', $card->getIssueNumber());
        $cardEl->appendChild($issueEl);

        $root->appendChild($cardEl);

        $md5El = $domTree->createElement('md5hash', $md5hash);
        $root->appendChild($md5El);

        $xmlString = $domTree->saveXML($root);

        return $xmlString;
    }

    protected function createResponse($data)
    {
        return $this->response = new RealVaultResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
