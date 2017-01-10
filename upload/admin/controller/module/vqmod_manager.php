<?php
class ControllerModuleVQModManager extends Controller {
	/** 
	 * @todo  VQMod 2.3.0
	 * @todo  Use VQMod vars for paths instead of hardcoding
	 * @todo  VQMod installation check
	 * @todo  Invalid XML handling
	 * @todo  Check for unused language text
	 * @todo  VQMod script rename
	 * @todo  Better handling of VQMod logs
	 * @todo  Break VQMod installation errors into more generic parts
	 *          -Missing Files (re-upload)
	 *          -Permissions (chmod)
	 *          -Version (upgrade)
	 */
	private $error = array();

	public function __construct($registry) {
		parent::__construct($registry);

		// Paths and Files
		$this->base_dir = substr_replace(DIR_SYSTEM, '/', -8);
		$this->vqmod_dir = substr_replace(DIR_SYSTEM, '/vqmod/', -8);
		$this->vqmod_script_dir = substr_replace(DIR_SYSTEM, '/vqmod/xml/', -8);
		$this->vqcache_dir = substr_replace(DIR_SYSTEM, '/vqmod/vqcache/', -8);
		$this->vqcache_files = substr_replace(DIR_SYSTEM, '/vqmod/vqcache/vq*', -8);
		$this->vqmod_log = substr_replace(DIR_SYSTEM, '/vqmod/vqmod.log', -8); // Depricated VQMod 2.2.0
		$this->vqmod_logs_dir = substr_replace(DIR_SYSTEM, '/vqmod/logs/', -8);
		$this->vqmod_logs = substr_replace(DIR_SYSTEM, '/vqmod/logs/*.log', -8);
		$this->vqmod_modcache = substr_replace(DIR_SYSTEM, '/vqmod/mods.cache', -8); // VQMod 2.2.0
		$this->vqmod_opencart_script = substr_replace(DIR_SYSTEM, '/vqmod/xml/vqmod_opencart.xml', -8);
		$this->vqmod_path_replaces = substr_replace(DIR_SYSTEM, '/vqmod/pathReplaces.php', -8); // VQMod 2.3.0

		clearstatcache();
	}

	public function index() {
		$this->language->load('module/vqmod_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['upload'])) {
			// Upload VQMod
			$this->vqmod_upload();
		}

		// Language
		$this->data = array_merge($this->data, $this->language->load('module/vqmod_manager'));

		// Check that VQMod is properly installed in store
		if ($this->vqmod_installation_check()) {
			$this->data['vqmod_is_installed'] = true;
		} else {
			$this->data['vqmod_is_installed'] = false;
		}

		// VQMod installation errors
		if (isset($this->session->data['vqmod_installation_error'])) {
			$this->data['vqmod_installation_error'] = $this->session->data['vqmod_installation_error'];

			unset($this->session->data['vqmod_installation_error']);
		} else {
			$this->data['vqmod_installation_error'] = '';
		}

		// Warning
		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} else {
			$this->data['error_warning'] = '';
		}

