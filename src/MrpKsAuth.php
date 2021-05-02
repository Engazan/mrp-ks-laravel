<?php

namespace Engazan\MrpKs;

/**
 * Class MrpKsAuth
 * @package Engazan\MrpKs
 * @author Engazan <Engazan.eu@icloud.com>
 */
class MrpKsAuth
{
    public static function getBasicAuthToken(): string
    {
        return base64_encode(config('mrp-ks.username').':'.config('mrp-ks.password'));
    }

    public static function getEncryptedAuthData(?string $varKey = null): array
    {
        $baseKey = base64_decode(config('mrp-ks.encryption'));

        if ($varKey === null) {
            $varKey = hash('sha256', time());
            $varKey = hex2bin($varKey);
        } else {
            $varKey = base64_decode($varKey);
        }

        $secretEncryptKey = hash_hmac('sha256', hex2bin('01'), $baseKey);
        $secretAuthKey = hash_hmac('sha256', hex2bin($secretEncryptKey.'02'), $baseKey);
        $finalEncryptKey = hash_hmac('sha256', ($varKey), hex2bin($secretEncryptKey));
        $initVector = substr(hash('sha256', ($varKey)), 0, 32);

        return [
            'encryptKey' => config('mrp-ks.encryption'),
            'baseKey' => $baseKey,
            'varKey' => $varKey,
            'secretEncryptKey' => $secretEncryptKey,
            'secretAuthKey' => $secretAuthKey,
            'finalEncryptKey' => $finalEncryptKey,
            'initVector' => $initVector,
        ];
    }
}
