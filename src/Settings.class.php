<?php
namespace fdask\surveybot;

/**
* methods to let us read values from an ini file
**/
class Settings {
	/** @var array cached copy of the ini file **/
	private static $_iniData = null;

	/**
	* absolute path!
	*
	* @return string
	**/
	public static function getPath() {
		$curDir = __DIR__;

		$bits = explode('/', str_replace('\\', '/', $curDir));

		array_pop($bits);
		array_pop($bits);

		return implode('/', $bits);
	}

	/**
	* absolute path of the ini file we want to load
	*
	* @return string
	**/
	public static function getFilename() {
		return static::getPath() . "/config.ini";
	}

	/**
	* parses entire ini file into memory
	**/
	private static function _loadIniFile() {
		$file = static::getFilename();

		if (file_exists($file)) {
			$data = parse_ini_file($file, true);

			if ($data) {
				static::$_iniData = $data;
			} else {
				throw new \Exception("Failed to parse the data in $file");
			}
		} else {
			throw new \Exception("Could not load the settings.ini file from $file");
		}
	}

	/**
	* gets a single value
	*
	* @param string $section
	* @param string $key
	* @return string
	**/
	public static function get_ini_value($section, $key) {
		if (is_null(static::$_iniData)) {
			static::_loadIniFile();
		}

		if (isset(static::$_iniData[$section])) {
			if (isset(static::$_iniData[$section][$key])) {
				return static::$_iniData[$section][$key];
			} else {
				throw new \Exception("Couldn't find value $key in section $section of settings.ini file " . static::getFilename());
			}
		} else {
			throw new \Exception("Couldn't find section $section in settings.ini file " . static::getFilename());
		}
	}

	/**
	* load an entire section
	*
	* @param string $section
	* @return array
	**/
	public static function get_ini_section($section) {
		if (is_null(static::$_iniData)) {
			static::_loadIniFile();
		}

		if (isset(static::$_iniData[$section])) {
			return static::$_iniData[$section];
		} else {
			throw new \Exception("Couldn't find section $section in settings.ini file " . static::getFilename());
		}
	}
}
