<?php

/**
 * Handles all connections to the API
 *
 * Class PlytixRetailersConnection
 */
class PlytixRetailersConnection {

    protected $connection;
    protected $api_password;
    protected $configuration;
    protected $api_key;

    function __construct($api_key, $api_password, $configuration)
    {
        $this->setApiKey($api_key);
        $this->setApiPassword($api_password);
        $this->setConfiguration($configuration);
    }

    /**
     * @return mixed
     */
    public function getApiPassword()
    {
        return $this->api_password;
    }

    /**
     * @param mixed $api_password
     */
    public function setApiPassword($api_password)
    {
        $this->api_password = $api_password;
    }

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param mixed $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @param $endpoint
     * @param $method
     * @param $qs
     * @param null $data
     * @return BaseResponse
     * @throws Exception
     */
    private function makeRequest($endpoint, $method, $qs, $data = null)
    {
        $response = array();

        $config = $this->getConfiguration();
        $url = $config->api_url . $config->api_version . '/' . $endpoint;

        /**
         * Building Query String if exists
         */
        $query_string = http_build_query($qs);
        $url = (strlen($query_string) > 0) ? $url . '?' . $query_string : $url;

        /**
         * Preparing request
         */
        $headers = array('Content-Type' => 'application/json');
        Unirest\Request::auth($this->getApiKey(), $this->getApiPassword(), CURLAUTH_DIGEST);
        $cookieTmpFile = sys_get_temp_dir() . '/' . date('Ymdhms');
        Unirest\Request::cookieFile($cookieTmpFile);
        switch ($method) {
            case 'GET' :
                    $response = Unirest\Request::get($url,$headers);
                break;
            case 'POST' :
                //var_dump($url, $data);die;
                    $response = Unirest\Request::post($url, $headers, $data);
                //var_dump($response);die;

                break;
            case 'PUT' :
                $response = Unirest\Request::put($url, $headers, $data);
                break;
            default :
                throw new Exception ('Not implemented');
        }

        return new BaseResponse($response);
    }

    public function performGet($endpoint, $qs, $data = null)
    {
        return $this->makeRequest($endpoint, 'GET', $qs, $data);
    }

    public function performPost($endpoint, $qs, $data)
    {
        return $this->makeRequest($endpoint, 'POST', $qs, $data);
    }

    public function performPut($endpoint, $qs, $data)
    {
        return $this->makeRequest($endpoint, 'PUT', $qs, $data);
    }
}
