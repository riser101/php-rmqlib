<?php namespace CommonLib;

class Log {
	private $log_file_handler;
	private $date_format;

	/**
	 * The class constructor
	 * 
	 * @param string $file       The file location
	 * @param date $date_format  The date format
	 * @param string $time_zone  The time zone
	 * @return void
	 */
	public function __construct($file = 'logs/logs.log', $date_format = 'd-M-Y G:i:s e', $time_zone = 'UTC') {
		$this->date_format = $date_format;
		date_default_timezone_set($time_zone);
		$this->log_file_handler = fopen($file, "a");
		if (!is_writable($file)) {
			throw new RuntimeException("Log file is not writable");
		}
	}

	/**
	 * The class destructor
	 * 
	 * @return void
	 */
	public function __destruct(){
		if ($this->log_file_handler !== null) {
			fclose($this->log_file_handler);
		}
	}

	/**
	 * Write the log to file
	 * 
	 * @param string $log   The log message
	 * @return void
	 */
	private function write_log($log) {
		if ($this->log_file_handler === null) {
			throw new RuntimeException("Could not open log file for writing. Check file permissions.");
		}
		fwrite($this->log_file_handler, $log);
	}

	/**
	 * Generate log
	 * 
	 * @param int $errCode    The error code
	 * @param string $errMsg The error message
	 * @param string $errDes 
	 * @param string $errMtd 
	 * @param bool $print 
	 * @return type
	 */
	public function log_error($errCode, $errMsg, $errDes, $errMtd, $print = true) {
		$log = 'Error ' . $errCode . ' : ' . $errMsg;
		$log .= PHP_EOL . "Error Source => " . $errMtd;
		if (!empty($errDes)) {
			$log.= PHP_EOL . "Error Description => " . $errDes;
		}
		$log .= PHP_EOL . "Server Name => " . gethostname() . ' (' . gethostbyname(php_uname('n')) . ')';
		$this->write_log(PHP_EOL . PHP_EOL . '[' . date($this->date_format) . '] ' . $log);
		if ($print) {
			echo $log;
		}
	}
}
?>