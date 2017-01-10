<?php
class Language {
	private $default = 'english';
	private $directory;
	private $data = array();

	public function __construct($directory) {
		$this->directory = $directory;
	}

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : $key);
	}

	public function load($filename) {
		// Load default language - the base
		$file = DIR_LANGUAGE . $this->default . '/' . $this->default . '.php';
		if (file_exists($file)) {
			$_ = array();
			require($file);
			$this->data = array_merge($this->data, $_);
		}

		// Override all or part of language resources
		$file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';

		if (file_exists($file)) {
			$_ = array();
			require($file);

			$this->data = array_merge($this->data, $_);
		} else {
			trigger_error('Error: Could not load language ' . $filename . '!');
		}

		return $this->data;
	}
}
?>