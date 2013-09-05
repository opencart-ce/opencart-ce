<?php
class Affiliate {
	private $affiliate_id;
	private $firstname;
	private $lastname;
	private $email;
	private $telephone;
	private $fax;
	private $code;

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['affiliate_id'])) {
			$affiliate_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$this->session->data['affiliate_id'] . "' AND status = '1'");

			if ($affiliate_query->num_rows) {
				$this->affiliate_id = $affiliate_query->row['affiliate_id'];
				$this->firstname = $affiliate_query->row['firstname'];
				$this->lastname = $affiliate_query->row['lastname'];
				$this->email = $affiliate_query->row['email'];
				$this->telephone = $affiliate_query->row['telephone'];
				$this->fax = $affiliate_query->row['fax'];
				$this->code = $affiliate_query->row['code'];

				$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE affiliate_id = '" . (int)$this->session->data['affiliate_id'] . "'");
			} else {
				$this->logout();
			}
		}
	}

	public function login($email, $password) {
		$affiliate_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1' AND approved = '1'");

		if ($affiliate_query->num_rows) {
			// Create affiliate login cookie if HTTPS
			if ($this->config->get('config_secure')) {
				if ($this->request->isSecure()) {
					// Create a cookie and restrict it to HTTPS pages
					$this->session->data['affiliate_cookie'] = hash_rand('md5');

					setcookie('affiliate', $this->session->data['affiliate_cookie'], 0, '/', '', true, true);
				} else {
					return false;
				}
			}

			// Regenerate session id
			$this->session->regenerateId();

			// Token used to protect affiliate functions against CSRF
			$this->setToken();

			$this->session->data['affiliate_id'] = $affiliate_query->row['affiliate_id'];
			$this->session->data['affiliate_login_time'] = time();

			$this->affiliate_id = $affiliate_query->row['affiliate_id'];
			$this->firstname = $affiliate_query->row['firstname'];
			$this->lastname = $affiliate_query->row['lastname'];
			$this->email = $affiliate_query->row['email'];
			$this->telephone = $affiliate_query->row['telephone'];
			$this->fax = $affiliate_query->row['fax'];
			$this->code = $affiliate_query->row['code'];

			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		unset($this->session->data['affiliate_id']);
		unset($this->session->data['affiliate_cookie']);
		unset($this->session->data['affiliate_token']);
		unset($this->session->data['affiliate_login_time']);

		$this->affiliate_id = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->email = '';
		$this->telephone = '';
		$this->fax = '';
	}

	public function isLogged() {
		return $this->affiliate_id;
	}

	public function getId() {
		return $this->affiliate_id;
	}

	public function getFirstName() {
		return $this->firstname;
	}

	public function getLastName() {
		return $this->lastname;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getTelephone() {
		return $this->telephone;
	}

	public function getFax() {
		return $this->fax;
	}

	public function getCode() {
		return $this->code;
	}

	public function isSecure() {
		if (!$this->config->get('config_secure') || ($this->request->isSecure() && isset($this->request->cookie['affiliate']) && isset($this->session->data['affiliate_cookie']) && $this->request->cookie['affiliate'] == $this->session->data['affiliate_cookie'])) {
			return true;
		} else {
			return false;
		}
	}

	public function setToken() {
		$this->session->data['affiliate_token'] = hash_rand('md5');
	}

	public function loginExpired($age = 900) {
		if (isset($this->session->data['affiliate_login_time']) && (time() - $this->session->data['affiliate_login_time'] < $age)) {
			return false;
		} else {
			return true;
		}
	}
}
?>