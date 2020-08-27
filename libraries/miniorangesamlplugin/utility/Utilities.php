<?php
/**
 * This file is part of miniOrange SAML plugin.
 *
 * miniOrange SAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * miniOrange SAML plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

include "xmlseclibs.php";
class Utilities {
	
	public static function generateID() {
		return '_' . self::stringToHex(self::generateRandomBytes(21));
	}
	
	public static function stringToHex($bytes) {
		$ret = '';
		for($i = 0; $i < strlen($bytes); $i++) {
			$ret .= sprintf('%02x', ord($bytes[$i]));
		}
		return $ret;
	}
	
	public static function generateRandomBytes($length, $fallback = TRUE) {
		//assert('is_int($length)');
        return openssl_random_pseudo_bytes($length);
	}
	

	public static function createAuthnRequest($acsUrl, $issuer,  $destination, $force_authn = 'false',$sso_binding_type = 'HttpRedirect') {
		$requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
				'<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="' . self::generateID() .
				'" Version="2.0" IssueInstant="' . self::generateTimestamp() . '"';
		if( $force_authn == 'true') {
			$requestXmlStr .= ' ForceAuthn="true"';
		}
		$requestXmlStr .= ' ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $acsUrl . 
						'" Destination="' . $destination . '"><saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer><samlp:NameIDPolicy AllowCreate="true" Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"
                        /></samlp:AuthnRequest>';
		if(empty($sso_binding_type) || $sso_binding_type == 'HttpRedirect') {
			
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}
		return $requestXmlStr;
	}
	
	public static function createLogoutRequest($nameId, $sessionIndex = '', $issuer, $destination,$slo_binding_type = 'HttpRedirect'){
		$requestXmlStr='<?xml version="1.0" encoding="UTF-8"?>' .
				'<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="'. self::generateID() .
				'" IssueInstant="' . self::generateTimestamp() .
				'" Version="2.0" Destination="'. $destination . '">
						<saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer>
						<saml:NameID xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">'. $nameId . '</saml:NameID>';
		if(!empty($sessionIndex)) {
			$requestXmlStr .= '<samlp:SessionIndex>' . $sessionIndex . '</samlp:SessionIndex>';
		}
		$requestXmlStr .= '</samlp:LogoutRequest>';
	
		if(empty($slo_binding_type) || $slo_binding_type == 'HttpRedirect') {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}
		return $requestXmlStr;
	}
	
	public static function createLogoutResponse( $inResponseTo, $issuer, $destination, $slo_binding_type = 'HttpRedirect'){
	
		$requestXmlStr='<?xml version="1.0" encoding="UTF-8"?>' .
				'<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ' .
				'ID="' . self::generateID() . '" ' .
				'Version="2.0" IssueInstant="' . self::generateTimestamp() . '" ' .
				'Destination="' . $destination . '" ' .
				'InResponseTo="' . $inResponseTo . '">' .
				'<saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer>' .
				'<samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status></samlp:LogoutResponse>';
	
		if(empty($slo_binding_type) || $slo_binding_type == 'HttpRedirect') {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
		}
		return $requestXmlStr;
	}
	
	public static function generateTimestamp($instant = NULL) {
		if($instant === NULL) {
			$instant = time();
		}
		return gmdate('Y-m-d\TH:i:s\Z', $instant);
	}
	
	public static function xpQuery(DOMNode $node, $query)
    {
        //assert('is_string($query)');
        static $xpCache = NULL;

        if ($node instanceof DOMDocument) {
            $doc = $node;
        } else {
            $doc = $node->ownerDocument;
        }

        if ($xpCache === NULL || !$xpCache->document->isSameNode($doc)) {
            $xpCache = new DOMXPath($doc);
            $xpCache->registerNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpCache->registerNamespace('saml_protocol', 'urn:oasis:names:tc:SAML:2.0:protocol');
            $xpCache->registerNamespace('saml_assertion', 'urn:oasis:names:tc:SAML:2.0:assertion');
            $xpCache->registerNamespace('saml_metadata', 'urn:oasis:names:tc:SAML:2.0:metadata');
            $xpCache->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
            $xpCache->registerNamespace('xenc', 'http://www.w3.org/2001/04/xmlenc#');
        }

        $results = $xpCache->query($query, $node);
        $ret = array();
        for ($i = 0; $i < $results->length; $i++) {
            $ret[$i] = $results->item($i);
        }

		return $ret;
    }
	
	public static function parseNameId(DOMElement $xml)
    {
        $ret = array('Value' => trim($xml->textContent));

        foreach (array('NameQualifier', 'SPNameQualifier', 'Format') as $attr) {
            if ($xml->hasAttribute($attr)) {
                $ret[$attr] = $xml->getAttribute($attr);
            }
        }

        return $ret;
    }
	
	public static function xsDateTimeToTimestamp($time)
    {
        $matches = array();

        // We use a very strict regex to parse the timestamp.
        $regex = '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?Z$/D';
        if (preg_match($regex, $time, $matches) == 0) {
            throw new Exception(
                'Invalid SAML2 timestamp passed to xsDateTimeToTimestamp: ' . $time
            );
        }

        // Extract the different components of the time from the  matches in the regex.
        // intval will ignore leading zeroes in the string.
        $year   = intval($matches[1]);
        $month  = intval($matches[2]);
        $day    = intval($matches[3]);
        $hour   = intval($matches[4]);
        $minute = intval($matches[5]);
        $second = intval($matches[6]);

        // We use gmmktime because the timestamp will always be given
        //in UTC.
        $ts = gmmktime($hour, $minute, $second, $month, $day, $year);

        return $ts;
    }
	
	public static function extractStrings(DOMElement $parent, $namespaceURI, $localName)
    {
        //assert('is_string($namespaceURI)');
        //assert('is_string($localName)');

        $ret = array();
        for ($node = $parent->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->namespaceURI !== $namespaceURI || $node->localName !== $localName) {
                continue;
            }
            $ret[] = trim($node->textContent);
        }

        return $ret;
    }
	
	public static function validateElement(DOMElement $root)
    {
    
        /* Create an XML security object. */
        $objXMLSecDSig = new XMLSecurityDSig();

        /* Both SAML messages and SAML assertions use the 'ID' attribute. */
        $objXMLSecDSig->idKeys[] = 'ID';
		
       
        /* Locate the XMLDSig Signature element to be used. */
        $signatureElement = self::xpQuery($root, './ds:Signature');
        

        if (count($signatureElement) === 0) {
            /* We don't have a signature element to validate. */
            return FALSE;
        } elseif (count($signatureElement) > 1) {
        	echo "XMLSec: more than one signature element in root.";
        	exit;
        }
       
        $signatureElement = $signatureElement[0];
        $objXMLSecDSig->sigNode = $signatureElement;
		
        /* Canonicalize the XMLDSig SignedInfo element in the message. */
        $objXMLSecDSig->canonicalizeSignedInfo();
		
       /* Validate referenced xml nodes. */
        if (!$objXMLSecDSig->validateReference()) { 
        	echo "XMLsec: digest validation failed";
        	exit;
        }
		
		/* Check that $root is one of the signed nodes. */
        $rootSigned = FALSE;
        /** @var DOMNode $signedNode */
        foreach ($objXMLSecDSig->getValidatedNodes() as $signedNode) {
            if ($signedNode->isSameNode($root)) {
                $rootSigned = TRUE;
                break;
            } elseif ($root->parentNode instanceof DOMDocument && $signedNode->isSameNode($root->ownerDocument)) {
                /* $root is the root element of a signed document. */
                $rootSigned = TRUE;
                break;
            }
        }
		
		if (!$rootSigned) {
			echo "XMLSec: The root element is not signed.";
			exit;
        }

        /* Now we extract all available X509 certificates in the signature element. */
        $certificates = array();
        foreach (self::xpQuery($signatureElement, './ds:KeyInfo/ds:X509Data/ds:X509Certificate') as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(array("\r", "\n", "\t", ' '), '', $certData);
            $certificates[] = $certData;
			
        }
	
        $ret = array(
            'Signature' => $objXMLSecDSig,
            'Certificates' => $certificates,
            );
		

			
			
        return $ret;
    }
	

	
	public static function validateSignature(array $info, XMLSecurityKey $key)
    {
        //assert('array_key_exists("Signature", $info)');

        /** @var XMLSecurityDSig $objXMLSecDSig */
        $objXMLSecDSig = $info['Signature'];

        $sigMethod = self::xpQuery($objXMLSecDSig->sigNode, './ds:SignedInfo/ds:SignatureMethod');
        if (empty($sigMethod)) {
            throw new Exception('Missing SignatureMethod element.');
        }
        $sigMethod = $sigMethod[0];
        if (!$sigMethod->hasAttribute('Algorithm')) {
            throw new Exception('Missing Algorithm-attribute on SignatureMethod element.');
        }
        $algo = $sigMethod->getAttribute('Algorithm');

        if ($key->type === XMLSecurityKey::RSA_SHA1 && $algo !== $key->type) {
            $key = self::castKey($key, $algo);
        }
		
        /* Check the signature. */
        if (! $objXMLSecDSig->verify($key)) {
            throw new Exception("Unable to validate Signature");
        }
    }
	
    public static function castKey(XMLSecurityKey $key, $algorithm, $type = 'public')
    {
    	//assert('is_string($algorithm)');
    	//assert('$type === "public" || $type === "private"');
    
    	// do nothing if algorithm is already the type of the key
    	if ($key->type === $algorithm) {
    		return $key;
    	}
    
    	$keyInfo = openssl_pkey_get_details($key->key);
    	if ($keyInfo === FALSE) {
    		throw new Exception('Unable to get key details from XMLSecurityKey.');
    	}
    	if (!isset($keyInfo['key'])) {
    		throw new Exception('Missing key in public key details.');
    	}
    
    	$newKey = new XMLSecurityKey($algorithm, array('type'=>$type));
    	$newKey->loadKey($keyInfo['key']);
    
    	return $newKey;
    }
    
	/*public static function processResponse($currentURL, $certFingerprint, $signatureData,
		SAML2_Response $response) {
		assert('is_string($currentURL)');
		assert('is_string($certFingerprint)');
		
		
		
		$msgDestination = $response->getDestination();
		if ($msgDestination !== NULL && $msgDestination !== $currentURL) {
			echo 'Destination in response doesn\'t match the current URL. Destination is "' .
				$msgDestination . '", current URL is "' . $currentURL . '".';
				exit;
		}
		
		$responseSigned = self::checkSign($certFingerprint, $signatureData);
		
		
		return $responseSigned;
	} */

	public static function processResponse($currentURL, $certFingerprint, $signatureData,
		SAML2_Response $response, $certFromPlugin, $relayState) {
		//assert('is_string($currentURL)');
		//assert('is_string($certFingerprint)');
		
		
		/* Validate Response-element destination. */
		$msgDestination = $response->getDestination();
		if ($msgDestination !== NULL && $msgDestination !== $currentURL) {
			echo 'Destination in response doesn\'t match the current URL. Destination is "' .
				$msgDestination . '", current URL is "' . $currentURL . '".';
				exit;
		}
		
		$responseSigned = self::checkSign($certFingerprint, $signatureData, $certFromPlugin, $relayState);
		
		/* Returning boolean $responseSigned */
		return $responseSigned;
	}
	
		public static function checkSign($certFingerprint, $signatureData, $certFromPlugin, $relayState) {
		
		$certificates = $signatureData['Certificates'];	

		if (count($certificates) === 0) {
			$pemCert = $certFromPlugin;
		} else {
			$fpArray = array();
			$fpArray[] = $certFingerprint;
			$pemCert = self::findCertificate($fpArray, $certificates, $relayState);
		}
		
		$lastException = NULL;
		
		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'public'));
		$key->loadKey($pemCert);
				
		try {
			/*
			 * Make sure that we have a valid signature
			 */
			//assert('$key->type === XMLSecurityKey::RSA_SHA1');
			self::validateSignature($signatureData, $key);
			return TRUE;
		} catch (Exception $e) {
			echo 'Validation with key failed with exception: ' . $e->getMessage();
			$lastException = $e;
		}
		
		
		/* We were unable to validate the signature with any of our keys. */
		if ($lastException !== NULL) {
			throw $lastException;
		} else {
			return FALSE;
		}
	
	}
	
	/*public static function checkSign($certFingerprint, $signatureData) {
		
		$certificates = $signatureData['Certificates'];	

		if (count($certificates) === 0) {
			echo 'No certificate in message when validating against fingerprint.';
			return FALSE;
		} 

		$fpArray = array();
		$fpArray[] = $certFingerprint;
		$pemCert = self::findCertificate($fpArray, $certificates);
		
		$lastException = NULL;
		
		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'public'));
		$key->loadKey($pemCert);
				
		try {
			/*
			 * Make sure that we have a valid signature
			 
			assert('$key->type === XMLSecurityKey::RSA_SHA1');
			self::validateSignature($signatureData, $key);
			return TRUE;
		} catch (Exception $e) {
			echo 'Validation with key failed with exception: ' . $e->getMessage();
			$lastException = $e;
		}
		
		
		/* We were unable to validate the signature with any of our keys. 
		if ($lastException !== NULL) {
			throw $lastException;
		} else {
			return FALSE;
		}
	
	}
	*/
	public static function validateIssuerAndAudience($samlResponse, $spEntityId, $issuerToValidateAgainst, $relayState) {
		
		
		$issuer = current($samlResponse->getAssertions())->getIssuer();
		$audience = current(current($samlResponse->getAssertions())->getValidAudiences());
		if(strcmp($issuerToValidateAgainst, $issuer) === 0) {
			
			if(strcmp($audience, $spEntityId) === 0) {
				return TRUE;
			} else {
				if($relayState=='testValidate'){
                    ob_end_clean();
           
                    echo '<div style="font-family:Calibri;padding:0 3%;">';
                    echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Invalid Audience URI.</p>
                    <p>Please contact your administrator and report the following error:</p>
                    <p><strong>Possible Cause: </strong>The value of \'Audience URI\' field on Identity Provider\'s side is incorrect</p>
                    <p>Expected one of the Audiences to be: '.$spEntityId.'<p>
                    </div>
                    <div style="margin:3%;display:block;text-align:center;">
                    <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';   
                    exit;
                }
                else
                {
                    echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>We could not sign you in. Please contact your Administrator.</p></div>
                  <div style="margin:3%;display:block;text-align:center;">
                        <form action='.JURI::root().'><input style="padding:1%;width:150px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to home"></form></div>';
                  exit;
                }
			}
		} else {
			if($relayState=='testValidate'){
                 ob_end_clean();
           
                echo '<div style="font-family:Calibri;padding:0 3%;">';
                echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Issuer cannot be verified.</p>
                <p>Please contact your administrator and report the following error:</p>
                <p><strong>Possible Cause: </strong>The value in \'IdP Entity ID or Issuer\' field in Service Provider Settings is incorrect</p>
                <p><strong>Expected Entity ID: </strong>'.$issuer.'<p>
                <p><strong>Entity ID Found: </strong>'.$issuerToValidateAgainst.'</p>
                </div>
                <div style="margin:3%;display:block;text-align:center;">
                <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
                 exit;
		}
         else
                {
                    echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>We could not sign you in. Please contact your Administrator.</p></div>
                  <div style="margin:3%;display:block;text-align:center;">
                        <form action='.JURI::root().'><input style="padding:1%;width:150px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to home"></form></div>';
                  exit;
                }
		}
	}
	
	private static function findCertificate(array $certFingerprints, array $certificates, $relayState) {

		$candidates = array();
		
		foreach ($certificates as $cert) {
			$fp = strtolower(sha1(base64_decode($cert)));
			if (!in_array($fp, $certFingerprints, TRUE)) {
				$candidates[] = $fp;
				continue;
			}

			/* We have found a matching fingerprint. */
			$pem = "-----BEGIN CERTIFICATE-----\n" .
				chunk_split($cert, 64) .
				"-----END CERTIFICATE-----\n";
			
			return $pem;
		}

		if($relayState=='testValidate'){
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Unable to find a certificate matching the configured fingerprint.</p>
            <p>Please contact your administrator and report the following error:</p>
            <p><strong>Possible Cause: </strong>Content of \'X.509 Certificate\' field in Service Provider Settings is incorrect</p>
            <p>You can download X.509 certificate from Identity Provider\'s site</p>';
            echo str_repeat('&nbsp;', 15);
            echo'<strong>OR</strong>
            <p>You can find X.509 certificate enclosed in X509Certificate tag in IdP-Metadata XML file.(parent tag: KeyDescriptor use="signing")</p>
                </div>
                <div style="margin:3%;display:block;text-align:center;">
                <form action="index.php">
                <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
                exit;
            }
            else{
                echo ' <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>We could not sign you in. Please contact your Administrator.</p></div>
                  <div style="margin:3%;display:block;text-align:center;">
                        <form action='.JURI::root().'><input style="padding:1%;width:150px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to home"></form></div>';
                  exit;
            }
 	}
	
	
	    /**
     * Decrypt an encrypted element.
     *
     * This is an internal helper function.
     *
     * @param  DOMElement     $encryptedData The encrypted data.
     * @param  XMLSecurityKey $inputKey      The decryption key.
     * @param  array          &$blacklist    Blacklisted decryption algorithms.
     * @return DOMElement     The decrypted element.
     * @throws Exception
     */
    private static function doDecryptElement(DOMElement $encryptedData, XMLSecurityKey $inputKey, array &$blacklist)
    {	
        $enc = new XMLSecEnc();
        $enc->setNode($encryptedData);
		
        $enc->type = $encryptedData->getAttribute("Type");
        $symmetricKey = $enc->locateKey($encryptedData);
        if (!$symmetricKey) {
            throw new Exception('Could not locate key algorithm in encrypted data.');
        }
		
        $symmetricKeyInfo = $enc->locateKeyInfo($symmetricKey);
        if (!$symmetricKeyInfo) {
            throw new Exception('Could not locate <dsig:KeyInfo> for the encrypted key.');
        }
        $inputKeyAlgo = $inputKey->getAlgorith();
        if ($symmetricKeyInfo->isEncrypted) {
            $symKeyInfoAlgo = $symmetricKeyInfo->getAlgorith();
            if (in_array($symKeyInfoAlgo, $blacklist, TRUE)) {
                throw new Exception('Algorithm disabled: ' . var_export($symKeyInfoAlgo, TRUE));
            }
            if ($symKeyInfoAlgo === XMLSecurityKey::RSA_OAEP_MGF1P && $inputKeyAlgo === XMLSecurityKey::RSA_1_5) {
                /*
                 * The RSA key formats are equal, so loading an RSA_1_5 key
                 * into an RSA_OAEP_MGF1P key can be done without problems.
                 * We therefore pretend that the input key is an
                 * RSA_OAEP_MGF1P key.
                 */
                $inputKeyAlgo = XMLSecurityKey::RSA_OAEP_MGF1P;
            }
            /* Make sure that the input key format is the same as the one used to encrypt the key. */
            if ($inputKeyAlgo !== $symKeyInfoAlgo) {
                throw new Exception(
                    'Algorithm mismatch between input key and key used to encrypt ' .
                    ' the symmetric key for the message. Key was: ' .
                    var_export($inputKeyAlgo, TRUE) . '; message was: ' .
                    var_export($symKeyInfoAlgo, TRUE)
                );
            }
            /** @var XMLSecEnc $encKey */
            $encKey = $symmetricKeyInfo->encryptedCtx;
            $symmetricKeyInfo->key = $inputKey->key;
            $keySize = $symmetricKey->getSymmetricKeySize();
            if ($keySize === NULL) {
                /* To protect against "key oracle" attacks, we need to be able to create a
                 * symmetric key, and for that we need to know the key size.
                 */
                throw new Exception('Unknown key size for encryption algorithm: ' . var_export($symmetricKey->type, TRUE));
            }
            try {
                $key = $encKey->decryptKey($symmetricKeyInfo);
                if (strlen($key) != $keySize) {
                    throw new Exception(
                        'Unexpected key size (' . strlen($key) * 8 . 'bits) for encryption algorithm: ' .
                        var_export($symmetricKey->type, TRUE)
                    );
                }
            } catch (Exception $e) {
                /* We failed to decrypt this key. Log it, and substitute a "random" key. */
                
                /* Create a replacement key, so that it looks like we fail in the same way as if the key was correctly padded. */
                /* We base the symmetric key on the encrypted key and private key, so that we always behave the
                 * same way for a given input key.
                 */
                $encryptedKey = $encKey->getCipherValue();
                $pkey = openssl_pkey_get_details($symmetricKeyInfo->key);
                $pkey = sha1(serialize($pkey), TRUE);
                $key = sha1($encryptedKey . $pkey, TRUE);
                /* Make sure that the key has the correct length. */
                if (strlen($key) > $keySize) {
                    $key = substr($key, 0, $keySize);
                } elseif (strlen($key) < $keySize) {
                    $key = str_pad($key, $keySize);
                }
            }
            $symmetricKey->loadkey($key);
        } else {
            $symKeyAlgo = $symmetricKey->getAlgorith();
            /* Make sure that the input key has the correct format. */
            if ($inputKeyAlgo !== $symKeyAlgo) {
                throw new Exception(
                    'Algorithm mismatch between input key and key in message. ' .
                    'Key was: ' . var_export($inputKeyAlgo, TRUE) . '; message was: ' .
                    var_export($symKeyAlgo, TRUE)
                );
            }
            $symmetricKey = $inputKey;
        }
        $algorithm = $symmetricKey->getAlgorith();
        if (in_array($algorithm, $blacklist, TRUE)) {
            throw new Exception('Algorithm disabled: ' . var_export($algorithm, TRUE));
        }
        /** @var string $decrypted */
        $decrypted = $enc->decryptNode($symmetricKey, FALSE);
        /*
         * This is a workaround for the case where only a subset of the XML
         * tree was serialized for encryption. In that case, we may miss the
         * namespaces needed to parse the XML.
         */
        $xml = '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" '.
                     'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            $decrypted .
            '</root>';
        $newDoc = new DOMDocument();
        if (!@$newDoc->loadXML($xml)) {
            throw new Exception('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
        }
        $decryptedElement = $newDoc->firstChild->firstChild;
        if ($decryptedElement === NULL) {
            throw new Exception('Missing encrypted element.');
        }

        if (!($decryptedElement instanceof DOMElement)) {
            throw new Exception('Decrypted element was not actually a DOMElement.');
        }

        return $decryptedElement;
    }
    /**
     * Decrypt an encrypted element.
     *
     * @param  DOMElement     $encryptedData The encrypted data.
     * @param  XMLSecurityKey $inputKey      The decryption key.
     * @param  array          $blacklist     Blacklisted decryption algorithms.
     * @return DOMElement     The decrypted element.
     * @throws Exception
     */
    public static function decryptElement(DOMElement $encryptedData, XMLSecurityKey $inputKey, array $blacklist = array())
    {	
        try {
            return self::doDecryptElement($encryptedData, $inputKey, $blacklist);
        } catch (Exception $e) {
            /*
             * Something went wrong during decryption, but for security
             * reasons we cannot tell the user what failed.
             */
			 //print_r($e->getMessage());
           throw new Exception('Failed to decrypt XML element.');
        }
    }
	
	 /**
     * Generates the metadata of the SP based on the settings
     *
     * @param string    $sp            The SP data
     * @param string    $authnsign     authnRequestsSigned attribute
     * @param string    $wsign         wantAssertionsSigned attribute 
     * @param DateTime  $validUntil    Metadata's valid time
     * @param Timestamp $cacheDuration Duration of the cache in seconds
     * @param array     $contacts      Contacts info
     * @param array     $organization  Organization ingo
     *
     * @return string SAML Metadata XML
     */
    public static function metadata_builder($siteUrl)
    {
		$xml = new DOMDocument();
	
		
$xml->load('saml2/sp-metadata.xml');

$xpath = new DOMXPath($xml);

$elements = $xpath->query('//md:EntityDescriptor[@entityID="none"]');
if ($elements->length >= 1) {
	
    $element = $elements->item(0);
    $element->setAttribute('entityID', $siteUrl.'/plugins/authentication/miniorangesaml/');
}

$elements = $xpath->query('//md:AssertionConsumerService[@Location="none"]');
if ($elements->length >= 1) {
    $element = $elements->item(0);
    $element->setAttribute('Location', $siteUrl.'/plugins/authentication/miniorangesaml/saml2/acs.php');
}

//re-save
$xml->save("saml2/sp-metadata.xml");

      
    }
	
/*public static function get_mapped_groups($saml_params, $saml_groups)
{
        $groups = array();

    if (!empty($saml_groups)) {
        $saml_mapped_groups = array();
        $i=1;
        while ($i < 10) {
            $saml_mapped_groups_value = $saml_params->get('group'.$i.'_map');
			
            $saml_mapped_groups[$i] = explode(';', $saml_mapped_groups_value);
            $i++;
        }
    }

    foreach ($saml_groups as $saml_group) {
        if (!empty($saml_group)) {
            $i = 0;
            $found = false;
			
            while ($i < 9 && !$found) {
                if (!empty($saml_mapped_groups[$i]) && in_array($saml_group, $saml_mapped_groups[$i])) {
					
					
					$groups[] = $saml_params->get('group'.$i);
                    $found = true;
                }
                $i++;
            }
        }
    }
	
    return array_unique($groups);
}*/

public static function get_mapped_groups($role_mapping_key_value, $saml_groups)
{
    $groups = array();
	
	foreach ($role_mapping_key_value as $mapping_key=>$mapping_value){
		if (!empty($mapping_key) && in_array($mapping_key,$saml_groups)) {
			$groups[] = $mapping_value;
		}
	}
	
    return array_unique($groups);
}

public static function get_user_profile_data($attrs, $user_profile_attributes){
	
	$profile_data = array();
	if(isset($user_profile_attributes) && !empty($user_profile_attributes)){
		$user_profile_attributes = json_decode($user_profile_attributes, true);
		foreach($user_profile_attributes as $profile_attr){
			//array_push($profile_data,$profile_attr['attr_name']);
			$key = $profile_attr['attr_name'];
			$value = $profile_attr['attr_value'];
			if(isset($attrs[$value]) && !empty($attrs[$value])){
				$anArray = array();
				$anArray['profile_key'] = $key;
				$p_val = $attrs[$value];
				if(is_array($p_val))
					$p_val = $p_val[0];
				if(isset($p_val) && !empty($p_val)){
					$anArray['profile_value'] = trim($p_val);
					array_push($profile_data, $anArray);
				}
			}
		}
	}
	return $profile_data;
}

public static function get_user_profile_data_from_table($user_id){
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	$query->select('profile_key');
	$query->from('#__user_profiles');
	$query->where($db->quoteName('user_id') . " = " . $db->quote($user_id));
	$db->setQuery($query);
	$result = $db->loadColumn();
	return $result;
	
}


public static function get_user_from_joomla($matcher,$username,$email){
			//Check if email exist in database
		$db = JFactory::getDBO();
		
		 switch ($matcher) {
        case 'username':
            $query = $db->getQuery(true)
                        ->select('id')
                        ->from('#__users')
                        ->where('username=' . $db->quote($username));
						
            break;
        case 'email':
        default:
            $query = $db->getQuery(true)
                        ->select('id')
                        ->from('#__users')
                        ->where('email=' . $db->quote($email));
						
						
            break;
		}
		
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	
}

public static function getEncryptionAlgorithm($method){
	switch($method){
		case 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc':
			return XMLSecurityKey::TRIPLEDES_CBC;
			break;
		
		case 'http://www.w3.org/2001/04/xmlenc#aes128-cbc':
			return XMLSecurityKey::AES128_CBC;
			
		case 'http://www.w3.org/2001/04/xmlenc#aes192-cbc':
			return XMLSecurityKey::AES192_CBC;
			break;
		
		case 'http://www.w3.org/2001/04/xmlenc#aes256-cbc':
			return XMLSecurityKey::AES256_CBC;
			break;
			
		case 'http://www.w3.org/2001/04/xmlenc#rsa-1_5':
			return XMLSecurityKey::RSA_1_5;
			break;
		
		case 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p':
			return XMLSecurityKey::RSA_OAEP_MGF1P;
			break;
			
		case 'http://www.w3.org/2000/09/xmldsig#dsa-sha1':
			return XMLSecurityKey::DSA_SHA1;
			break;

		case 'http://www.w3.org/2000/09/xmldsig#rsa-sha1':
			return XMLSecurityKey::RSA_SHA1;
			break;
		
		case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256':
			return XMLSecurityKey::RSA_SHA256;
			break;
			
		case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384':
			return XMLSecurityKey::RSA_SHA384;
			break;
		
		case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512':
			return XMLSecurityKey::RSA_SHA512;
			break;
		
		default:
			throw new Exception("Invalid Encryption Method: ".$method);
			break;
	}
}
	
public static function sanitize_certificate( $certificate ) {
	$certificate = preg_replace("/[\r\n]+/", "", $certificate);
	$certificate = str_replace( "-", "", $certificate );
	$certificate = str_replace( "BEGIN CERTIFICATE", "", $certificate );
	$certificate = str_replace( "END CERTIFICATE", "", $certificate );
	$certificate = str_replace( " ", "", $certificate );
	$certificate = chunk_split($certificate, 64, "\r\n");
	$certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
	return $certificate;
}

public static function desanitize_certificate( $certificate ) {
	$certificate = preg_replace("/[\r\n]+/", "", $certificate);
	//$certificate = str_replace( "-", "", $certificate );
	$certificate = str_replace( "-----BEGIN CERTIFICATE-----", "", $certificate );
	$certificate = str_replace( "-----END CERTIFICATE-----", "", $certificate );
	$certificate = str_replace( " ", "", $certificate );
	//$certificate = chunk_split($certificate, 64, "\r\n");
	//$certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
	return $certificate;
}

public static function mo_saml_show_test_result($username,$attrs,$siteUrl){
	ob_end_clean();
	$siteUrl = $siteUrl. '/plugins/authentication/miniorangesaml/';
	echo '<div style="font-family:Calibri;padding:0 3%;">';
	if(!empty($username)){
		echo '<div style="color: #3c763d;
				background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST SUCCESSFUL</div>
				<div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="'. $siteUrl . 'images/green_check.png"></div>';
	}else{
		echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">TEST FAILED</div>
				<div style="color: #a94442;font-size:14pt; margin-bottom:20px;">WARNING: Some Attributes Did Not Match.</div>
				<div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="'. $siteUrl . 'images/wrong.png"></div>';
	}
		echo '<span style="font-size:14pt;"><b>Hello</b>, '.$username.'</span><br/><p style="font-weight:bold;font-size:14pt;margin-left:1%;">ATTRIBUTES RECEIVED:</p>
				<table style="border-collapse:collapse;border-spacing:0; display:table;width:100%; font-size:14pt;background-color:#EDEDED;">
				<tr style="text-align:center;"><td style="font-weight:bold;border:2px solid #949090;padding:2%;">ATTRIBUTE NAME</td><td style="font-weight:bold;padding:2%;border:2px solid #949090; word-wrap:break-word;">ATTRIBUTE VALUE</td></tr>';
	if(!empty($attrs)){
		
		foreach ($attrs as $key => $value)
			echo "<tr><td style='font-weight:bold;border:2px solid #949090;padding:2%;'>" .$key . "</td><td style='padding:2%;border:2px solid #949090; word-wrap:break-word;'>" .implode('<br/>',(array)$value). "</td></tr>";
	} else
			echo "No Attributes Received.";
		echo '</table></div>';
		echo '<div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
		exit;
}

	public static function postSAMLRequest($url, $samlRequestXML, $relayState) {
		echo "<html><head><script src='https://code.jquery.com/jquery-1.11.3.min.js'></script><script type=\"text/javascript\">$(function(){document.forms['saml-request-form'].submit();});</script></head><body>Please wait...<form action=\"" . $url . "\" method=\"post\" id=\"saml-request-form\"><input type=\"hidden\" name=\"SAMLRequest\" value=\"" . $samlRequestXML . "\" /><input type=\"hidden\" name=\"RelayState\" value=\"" . htmlentities($relayState) . "\" /></form></body></html>";
		exit;
	}
	
	public static function postSAMLResponse($url, $samlResponseXML, $relayState) {
		echo "<html><head><script src='https://code.jquery.com/jquery-1.11.3.min.js'></script><script type=\"text/javascript\">$(function(){document.forms['saml-request-form'].submit();});</script></head><body>Please wait...<form action=\"" . $url . "\" method=\"post\" id=\"saml-request-form\"><input type=\"hidden\" name=\"SAMLResponse\" value=\"" . $samlResponseXML . "\" /><input type=\"hidden\" name=\"RelayState\" value=\"" . htmlentities($relayState) . "\" /></form></body></html>";
		exit();
	}
	/**
     * Insert a Signature-node.
     *
     * @param XMLSecurityKey $key           The key we should use to sign the message.
     * @param array          $certificates  The certificates we should add to the signature node.
     * @param DOMElement     $root          The XML node we should sign.
     * @param DOMNode        $insertBefore  The XML element we should insert the signature element before.
     */
    public static function insertSignature(
        XMLSecurityKey $key,
        array $certificates,
        DOMElement $root = NULL,
        DOMNode $insertBefore = NULL
    ) {
        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        switch ($key->type) {
            case XMLSecurityKey::RSA_SHA256:
                $type = XMLSecurityDSig::SHA256;
                break;
            case XMLSecurityKey::RSA_SHA384:
                $type = XMLSecurityDSig::SHA384;
                break;
            case XMLSecurityKey::RSA_SHA512:
                $type = XMLSecurityDSig::SHA512;
                break;
            default:
                $type = XMLSecurityDSig::SHA1;
        }

        $objXMLSecDSig->addReferenceList(
            array($root),
            $type,
            array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N),
            array('id_name' => 'ID', 'overwrite' => FALSE)
        );

        $objXMLSecDSig->sign($key);

        foreach ($certificates as $certificate) {
            $objXMLSecDSig->add509Cert($certificate, TRUE);
        }

        $objXMLSecDSig->insertSignature($root, $insertBefore);
    }
	
	
	public static function signXML($xml, $publicCertPath, $privateKeyPath, $insertBeforeTagName = "") {
		$param =array( 'type' => 'private');
		$key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, $param);
		$key->loadKey($privateKeyPath, TRUE);
		$publicCertificate = file_get_contents( $publicCertPath );
		$document = new DOMDocument();
		
		$document->loadXML($xml);
		$element = $document->firstChild;
		if( !empty($insertBeforeTagName) ) {
			$domNode = $document->getElementsByTagName( $insertBeforeTagName )->item(0);
			
			self::insertSignature($key, array ( $publicCertificate ), $element, $domNode);
		} else {
			self::insertSignature($key, array ( $publicCertificate ), $element);
		}
		$requestXML = $element->ownerDocument->saveXML($element);
		$base64EncodedXML = base64_encode($requestXML);
		return $base64EncodedXML;
	}
	
	public static function getSAMLConfiguration(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_config'));
		$query->where($db->quoteName('id')." = 1");

		$db->setQuery($query);
		$attributes = $db->loadAssoc();
		
		return $attributes;
	}

	  public static function getRoleMapping(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
					
		$db->setQuery($query);
		$role_mapping = $db->loadAssoc();
		return $role_mapping;
	}

	public static function selectMaxOrdering($user_id){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('MAX(ordering)');
		$query->from($db->quoteName('#__user_profiles'));
		$query->where($db->quoteName('user_id') . " = " . $db->quote($user_id));

		$db->setQuery($query);
		$ordering = $db->loadResult();
		
		return (isset($ordering) && !empty($ordering)) ? $ordering : '0';
		
	}

	public static function updateCurrentUserName($id, $name){
		if(empty($name)){
			return;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('name') . ' = ' . $db->quote($name),
		);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($id) , 
		);
		$query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
 
		$db->setQuery($query);
					 
		$result = $db->execute();
	}

	public static function updateUsernameToSessionId($username, $sessionId){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('username') . ' = ' . $db->quote($username),
		);
					
		$conditions = array(
			$db->quoteName('session_id') . ' = ' . $db->quote($sessionId)
		);
					
		$query->update($db->quoteName('#__session'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
	}

	public static function getCustomerDetails(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('api_key','customer_token'));
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
		$db->setQuery($query);
		$customerResult = $db->loadAssoc();
		return $customerResult;
					
	}

	public static function getAccessOfArticle($id){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('access');
		$query->from('#__content');
		$query->where($db->quoteName('id')." = ".$db->quote($id));
		
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}
	
}
?>