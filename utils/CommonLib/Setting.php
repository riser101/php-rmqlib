<?php namespace CommonLib;

class Setting {
	private $config_data;

	/**
	 * The class constructor
	 *
	 * @param string $config_file 
	 * @return void
	 */
	public function __construct($config_file = './config/config.json') {
		if (!is_readable($config_file)) {
			throw new RuntimeException("Configuration file '" . $config_file . "' is not accessible or doesn't exists.");
		}
		$json = file_get_contents($config_file);
		//Encode to utf-8 if it is already not in utf-8
		if (!mb_check_encoding($json, 'utf-8')) {
			$json = utf8_encode($json);
		}
		//Remove UTF-8 BOM if present, json_decode() does not like it.
		if(substr($json, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) {
		    $json = substr($json, 3);
		}
		$data = json_decode($json, true);
		$this->config_data = $data;
		if ($this->config_data === null) {
			throw new RuntimeException("Config is not well-formed JSON.");
		}
	}

	/**
	 * Get the configuration data from config file
	 * 
	 * @param string $section 
	 * @param string $key 
	 * @param string $default 
	 * @return void
	 */
	public function get($section, $key, $default) {
		if (empty($section) || empty($key) || empty($default)) {
			die("Arguments cannot be empty");
		}
		if(!isset($this->config_data[$section])) {
			die("Configuration section '" . $section . "' doesn't exists");
		}
		if(!isset($this->config_data[$section][$key])) {
			die("Configuration key '" . $key . "' doesn't exists in section '" . $section . "'");
		}

		return empty($this->config_data[$section][$key]) ? $default : $this->config_data[$section][$key];
	}
}
?>