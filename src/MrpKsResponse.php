<?php

namespace Engazan\MrpKs;

/**
 * Class MrpKsResponse
 * @package Engazan\MrpKs
 * @author Engazan <Engazan.eu@icloud.com>
 */
class MrpKsResponse
{
    public static function decryptEncryptedResponse(string $responseXml): bool|string
    {
        $simpleXmlResponse = new SimpleXMLElement($responseXml);

        $encodingParams = base64_decode((string)$simpleXmlResponse->encodedBody->encodingParams);
        $encodedData = (string)$simpleXmlResponse->encodedBody->encodedData;
        $authCode = (string)$simpleXmlResponse->encodedBody->authCode;


        $simpleXmlResponseEncodingParams = new SimpleXMLElement($encodingParams);
        $varKey = (string)$simpleXmlResponseEncodingParams->varKey;

        $authCredentials = MrpKsAuth::getEncryptedAuthData($varKey);

        return openssl_decrypt(base64_decode($encodedData), 'aes-256-ctr', hex2bin($authCredentials['finalEncryptKey']), OPENSSL_RAW_DATA, hex2bin($authCredentials['initVector']));
    }
}
