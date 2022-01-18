<?php

/**
 * Class Encryption
 * Source: Opencart Forum @IP_CAM https://forum.opencart.com/viewtopic.php?t=207192
 */
final class Encryption {
    private $key;

    public function __construct($key) {
        $this->key = hash('sha256', $key, true);
    }

    public function encrypt($value) {
        $php_version = phpversion();

        if ($php_version >= '7.1') {
            $method = 'AES-128-CBC';

            $iv_length = openssl_cipher_iv_length($method); // 16

            $iv = openssl_random_pseudo_bytes($iv_length);

            $encrypted = openssl_encrypt($value, $method, hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv);

        } else {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

            if ($php_version >= '5.6') {
                $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
            } elseif ($php_version >= '5.3') {
                $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_RANDOM);
            } else {
                $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            }

            $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->key, true), $value, MCRYPT_MODE_CBC, $iv);
        }

        $encoded = base64_encode($encrypted) . '|' . base64_encode($iv);

        return strtr($encoded, '+/=', '-_,');
    }

    public function decrypt($value) {
        $php_version = phpversion();

        $value = explode('|', strtr($value, '-_,', '+/=') . '|');

        $decoded = base64_decode($value[0]);

        $iv = base64_decode($value[1]);

        if ($php_version >= '7.1') {
            $method = 'AES-128-CBC';

            $decrypted = trim(openssl_decrypt($decoded, $method, hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv));

        } else {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

            if (strlen($iv) !== $iv_size) {
                return false;
            }

            $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->key, true), $decoded, MCRYPT_MODE_CBC, $iv));
        }

        return $decrypted;
    }
}
