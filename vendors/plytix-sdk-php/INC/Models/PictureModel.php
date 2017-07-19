<?php
require_once dirname(__FILE__) . '/AbstractModel.php';

/**
 * Model For Picture
 * This Class will handle all kind of (Model)responses.
 *
 * Class PictureModel
 */
class PictureModel extends AbstractModel
{
    protected $picture_id;
    protected $version;
    protected $original;
    protected $thumbs;

    /**
     * @param stdClass $object
     */
    function __construct(stdClass $object)
    {
        $this->setPictureId($object->picture_id);
        $this->setVersion($object->version);
        $this->setOriginal($object->original);
        $this->setThumbs($object->thumbs);
    }

    /**
     * @return mixed
     */
    public function getPictureId()
    {
        return $this->picture_id;
    }

    /**
     * @param mixed $picture_id
     */
    public function setPictureId($picture_id)
    {
        $this->picture_id = $picture_id;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getThumbs()
    {
        return $this->thumbs;
    }

    /**
     * @param $thumbs
     */
    public function setThumbs($thumbs)
    {
        $this->thumbs = $thumbs;
    }

    /**
     * ModelObject to Json *Not really needed*
     *
     * @param AbstractModel $model
     * @return bool
     */
    function modelToJson(AbstractModel $model)
    {
        $modelToObject = $this->objectToArray($model);
        return json_encode($modelToObject);
    }
}