		// Success
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// Breadcrumbs
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'text'      => $this->language->get('text_home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
			'text'      => $this->language->get('text_module'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'),
			'text'      => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		// Action Buttons
		$this->data['action'] = $this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['clear_log'] = $this->url->link('module/vqmod_manager/clear_log', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['clear_vqcache'] = $this->url->link('module/vqmod_manager/clear_vqcache', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['download_log'] = $this->url->link('module/vqmod_manager/download_log', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['download_scripts'] = $this->url->link('module/vqmod_manager/download_vqmod_scripts', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['download_vqcache'] = $this->url->link('module/vqmod_manager/download_vqcache', 'token=' . $this->session->data['token'], 'SSL');

		// Check ZipArchive for use with downloads
		if (class_exists('ZipArchive')) {
			$this->data['ziparchive'] = true;
		} else {
			$this->data['ziparchive'] = false;
		}

		// Detect scripts
		$vqmod_scripts = $this->list_vqmod_scripts();

		$this->data['vqmods'] = array();

		if (!empty($vqmod_scripts)) {
			foreach ($vqmod_scripts as $vqmod_script) {
				$extension = pathinfo($vqmod_script, PATHINFO_EXTENSION);

				if ($extension == 'xml_') {
					$file = basename($vqmod_script, '.xml_');
				} else {
					$file = basename($vqmod_script, '.xml');
				}

				$action = array();

				if ($extension == 'xml_') {
					$action[] = array(
						'text' => $this->language->get('text_install'),
						'href' => $this->url->link('module/vqmod_manager/vqmod_install', 'token=' . $this->session->data['token'] . '&vqmod=' . $file, 'SSL')
					);
				} else {
					$action[] = array(
						'text' => $this->language->get('text_uninstall'),
						'href' => $this->url->link('module/vqmod_manager/vqmod_uninstall', 'token=' . $this->session->data['token'] . '&vqmod=' . $file, 'SSL')
					);
				}

				libxml_use_internal_errors(true);
				$xml = simplexml_load_file($vqmod_script);

				if (libxml_get_errors()) {
					$invalid_xml = sprintf($this->language->get('highlight'), $this->language->get('error_invalid_xml'));
					libxml_clear_errors();
				} else {
					$invalid_xml = '';
				}

				$this->data['vqmods'][] = array(
					'file_name'   => basename($vqmod_script, ''),
					'id'          => isset($xml->id) ? $xml->id : $this->language->get('text_unavailable'),
					'version'     => isset($xml->version) ? $xml->version : $this->language->get('text_unavailable'),
					'vqmver'      => isset($xml->vqmver) ? $xml->vqmver : $this->language->get('text_unavailable'),
					'author'      => isset($xml->author) ? $xml->author : $this->language->get('text_unavailable'),
					'status'      => $extension == 'xml_' ? sprintf($this->language->get('highlight'), $this->language->get('text_disabled')) : $this->language->get('text_enabled'),
					'delete'      => $this->url->link('module/vqmod_manager/vqmod_delete', 'token=' . $this->session->data['token'] . '&vqmod=' . basename($vqmod_script), 'SSL'),
					'action'      => $action,
					'invalid_xml' => $invalid_xml
				);
			}
		}

		// VQCache files
		$this->data['vqcache'] = array();

		if (is_dir($this->vqcache_dir)) {
			$this->data['vqcache'] = array_diff(scandir($this->vqcache_dir), array('.', '..'));
		}

		// VQMod Error Log
		$this->data['log'] = '';

		if (is_dir($this->vqmod_logs_dir) && is_readable($this->vqmod_logs_dir)) {
			// VQMod 2.2.0 and later logs
			$vqmod_logs = glob($this->vqmod_logs);
			$vqmod_logs_size = 0;

			if (!empty($vqmod_logs)) {
				foreach ($vqmod_logs as $vqmod_log) {
					$vqmod_logs_size += filesize($vqmod_log);
				}

				// Error if log files are larger than 6MB combined
				if ($vqmod_logs_size > 6291456) {
					$this->data['error_warning'] = sprintf($this->language->get('error_log_size'), round(($vqmod_logs_size / 1048576), 2));
					$this->data['log'] = sprintf($this->language->get('error_log_size'), round(($vqmod_logs_size / 1048576), 2));
				} else {
					foreach ($vqmod_logs as $vqmod_log) {
						$this->data['log'] .= str_pad(basename($vqmod_log), 70, '*', STR_PAD_BOTH) . "\n";
						$this->data['log'] .= file_get_contents($vqmod_log, FILE_USE_INCLUDE_PATH, null);
					}
				}
			}
		} elseif (is_file($this->vqmod_log) && filesize($this->vqmod_log) > 0) {
			// VQMod 2.1.7 and earlier log
			if (filesize($this->vqmod_log) > 6291456) {
				// Error if log file is larger than 6MB
				$this->data['error_warning'] = sprintf($this->language->get('error_log_size'), round((filesize($this->vqmod_log) / 1048576), 2));
				$this->data['log'] = sprintf($this->language->get('error_log_size'), round((filesize($this->vqmod_log) / 1048576), 2));
			} else {
				// Regular log
				$this->data['log'] = file_get_contents($this->vqmod_log, FILE_USE_INCLUDE_PATH, null);
			}
		}

		if ($this->data['log']) {
			// Highlight Error Log tab
			$this->data['tab_error_log'] = sprintf($this->language->get('highlight'), $this->language->get('tab_error_log'));
		}

		// VQMod Path
		if (is_dir($this->vqmod_dir)) {
			$this->data['vqmod_path'] = $this->vqmod_dir;
		} else {
			$this->data['vqmod_path'] = '';
		}

		// VQMod class variables
		$vqmod_vars = get_class_vars('VQMod');

		$this->data['vqmod_vars'] = array();

		if ($vqmod_vars) {
			foreach ($vqmod_vars as $setting => $value) {
				// Deprecated VQMod 2.1.7
				if ($setting == 'useCache') {
					$this->data['vqmod_vars'][] = array(
						'setting' => $this->language->get('setting_usecache'),
						'value'   => ($value === true ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
					);
				}

				if ($setting == 'logging') {
					$this->data['vqmod_vars'][] = array(
						'setting' => $this->language->get('setting_logging'),
						'value'   => ($value === true ? $this->language->get('text_enabled') : $this->language->get('text_disabled'))
					);
				}

				// Deprecated VQMod 2.2.0
				if ($setting == 'cacheTime') {
					$this->data['vqmod_vars'][] = array(
						'setting' => $this->language->get('setting_cachetime'),
						'value'   => sprintf($this->language->get('text_cachetime'), $value)
					);
				}

				if ($setting == 'protectedFilelist' && is_file($this->base_dir . $value)) {
					$protected_files = file_get_contents($this->base_dir . $value);

					if (!empty($protected_files)) {
						$protected_files = preg_replace('~\r?\n~', "\n", $protected_files);

						$paths = explode("\n", $protected_files);

						$this->data['vqmod_vars'][] = array(
							'setting' => $this->language->get('setting_protected_files'),
							'value'   => implode('<br />', $paths)
						);
					}
				}

				if ($setting == 'directorySeparator' && !empty($value)) {
					$this->data['vqmod_vars'][] = array(
						'setting' => $this->language->get('setting_dir_separator'),
						'value'   => htmlentities($value, ENT_QUOTES, 'UTF-8')
					);
				}
			}
		}

		// Path Replacements - VQMod 2.3.0
		if (is_file($this->vqmod_path_replaces)) {
			if (!in_array('pathReplaces.php', get_included_files())) {
				include_once($this->vqmod_path_replaces);
			}

			if (!empty($replaces)) {
				$replacement_values = array();

				foreach ($replaces as $key => $value) {
					$replacement_values[] = $value[0] . $this->language->get('text_separator') . $value[1];
				}

				$this->data['vqmod_vars'][] = array(
					'setting' => $this->language->get('setting_path_replaces'),
					'value'   => implode('<br />', $replacement_values)
				);
			}
		}

		// Stylesheet
		$this->document->addStyle('view/stylesheet/vqmod_manager.css');

		// Template
		$this->template = 'module/vqmod_manager.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function vqmod_install() {
		if ($this->userPermission()) {
			$vqmod_script = $this->request->get['vqmod'];

			if (is_file($this->vqmod_script_dir . $vqmod_script . '.xml_')) {
				rename($this->vqmod_script_dir . $vqmod_script . '.xml_', $this->vqmod_script_dir . $vqmod_script . '.xml');

				$this->clear_vqcache(true);

				$this->session->data['success'] = $this->language->get('success_install');
			} else {
				$this->session->data['error'] = $this->language->get('error_install');
			}
		}

		$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function vqmod_uninstall() {
		if ($this->userPermission()) {
			$vqmod_script = $this->request->get['vqmod'];

			if (is_file($this->vqmod_script_dir . $vqmod_script . '.xml')) {
				rename($this->vqmod_script_dir . $vqmod_script . '.xml', $this->vqmod_script_dir . $vqmod_script . '.xml_');

				$this->clear_vqcache(true);

				$this->session->data['success'] = $this->language->get('success_uninstall');
			} else {
				$this->session->data['error'] = $this->language->get('error_uninstall');
			}
		}

		$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function vqmod_upload() {
		$this->language->load('module/vqmod_manager');

		if ($this->userPermission()) {
			$file = $this->request->files['vqmod_file']['tmp_name'];
			$file_name = $this->request->files['vqmod_file']['name'];

			if ($this->request->files['vqmod_file']['error'] > 0) {
				switch($this->request->files['vqmod_file']['error']) {
					case 1:
						$this->session->data['error'] = $this->language->get('error_ini_max_file_size');
						break;
					case 2:
						$this->session->data['error'] = $this->language->get('error_form_max_file_size');
						break;
					case 3:
						$this->session->data['error'] = $this->language->get('error_partial_upload');
						break;
					case 4:
						$this->session->data['error'] = $this->language->get('error_no_upload');
						break;
					case 6:
						$this->session->data['error'] = $this->language->get('error_no_temp_dir');
						break;
					case 7:
						$this->session->data['error'] = $this->language->get('error_write_fail');
						break;
					case 8:
						$this->session->data['error'] = $this->language->get('error_php_conflict');
						break;
					default:
						$this->session->data['error'] = $this->language->get('error_unknown');
				}

			} else {
				if ($this->request->files['vqmod_file']['type'] != 'text/xml') {
					$this->session->data['error'] = $this->language->get('error_filetype');
				} else {
					libxml_use_internal_errors(true);
					simplexml_load_file($file);

					if (libxml_get_errors()) {
						$this->session->data['error'] = $this->language->get('error_invalid_xml');
						libxml_clear_errors();
					} elseif (move_uploaded_file($file, $this->vqmod_script_dir . $file_name) === false) {
						$this->session->data['error'] = $this->language->get('error_move');
					} else {
						$this->clear_vqcache(true);

						$this->session->data['success'] = $this->language->get('success_upload');
					}
				}
			}
		}

		$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function vqmod_delete() {
		if ($this->userPermission()) {
			$vqmod_script = $this->request->get['vqmod'];

			if (is_file($this->vqmod_script_dir . $vqmod_script)) {
				if (unlink($this->vqmod_script_dir . $vqmod_script)) {
					$this->clear_vqcache(true);

					$this->session->data['success'] = $this->language->get('success_delete');
				} else {
					$this->session->data['error'] = $this->language->get('error_delete');
				}
			}
		}

		$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function clear_vqcache($return = false) {
		if ($this->userPermission()) {
			$files = glob($this->vqcache_files);

			if ($files) {
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}
			}

			if (is_file($this->vqmod_modcache)) {
				unlink($this->vqmod_modcache);
			}

			if ($return) {
				return;
			}

			$this->session->data['success'] = $this->language->get('success_clear_vqcache');
		}

		$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function clear_log() {
		if ($this->userPermission()) {
			if (is_dir($this->vqmod_logs_dir)) {
				// VQMod 2.2.0 and later
				$files = glob($this->vqmod_logs);

				if ($files) {
					foreach ($files as $file) {
						unlink($file);
					}
				}
			} else {
				// VQMod 2.1.7 and earlier
				$file = $this->vqmod_log;

				$handle = fopen($file, 'w+');

				fclose($handle);

				$this->session->data['success'] = $this->language->get('success_clear_log');
			}
		}

		$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	private function list_vqmod_scripts() {
		$vqmod_scripts = array();

		if ($this->userPermission('access')) {
			$enabled_vqmod_scripts = glob($this->vqmod_script_dir . '*.xml');
			$disabled_vqmod_scripts = glob($this->vqmod_script_dir . '*.xml_');

			if (!empty($enabled_vqmod_scripts)) {
				$vqmod_scripts = array_merge($vqmod_scripts, $enabled_vqmod_scripts);
			}

			if (!empty($disabled_vqmod_scripts)) {
				$vqmod_scripts = array_merge($vqmod_scripts, $disabled_vqmod_scripts);
			}
		}

		return $vqmod_scripts;
	}

	public function download_vqmod_scripts() {
		if ($this->userPermission()) {
			$targets = $this->list_vqmod_scripts();

			$this->zip_send($targets, 'vqmod_scripts_backup');
		} else {
			$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function download_vqcache() {
		if ($this->userPermission()) {
			$targets = glob($this->vqcache_files);

			if (is_file($this->vqmod_modcache)) {
				$targets[] = $this->vqmod_modcache;
			}

			$this->zip_send($targets, 'vqcache_dump');
		} else {
			$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function download_log() {
		if ($this->userPermission()) {
			if (is_dir($this->vqmod_logs_dir) && count(array_diff(scandir($this->vqmod_logs_dir), array('.', '..'))) > 0) {
				// VQMod 2.2.0 and later
				$targets = glob($this->vqmod_logs);

				$this->zip_send($targets, 'vqmod_logs');
			} elseif (is_file($this->vqmod_log)) {
				// VQMod 2.1.7 and earlier
				$targets = array($this->vqmod_log);

				$this->zip_send($targets, 'vqmod_log');
			} else {
				// No log available for download error
				$this->language->load('module/vqmod_manager');
				$this->session->data['error'] = $this->language->get('error_log_download');

				$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
			}
		} else {
			$this->redirect($this->url->link('module/vqmod_manager', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	private function zip_send($targets, $filename = 'download') {
		$temp = tempnam('tmp', 'zip');

		$zip = new ZipArchive();
		$zip->open($temp, ZipArchive::OVERWRITE);

		foreach ($targets as $target) {
			if (is_file($target)) {
				$zip->addFile($target, basename($target));
			}
		}

		$zip->close();

		header('Pragma: public');
		header('Expires: 0');
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $filename . '_' . date('Y-m-d_H-i') . '.zip');
		header('Content-Transfer-Encoding: binary');
		readfile($temp);
		unlink($temp);
	}

	private function vqmod_installation_check() {
		// Check SimpleXML for VQMod Manager use
		if (!function_exists('simplexml_load_file')) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_simplexml');
			return false;
		}

		// Check if /vqmod directory exists
		if (!is_dir($this->vqmod_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_dir');
			return false;
		}

		// Check if vqmod.php exists
		if (!is_file($this->vqmod_dir . 'vqmod.php')) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_core');
			return false;
		}

		// Check if /vqmod/xml directory exists
		if (!is_dir($this->vqmod_script_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_script_dir');
			return false;
		}

		// Check if /vqmod/vqcache directory exists
		if (!is_dir($this->vqcache_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqcache_dir');
			return false;
		}

		// Check that vqmod_opencart.xml exists
		if (!is_file($this->vqmod_opencart_script)) {
			if (is_file($this->vqmod_opencart_script . '_')) {
				$this->session->data['error'] = $this->language->get('error_opencart_xml_disabled');
			} else {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_opencart_xml');
				return false;
			}
		}

		// Check that OpenCart 1.5.x is being used - other errors will appear on the page if they're using 1.4.x but at least the user will be told what the issue is
		if (!defined('VERSION') || version_compare(VERSION, '1.5.0', '<')) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_opencart_version');
			return false;
		}

		// If OpenCart 1.5.4+ check that vqmod_opencart.xml 2.1.7 or later is being used
		if (version_compare(VERSION, '1.5.4', '>=')) {
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file($this->vqmod_opencart_script);
			libxml_clear_errors();

			// In VQMod 2.3.0 'vqmver' is set as '2.X'
			if ((isset($xml->vqmver)) && (strtolower($xml->vqmver) != '2.x') && (version_compare($xml->vqmver, '2.1.7', '<'))) {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_opencart_xml_version');
				return false;
			}
		}

		// Check if VQMod class is added to OpenCart
		if (!class_exists('VQMod')) {
			if (is_file($this->vqmod_dir . 'install/index.php') && is_file($this->vqmod_dir . 'install/ugrsr.class.php')) {
				$this->session->data['vqmod_installation_error'] = sprintf($this->language->get('error_vqmod_install_link'), HTTP_CATALOG . 'vqmod/install');
			} else {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_opencart_integration');
			}
			return false;
		}

		// Check VQMod Error Log Writing
		if ((is_dir($this->vqmod_logs_dir) && !is_writable($this->vqmod_logs_dir)) || (!is_writable($this->vqmod_dir))) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_error_log_write');
			return false;
		}

		// Check VQMod Script Writing
		if (!is_writable($this->vqmod_script_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_script_write');
			return false;
		}

		// Check VQCache Writing
		if (!is_writable($this->vqcache_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqcache_write');
			return false;
		}

		// Check if vqcache files from vqmod_opencart.xml have been generated
		$vqcache_files = array(
			'vq2-system_engine_controller.php',
			'vq2-system_engine_front.php',
			'vq2-system_engine_loader.php',
			'vq2-system_library_language.php',
			'vq2-system_library_template.php',
			'vq2-system_startup.php'
		);

		foreach ($vqcache_files as $vqcache_file) {
			// Only return false if vqmod_opencart.xml_ isn't present (in case the user has disabled it) so they aren't locked out of VQMM
			if (!is_file($this->vqcache_dir . $vqcache_file) && !is_file($this->vqmod_opencart_script . '_')) {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqcache_files_missing');
				return false;
			}
		}

		return true;
	}

	private function userPermission($permission = 'modify') {
		$this->language->load('module/vqmod_manager');

		if (!$this->user->hasPermission($permission, 'module/vqmod_manager')) {
			$this->session->data['error'] = $this->language->get('error_permission');
			return false;
		} else {
			return true;
		}
	}
}
?>