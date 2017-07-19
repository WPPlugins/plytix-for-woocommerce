<?php
require_once dirname(__FILE__) . '/AbstractModel.php';

class OriginalModel extends AbstractModel
{
    protected $url_to_latest;
    protected $url_to_version;

    /**
     * @param stdClass $object
     */
    function __construct(stdClass $object)
    {
        $this->setUrlToLatest($object->url_to_latest);
        $this->setUrlToVersion($object->url_to_version);
    }

    /**
     * @return mixed
     */
    public function getUrlToLatest()
    {
        return $this->url_to_latest;
    }

    /**
     * @return mixed
     */
    public function getUrlToVersion()
    {
        return $this->url_to_version;
    }

    /**
     * @param $url_to_latest
     */
    public function setUrlToLatest($url_to_latest)
    {
        $this->url_to_latest = $url_to_latest;
    }

    /**
     * @param $url_to_version
     */
    public function setUrlToVersion($url_to_version)
    {
        $this->url_to_version = $url_to_version;
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
        return json_encode($modelToObject);
    }
}
