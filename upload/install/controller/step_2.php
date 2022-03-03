<?php
class ControllerStep2 extends Controller {
	private $error = array();

	public function index() {
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->redirect(HTTP_SERVER . 'index.php?route=step_3');
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = HTTP_SERVER . 'index.php?route=step_2';

		$this->data['config_catalog'] = DIR_OPENCART . 'config.php';
		$this->data['config_admin'] = DIR_OPENCART . 'admin/config.php';

		$this->data['cache'] = DIR_SYSTEM . 'cache';
		$this->data['logs'] = DIR_SYSTEM . 'logs';
		$this->data['image'] = DIR_OPENCART . 'image';
		$this->data['image_cache'] = DIR_OPENCART . 'image/cache';
		$this->data['image_data'] = DIR_OPENCART . 'image/data';
		$this->data['download'] = DIR_OPENCART . 'download';

		$this->template = 'step_2.tpl';

		$this->children = array(
			'header',
			'footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!version_compare(phpversion(), '5.5', '>=')) {
			$this->error['warning'] = 'Warning: You need to use PHP 5.5 or above for OpenCart to work!';
		}

		if (!ini_get('file_uploads')) {
			$this->error['warning'] = 'Warning: file_uploads needs to be enabled!';
		}

		if (ini_get('session.auto_start')) {
			$this->error['warning'] = 'Warning: OpenCart will not work with session.auto_start enabled!';
		}

		if (!extension_loaded('mysql')) {
			$this->error['warning'] = 'Warning: MySQL extension needs to be loaded for OpenCart to work!';
		}

		if (!extension_loaded('gd')) {
			$this->error['warning'] = 'Warning: GD extension needs to be loaded for OpenCart to work!';
		}

		if (!extension_loaded('curl')) {
			$this->error['warning'] = 'Warning: CURL extension needs to be loaded for OpenCart to work!';
		}

		if (version_compare(PHP_VERSION, '7.1', '>=')) {
			if (!function_exists('openssl_encrypt')) {
				$this->error['warning'] = 'Warning: OpenSSL extension needs to be loaded for OpenCart to work!';
			}
		} else {
			if (!function_exists('mcrypt_encrypt')) {
				$this->error['warning'] = 'Warning: mCrypt extension needs to be loaded for OpenCart to work!';
			}
		}

		if (!extension_loaded('zlib')) {
			$this->error['warning'] = 'Warning: ZLIB extension needs to be loaded for OpenCart to work!';
		}

		if (!extension_loaded('json')) {
			$this->error['warning'] = 'Warning: JSON extension needs to be loaded for OpenCart to work!';
		}

		if (!is_writable(DIR_OPENCART . 'config.php')) {
			$this->error['warning'] = 'Warning: config.php needs to be writable for OpenCart to be installed!';
		}

		if (!is_writable(DIR_OPENCART . 'admin/config.php')) {
			$this->error['warning'] = 'Warning: admin/config.php needs to be writable for OpenCart to be installed!';
		}

		if (!is_writable(DIR_SYSTEM . 'cache')) {
			$this->error['warning'] = 'Warning: Cache directory needs to be writable for OpenCart to work!';
		}

		if (!is_writable(DIR_SYSTEM . 'logs')) {
			$this->error['warning'] = 'Warning: Logs directory needs to be writable for OpenCart to work!';
		}

		if (!is_writable(DIR_OPENCART . 'image')) {
			$this->error['warning'] = 'Warning: Image directory needs to be writable for OpenCart to work!';
		}

		if (!is_writable(DIR_OPENCART . 'image/cache')) {
			$this->error['warning'] = 'Warning: Image cache directory needs to be writable for OpenCart to work!';
		}

		if (!is_writable(DIR_OPENCART . 'image/data')) {
			$this->error['warning'] = 'Warning: Image data directory needs to be writable for OpenCart to work!';
		}

		if (!is_writable(DIR_OPENCART . 'download')) {
			$this->error['warning'] = 'Warning: Download directory needs to be writable for OpenCart to work!';
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>