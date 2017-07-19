<?php
require_once dirname(__FILE__) . '/AbstractModel.php';

/**
 * Class SiteModel
 */
class SiteModel extends AbstractModel {

    /**
     * @var String
     */
    protected $protocol;
    /**
     * @var String
     */
    protected $name;
    /**
     * @var String
     */
    protected $url;
    /**
     * @var String
     */
    protected $uri;
    /**
     * @var Boolean
     */
    protected $debug;

    /**
     * @var String
     */
    protected $timezone;

    /**
     * @var String
     */
    protected $id;

    /**
     * @var array
     */
    protected $info;


    /**
     * Constructor takes stdObject from API
     * or Array from Client SdK
     * @param null $object
     */
    function __construct($object = null)
    {
        $returned = $this;
        if ($object instanceof stdClass) {
            $returned = $this->constructorFromObjectApi($object);
        } elseif (is_array($object)) {
            $returned = $this->constructorFromArraySDK($object);
        }
        return $returned;
    }

    /**
     * When Site model instance is created from API query
     *
     * @param stdClass $object
     */
    private function constructorFromObjectApi(stdClass $object)
    {
        $this->setDebug($object->debug);
        $this->setId($object->id);
        $this->setName($object->name);
        $this->setProtocol($object->protocol);
        $this->setTimezone($object->timezone);
        $this->setUri($object->uri);
        $this->setUrl($object->url);
    }

    /**
     * When Site model instance is created from Array Object from SDK client side
     *
     * @param array $object
     */
    private function constructorFromArraySDK(Array $object)
    {
        if (!isset($object['debug'])) {
            $this->setDebug(true);
        } else {
            $this->setDebug($object['debug']);
        }
        $this->setName($object['name']);
        $this->setProtocol($object['protocol']);
        $this->setTimezone($object['timezone']);
        $this->setUrl($object['url']);
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return String
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return String
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return String
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return String
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param String $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param String $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @param String $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @param String $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param String $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * It takes Site Model Object and converts it to JSON to make request
     *
     * @param AbstractModel $model
     * @return string
     */
    function modelToJson(AbstractModel $model)
    {
        $modelToObject = $this->objectToArray($model);
        unset($modelToObject['id']);
        return json_encode($modelToObject);
    }
}
