<?php

define('SYSTEM_PATH_E', __DIR__ . DIRECTORY_SEPARATOR . '..');
define("APP_CONFIG_PATH", __DIR__ . DIRECTORY_SEPARATOR);

class Config
{
    /**
     * Ini configuration
     *
     * @var Array
     */
    private $_config;

    /**
     * Singleton variable
     *
     * @var Config
     */
    private static $_instance;

    /**
     * Settings from Ini File
     *
     * @var stdClass
     */
    private $_options;

    /**
     * Singleton function to instantiate the class
     *
     * @return Config
     */
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Checks if config file is on place, if so,
     * it loads configuration and set options.
     *
     * @throws Exception
     */
    public function init()
    {
        if (!file_exists(APP_CONFIG_PATH . "config.ini")) {
            throw new Exception ("Could not find app config file: config.ini");
        }

        $this->_config = parse_ini_file(APP_CONFIG_PATH . "config.ini");
        $this->setOptions();
    }

    /**
     * Reads and load from INI configuration
     */
    private function setOptions()
    {
        $this->_options = new stdClass();
        $this->_options->api_version = $this->_config['api_version'];
        $this->_options->api_url = $this->_config['api_url'];
    }

    /**
     * Get Options
     * @return SimpleXMLElement
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
