<?php
/**
 * Autoloader
 */
require_once dirname(__FILE__) . '/../INC/autoloader.php';
require_once dirname(__FILE__) . '/PlytixRetailersConnection.php';
require_once dirname(__FILE__) . '/../INC/Services/BaseResponse.php';
require_once dirname(__FILE__) . '/../INC/Services/BaseService.php';
require_once dirname(__FILE__) . '/../INC/Services/BankService.php';
require_once dirname(__FILE__) . '/../INC/Services/BrandService.php';
require_once dirname(__FILE__) . '/../INC/Services/FolderService.php';
require_once dirname(__FILE__) . '/../INC/Services/ProductService.php';
require_once dirname(__FILE__) . '/../INC/Services/SiteService.php';
require_once dirname(__FILE__) . '/../INC/Services/CredentialService.php';

/**
 * Handles Client Side calls
 *
 * Class PlytixRetailersClient
 */
class PlytixRetailersClient {

    protected  $configuration;
    protected  $connection;

    /**
     * @param $api_key
     * @param $api_password
     */
    function __construct($api_key, $api_password)
    {
        $this->setConfiguration($this->readConfiguration());
        $this->connection = new PlytixRetailersConnection($api_key, $api_password, $this->getConfiguration());
    }

    /**
     * @return mixed
     */
    private function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param mixed $configuration
     */
    private function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return mixed
     */
    private function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    private function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Reading from Configuration XML File
     * @return SimpleXMLElement
     */
    private function readConfiguration()
    {
        $config = Config::instance();
        $config->init();
        return $config->getOptions();
    }

    /**
     * Getting Brand Service
     *
     * @return BrandService
     */
    public function brands()
    {
        return new BrandService($this->connection);
    }

    /**
     * Getting Site Service
     *
     * @return SiteService
     */
    public function sites()
    {
        return new SiteService($this->connection);
    }

    /**
     * Getting Product Service
     *
     * @return ProductService
     */
    public function products()
    {
        return new ProductService($this->connection);
    }

    /**
     * Getting Folder Service
     *
     * @return FolderService
     */
    public function folders()
    {
        return new FolderService($this->connection);
    }

    /**
     * Getting Bank Service
     *
     * @return BankService
     */
    public function bank()
    {
        return new BankService($this->connection);
    }

    /**
     * Getting Credential Service
     *
     * @return CredentialService
     */
    public function credentials()
    {
        return new CredentialService($this->connection);
    }
}
