<?php

namespace Omnipay\Realex\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Global Iris New Payer Request
 */
class PayerNewRequest extends RemoteAbstractRequest
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
        $orderId = $this->getTransactionId().'payer-new';
        $secret = $this->getSecret();
        $payerRef = $this->getPayerRef();
        $title = $this->getCard()->getTitle();
        $firstname = $this->getCard()->getFirstName();
        $surname =  $this->getCard()->getLastName();
        $address1 = $this->getCard()->getBillingAddress1();
        $address2 = $this->getCard()->getBillingAddress2();
        $city = $this->getCard()->getBillingCity();
        $pcode = $this->getCard()->getBillingPostcode();
        $state = $this->getCard()->getBillingState();
        $country = $this->getCard()->getBillingCountry();
        $phone = $this->getCard()->getBillingPhone();
        $email  = $this->getCard()->getEmail();
        $tmp = "$timestamp.$merchantId.$orderId...$payerRef";
        $md5hash = md5($tmp);
        $tmp2 = "$md5hash.$secret";
        $md5hash = md5($tmp2);
        $domTree = new \DOMDocument('1.0', 'UTF-8');

        // root element
        $root = $domTree->createElement('request');
        $root->setAttribute('type', 'payer-new');
        $root->setAttribute('timestamp', $timestamp);
        $root = $domTree->appendChild($root);

        // merchant ID
        $merchantEl = $domTree->createElement('merchantid', $merchantId);
        $root->appendChild($merchantEl);

        // order ID
        $merchantEl = $domTree->createElement('orderid', $orderId);
        $root->appendChild($merchantEl);

        //payer
        $payer = $domTree->createElement('payer');
        $payer->setAttribute('type', 'Business');
        $payer->setAttribute('ref',$payerRef);

            $titleEl = $domTree->createElement('title',$title);
            $payer->appendChild($titleEl);

            $firstnameEl = $domTree->createElement('firstname',$firstname);
            $payer->appendChild($firstnameEl);

            $surnameEl = $domTree->createElement('surname',$surname);
            $payer->appendChild($surnameEl);

            //address
            $addressEl = $domTree->createElement('address');
                $line1El = $domTree->createElement('line1', $address1);
                $addressEl->appendChild($line1El);
                $line2El = $domTree->createElement('line2', $address2);
                $addressEl->appendChild($line2El);
                $cityEl = $domTree->createElement('city', $city);
                $addressEl->appendChild($cityEl);
                $countyEl = $domTree->createElement('county', $state);
                $addressEl->appendChild($countyEl);
                $pcodeEl = $domTree->createElement('postcode', $pcode);
                $addressEl->appendChild($pcodeEl);
                $countryEl = $domTree->createElement('country', get_country($country));
                $countryEl->setAttribute('code',$country);
                $addressEl->appendChild($countryEl);
            $payer->appendChild($addressEl);

            //phone numbers
            $phonesEl = $domTree->createElement('phonenumbers');
                $homephoneEl = $domTree->createElement('home', $phone);
                $phonesEl->appendChild($homephoneEl);
            $payer->appendChild($phonesEl);

            $emailEl = $domTree->createElement('email',$email);
            $payer->appendChild($emailEl);


        $root->appendChild($payer);

        $md5El = $domTree->createElement('md5hash', $md5hash);
        $root->appendChild($md5El);

        $xmlString = $domTree->saveXML($root);
    //error_log($xmlString);
        return $xmlString;
    }

    protected function createResponse($data)
    {
        $response =  $this->response = new RealVaultResponse($this, $data);
        $response_payer = $response;
        if($response->isSuccessful()){
            $request = new CardNewRequest($this->httpClient, $this->httpRequest);
            $request->initialize($this->getParameters());

            $response = $request->send();
            $response->setTrigger($response_payer);
        }
        return $response;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
