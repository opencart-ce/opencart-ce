<?php
final class Encryption {
	private $key;

	public function __construct($key) {
		$this->key = $key;
	}

	public function encrypt($value) {
		if (version_compare(PHP_VERSION, '7.1', '>=')) {
			$iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
			$tag = '';

			$encrypted = openssl_encrypt($value, 'aes-256-gcm', hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv, $tag);

			return strtr(base64_encode($iv . $tag . $encrypted), '+/=', '-_,');
		} else {
			trigger_error('Insecure encryption used, PHP 7.1 or above required', E_USER_WARNING);

			return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->key, true), $value, MCRYPT_MODE_ECB)), '+/=', '-_,');
		}
	}

	public function decrypt($value) {
		if (version_compare(PHP_VERSION, '7.1', '>=')) {
			$encrypted = base64_decode(strtr($value, '-_,', '+/='));

			$iv_length = openssl_cipher_iv_length('aes-256-gcm');
			$tag_length = 16;

			if (strlen($encrypted) <= $iv_length + $tag_length) {
				return '';
			}

			$iv = substr($encrypted, 0, $iv_length);
			$tag = substr($encrypted, $iv_length, $tag_length);

			return trim(openssl_decrypt(substr($encrypted, $iv_length + $tag_length), 'aes-256-gcm', hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv, $tag));
		} else {
			trigger_error('Insecure decryption used, PHP 7.1 or above required', E_USER_WARNING);

			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->key, true), base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_ECB));
		}
	}
}
?>