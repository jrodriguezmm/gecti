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
		assert('is_int($length)');
        return openssl_random_pseudo_bytes($length);
	}
	
	public static function createAuthnRequest($acsUrl, $issuer) {
		$requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
						'<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="' . self::generateID() . 
						'" Version="2.0" IssueInstant="' . self::generateTimestamp() . 
						'" ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $acsUrl . 
						'" ><saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . 
						'</saml:Issuer></samlp:AuthnRequest>';
		$deflatedStr = gzdeflate($requestXmlStr);
		$base64EncodedStr = base64_encode($deflatedStr);
		$urlEncoded = urlencode($base64EncodedStr);
		return $urlEncoded;
	}
	
	public static function generateTimestamp($instant = NULL) {
		if($instant === NULL) {
			$instant = time();
		}
		return gmdate('Y-m-d\TH:i:s\Z', $instant);
	}
	
	public static function xpQuery(DOMNode $node, $query)
    {
        assert('is_string($query)');
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
        assert('is_string($namespaceURI)');
        assert('is_string($localName)');

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
    	//$data = $root->ownerDocument->saveXML($root);
    	//echo htmlspecialchars($data);
    	
        /* Create an XML security object. */
        $objXMLSecDSig = new XMLSecurityDSig();

        /* Both SAML messages and SAML assertions use the 'ID' attribute. */
        $objXMLSecDSig->idKeys[] = 'ID';
		
       
        /* Locate the XMLDSig Signature element to be used. */
        $signatureElement = self::xpQuery($root, './ds:Signature');
        //print_r($signatureElement);
        
        if (count($signatureElement) === 0) {
            /* We don't have a signature element to validate. */
            return FALSE;
        } elseif (count($signatureElement) > 1) {
            throw new Exception('XMLSec: more than one signature element in root.');
        } /*elseif ((in_array('Response', $signatureElement) && $ocurrence['Response'] > 1) ||
            (in_array('Assertion', $signatureElement) && $ocurrence['Assertion'] > 1) ||
            !in_array('Response', $signatureElement) && !in_array('Assertion', $signatureElement)
        ) {
            return false;
        }*/
        
        $signatureElement = $signatureElement[0];
        $objXMLSecDSig->sigNode = $signatureElement;
		
        /* Canonicalize the XMLDSig SignedInfo element in the message. */
        $objXMLSecDSig->canonicalizeSignedInfo();
		
       /* Validate referenced xml nodes. */
        if (!$objXMLSecDSig->validateReference()) { 
            throw new Exception('XMLsec: digest validation failed');
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
            throw new Exception('XMLSec: The root element is not signed.');
        }

        /* Now we extract all available X509 certificates in the signature element. */
        $certificates = array();
        foreach (self::xpQuery($signatureElement, './ds:KeyInfo/ds:X509Data/ds:X509Certificate') as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(array("\r", "\n", "\t", ' '), '', $certData);
            $certificates[] = $certData;
			//echo "CertDate: " . $certData . "<br />";
        }
	
        $ret = array(
            'Signature' => $objXMLSecDSig,
            'Certificates' => $certificates,
            );
		
		//echo "Signature validated";
			
			
        return $ret;
    }
	

	
	public static function validateSignature(array $info, XMLSecurityKey $key)
    {
        assert('array_key_exists("Signature", $info)');

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
    	assert('is_string($algorithm)');
    	assert('$type === "public" || $type === "private"');
    
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
    
	public static function processResponse($currentURL, $certFingerprint, $signatureData,
		SAML2_Response $response) {
		assert('is_string($currentURL)');
		assert('is_string($certFingerprint)');
		
		
		/* Validate Response-element destination. */
		$msgDestination = $response->getDestination();
		if ($msgDestination !== NULL && $msgDestination !== $currentURL) {
			echo 'Destination in response doesn\'t match the current URL. Destination is "' .
				$msgDestination . '", current URL is "' . $currentURL . '".';
				exit;
		}
		
		$responseSigned = self::checkSign($certFingerprint, $signatureData);
		
		/* Returning boolean $responseSigned */
		return $responseSigned;
	}
	
	public static function checkSign($certFingerprint, $signatureData) {
		
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
			 */
			assert('$key->type === XMLSecurityKey::RSA_SHA1');
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
	
	public static function validateIssuerAndAudience($samlResponse, $spEntityId, $issuerToValidateAgainst) {
		
		
		$issuer = current($samlResponse->getAssertions())->getIssuer();
		$audience = current(current($samlResponse->getAssertions())->getValidAudiences());
		if(strcmp($issuerToValidateAgainst, $issuer) === 0) {
			
			if(strcmp($audience, $spEntityId) === 0) {
				return TRUE;
			} else {
				ob_end_clean();
           
                    echo '<div style="font-family:Calibri;padding:0 3%;">';
                    echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Invalid Audience URI.</p>
                    <p>Please contact your administrator and report the following error:</p>
                    <p><strong>Possible Cause: </strong>The value of \'Audience URI\' field on Identity Provider\'s side is incorrect</p>
                    <p>Expected one of the Audiences to be: '.$spEntityId.'<p>
                    </div>
                    <div style="margin:3%;display:block;text-align:center;">
                    <form action="index.php">
                    <input style="padding:1%;width:115px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to Home" ></form></div>';
					   exit;
			}
		} else {
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
                <form action="index.php">
                <input style="padding:1%;width:115px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to Home" ></form></div>';
                 exit;
		}
	}
	
	private static function findCertificate(array $certFingerprints, array $certificates) {

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
                <input style="padding:1%;width:115px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Back to Home" ></form></div>';
                exit;
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
    


public static function get_user_from_joomla($matcher,$username,$email){
			//Check if email exist in database
		$db = &JFactory::getDBO();
		
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



	
	
}
?>