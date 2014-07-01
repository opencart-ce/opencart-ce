<?php
class CryptoException extends Exception 
{
	// Flesh these out later? 
}

final class Encryption {
	private $key;
	private $iv;
	
	
	protected $_digests = array(
		'sha224' => 28,
		'sha256' => 32,
		'sha384' => 48,
		'sha512' => 64
	);
	
	const SEPARATOR = '$'; // Something outside the base64 alphabet please

	public function __construct($key = null)
	{
		if(isset($key)) {
			$this->key = hash('sha256', $key, true);
		}
	}

	/**
	 * Authenticated encryption
	 * 
	 * @param string $value - this is the message to be encrypted
	 * @param string $key (optional) - use this as a master key
	 * @return string
	 */
	public function encrypt($value, $key = null)
	{
		if (empty($key)) {
			if (empty($this->key)) {
				throw new CryptoException("No encryption key provided");
			}
			$key = $this->key;
		}
		list($eKey, $aKey) = $this->deriveKeys($key);
		// encrypted := iv || '$' || ciphertext
		$encrypted = $this->encryptOnly($value, $eKey);
		
		// return: iv || '$' || ciphertext || '$' || HMAC
		return $encrypted .
			self::SEPARATOR .
			$this->toBase64(
				hash_hmac('sha256', $encrypted, $aKey, true)
			);
	}
	
	/**
	 * Encryption without authentication
	 * @param string $value - this is the message to be encrypted
	 * @param string $key (optional) - encrypt with this key
	 * @return string
	 * 
	 * @todo PKCS#7 Padding
	 */
	public function encryptOnly($plaintext, $key = null)
	{
		if (empty($key)) {
			if (empty($this->key)) {
				die("No encryption key provided");
			}
			$key = $this->key;
		}
		
		// Proper IV for encryption
		$iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
		
		// PKCS PADDING
		$padded = $value;
			$l = strlen($value) % 16;
		if($l === 0) {
			// Special case
			$l = 16;
		} else {
			$l = 16 - $l;
		}
		$padded .= str_repeat(chr($l), $l);
		
		// iv || '$' || ciphertext
		return $this->toBase64($iv) .
			self::SEPARATOR .
			$this->toBase64(
				mcrypt_encrypt(
					MCRYPT_RIJNDAEL_128,
					$key,
					$padded
					MCRYPT_MODE_CBC,
					$iv
				)
			);
	}
	
	/**
	 * Verify and decrypt a string
	 */
	public function decrypt($value, $key = null, $iv = null, $hmac = null)
	{
		if (empty($key)) {
			if (empty($this->key)) {
				throw new CryptoException("No encryption key provided");
			}
			$key = $this->key;
		}
		
		$blob = explode(self::SEPARATOR, $value);
		
		if (count($blob) === 1 && isset($iv) && isset($hmac)) {
			$ciphertext = $value;
			// Weird usage, but okay. We can work with this.
		} elseif (count($blob) === 3) {
			// $value := iv || '$' || ciphertext || '$' || HMAC
			list($iv, $ciphertext, $hmac) = $blob;
		} else {
			throw new CryptoException("Improperly formatted ciphertext");
		}
		list($eKey, $aKey) = $this->deriveKeys($key);
		
		// Recalculate the MAC we'd expect given the ciphertext, and 
		// having the same $aKey
		$calc = hash_hmac(
				'sha256', 
				$iv . self::SEPARATOR . $ciphertext, 
				$aKey, 
				true
			);
		if (!$this->equals($hmac, $calc)) {
			throw new CryptoException("Invalid signature.");
		}
		return $this->decryptOnly($ciphertext, $eKey, $iv);
	}
	
	/**
	 * Only decrypt, discarding signatures
	 * @param string $ciphertext 
	 * @param string $key (optional)
	 * @param string $iv (optional)
	 */
	public function decryptOnly($ciphertext, $key = null, $iv = null)
	{
		if (empty($key)) {
			if(empty($this->key)) {
				throw new CryptoException("No encryption key provided");
			}
			$key = $this->key;
		}
		if (empty($iv)) {
			$blob = explode(self::SEPARATOR, $ciphertext);
			if (count($blob) < 2) {
				die("No IV provided!");	
			}
			list($iv, $ciphertext) = $blob;
		}
		
		
		$plain = mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$key,
				$this->fromBase64($ciphertext), 
				MCRYPT_MODE_CBC, 
				$iv
			);
		
		// Finally emove PKCS#7 Padding:
		$l = strlen($plain) - ord($plain[strlen($plain) - 1]);
		return substr($plain, 0, $l);
	}
	
	/**
	 * Derive two keys from a master key (HKDF)
	 * 
	 * @param master_key
	 * @return array[2]
	 */
	public function deriveKeys($master_key)
	{	
		return array(
			$this->hkdf($master_key, 'sha512', NULL, strlen($master_key), 'encryption'),
			$this->hkdf($master_key, 'sha512', NULL, strlen($master_key), 'authentication')
		);
		
	}
	
	/**
	 * Compare two hashes without being susceptible to timing attacks
	 * by using HMAC with a random key before comparing.
	 */
	public function equals($A, $B)
	{
		$nonce = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
		return hash_hmac('sha256', $A, $nonce) === hash_hmac('sha256', $B, $nonce);
	}
	
	/**
	 * Custom-defined base64 encoding
	 */
	protected function toBase64($string)
	{
		return strtr(base64_encode( $string ), '+/=', '-_,');
	}
	protected function fromBase64($string)
	{
		return base64_decode(strtr($string, '-_,', '+/=');
	}
	

	/**
	 * HKDF - stolen from CodeIgniter
	 *
	 * @link	https://tools.ietf.org/rfc/rfc5869.txt
	 * @param	$key	Input key
	 * @param	$digest	A SHA-2 hashing algorithm
	 * @param	$salt	Optional salt
	 * @param	$length	Output length (defaults to the selected digest size)
	 * @param	$info	Optional context/application-specific info
	 * @return	string	A pseudo-random key
	 */
	public function hkdf($key, $digest = 'sha512', $salt = NULL, $length = NULL, $info = '')
	{
		if ( ! isset($this->_digests[$digest]))
		{
			return FALSE;
		}

		if (empty($length) OR ! is_int($length))
		{
			$length = $this->_digests[$digest];
		}
		elseif ($length > (255 * $this->_digests[$digest]))
		{
			return FALSE;
		}

		strlen($salt) OR $salt = str_repeat("\0", $this->_digests[$digest]);

		$prk = hash_hmac($digest, $key, $salt, TRUE);
		$key = '';
		for ($key_block = '', $block_index = 1; strlen($key) < $length; $block_index++)
		{
			$key_block = hash_hmac($digest, $key_block.$info.chr($block_index), $prk, TRUE);
			$key .= $key_block;
		}

		return substr($key, 0, $length);
	}
}
?>
