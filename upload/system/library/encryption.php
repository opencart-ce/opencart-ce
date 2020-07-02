<?php
final class Encryption {
	private $key;
	private $cipher;
	private $iv;
	private $iv_length;

    public function __construct($key) {
        $this->key = $key;
		$this->cipher = 'aes-128-cbc';
		$this->iv_length = openssl_cipher_iv_length($this->cipher);
		$this->iv = openssl_random_pseudo_bytes($this->iv_length);
    }

    public function encrypt($value) {
		return strtr(base64_encode($this->iv . openssl_encrypt($value, $this->cipher, hash('sha256', $this->key, true), 0, $this->iv)), '+/=', '-_,');
    }

    public function decrypt($value) {
		$mixed = base64_decode(strtr($value, '-_,', '+/='));
		$iv = substr($mixed, 0, $this->iv_length);
		$encrypted = substr($mixed, $this->iv_length);

		return trim(openssl_decrypt($encrypted, $this->cipher, hash('sha256', $this->key, true), 0, $iv));
    }
}
?>
