<?php

namespace CRUDServices\ConfigManagers;
use CRUDServices\Helpers\Helpers;
use Exception;

class ConfigManager
{
    protected static ?ConfigManager $instance = null;
    protected array $configs = [];
    protected string $configFileName = "crud-config";
    protected string $configFilePath = "";

    /**
     * @throws Exception
     */
    protected function __construct()
    {
        $this->setConfigFilePath();
        $this->setConfigFileContent();
    }

    static public function Singleton() : ConfigManager
    {
        if(!static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }
    /**
     * @return string
     */
    public function getConfigFileFullName(): string
    {
        return $this->configFileName . ".php";
    }
    /**
     * @return string
     */
    public function getConfigFileName(): string
    {
        return $this->configFileName;
    }
    /**
     * @return string
     * It always set in constructor
     */
    public function getConfigFilePath(): string
    {
        return $this->configFilePath;
    }

    protected function setConfigFilePath() : void
    {
        $this->configFilePath = __DIR__ . "/../../config/". $this->getConfigFileFullName();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function setDefaultConfigFileContent() : void
    {
        $configs = require $this->configFilePath;
        if(!is_array($configs))
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass("CRUDServices config file must return an array of config values");
        }
        $this->configs = $configs;
    }
    protected function setPublishedConfigFileContent() : bool
    {
        $configFileContent = config($this->configFileName);
        if(is_array($configFileContent))
        {
            $this->configs = $configFileContent;
            return true;
        }
        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function setConfigFileContent() : void
    {
        if(!$this->setPublishedConfigFileContent())
        {
            $this->setDefaultConfigFileContent();
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getValue(string $key) : mixed
    {
        return $this->configs[$key] ?? null;
    }
}