<?php
require_once dirname(__FILE__) . '/AbstractModel.php';
/**
 * Class BrandModel
 */
class BrandModel extends AbstractModel {

    protected $name;
    protected $website;
    protected $picture;
    protected $id;

    /**
     * @param stdClass $object
     */
    public function __construct(stdClass $object)
    {
        $this->setName($object->name);
        $this->setWebsite($object->website);
        $this->setPicture($object->picture);
        $this->setId($object->id);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    private function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    private function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    private function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     */
    private function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Not really needed
     *
     * @param AbstractModel $obj
     * @return bool
     */
    function modelToJson(AbstractModel $obj)
    {
        return true;
    }

}
