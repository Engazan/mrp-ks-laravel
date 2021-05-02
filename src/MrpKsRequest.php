<?php

namespace Engazan\MrpKs;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

/**
 * Class MrpKsRequest
 * @package Engazan\MrpKs
 * @author Engazan <Engazan.eu@icloud.com>
 */
class MrpKsRequest
{
    private static function getClient(): Client
    {
        static $client;

        if (!$client) {
            $client = new Client(
                [
                    'base_uri' => config('mrp-ks.uri').':'.config('mrp-ks.port')
                ]
            );
        }

        return $client;
    }

    private static function getFilterForRequest(array $filters): string
    {
        $tmpFilterString = '';
        foreach ($filters as $filterName => $filterValue) {
            $tmpFilterString .= '<fltvalue name="'.$filterName.'">'.$filterValue.'</fltvalue>';
        }
        return '<filter>'.$tmpFilterString.'</filter>';
    }

    private static function wrapEncryptedRequestData(string $command, array $filters, string $dataXml = ''): string
    {
        $requestId = time();
        $authCredentials = MrpKsAuth::getEncryptedAuthData();

        // hex2bin($varKey) || $varKey ?
        $rawEncodedParams = '<mrpEncodingParams encryption="aes"><varKey>'.base64_encode(($authCredentials['varKey'])).'</varKey></mrpEncodingParams>';

        $mrpRequest = '<mrpRequest><request command="'.$command.'" requestId="'.$requestId.'" /><data>'.self::getFilterForRequest($filters).$dataXml.'</data></mrpRequest>';

        // Encrypt
        $mrpRequest = openssl_encrypt($mrpRequest, 'aes-256-ctr', hex2bin($authCredentials['finalEncryptKey']), OPENSSL_RAW_DATA, hex2bin($authCredentials['initVector']));

        return '<mrpEnvelope>
                      <encodedBody authentication="hmac_sha256">
                           <encodingParams>'.base64_encode($rawEncodedParams).'</encodingParams>
                           <encodedData>'.base64_encode($mrpRequest).'</encodedData>
                           <authCode>'.base64_encode(hex2bin(hash_hmac('sha256', $rawEncodedParams.$mrpRequest, hex2bin($authCredentials['secretAuthKey'])))).'</authCode>
                      </encodedBody>
                    </mrpEnvelope>';
    }

    private static function wrapRequestData(string $command, array $filters): string
    {
        $requestId = time();
        $request = '<?xml version="1.0" encoding="windows-1250"?><mrpEnvelope>
                      <body>
                        <mrpRequest>
                          <request command="'.$command.'" requestId="'.$requestId.'" />
                          <data>
                            '.self::getFilterForRequest($filters).'
                          </data>
                        </mrpRequest>
                      </body>
                    </mrpEnvelope>';
        return $request;
    }

    private static function wrapRequest(string $command, array $filters): string
    {
        if (config('mrp-ks.encryption')) {
            return self::wrapEncryptedRequestData($command, $filters);
        }
        return self::wrapRequestData($command, $filters);
    }

    public static function sendGetRequest(string $command, array $filters = []): JsonResponse
    {
        $client = self::getClient();

        $requestBody = MrpKsRequest::wrapRequest($command, $filters);

//        dd($requestBody);

        try {
            $response = $client->get(
                '',
                [
                    'headers' => [
                        'Authorization: Basic '.MrpKsAuth::getBasicAuthToken(),
                        'Content-Type: application/xml',
                    ],
                    'body' => $requestBody,
                ]
            );

            return response()->json(
                [
                    'statusCode' => $response->getStatusCode(),
                    'data' => $response->getBody()->getContents(),
                ]
            );
        } catch (GuzzleException $e) {
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
