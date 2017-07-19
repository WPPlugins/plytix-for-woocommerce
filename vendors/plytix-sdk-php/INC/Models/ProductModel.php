<?php
require_once dirname(__FILE__) . '/AbstractModel.php';

/**
 * Model For Products
 * This Class will handle all kind of (Model)responses.
 *
 * Class ProductModel
 */
class ProductModel extends AbstractModel {

    protected $id;
    protected $name;
    protected $sku;
    protected $ean;
    protected $jan;
    protected $upc;
    protected $gtin;
    protected $thumb;
    protected $folder;
    protected $brandId;
    protected $brandName;
    protected $brandPicture;

    /**
     * @param stdClass $object
     */
    function __construct(stdClass $object)
    {
        $this->setFolder($object->folder);
        $this->setId($object->id);
        $this->setName($object->name);
        $this->setSku($object->sku);
        $this->setEan($object->ean);
        $this->setJan($object->jan);
        $this->setUpc($object->upc);
        $this->setGtin($object->gtin);
        $this->setThumb($object->thumb);
        //Todo: Right now it is only for bank response
        if (isset($object->brand_id))
        {
            $this->setBrandId($object->brand_id);
        }
        if (isset($object->brand_name))
        {
            $this->setBrandName($object->brand_name);
        }
    }

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param mixed $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
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
    public function setId($id)
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return mixed
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param mixed $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return mixed
     */
    public function getJan()
    {
        return $this->jan;
    }

    /**
     * @param mixed $jan
     */
    public function setJan($jan)
    {
        $this->jan = $jan;
    }

    /**
     * @return mixed
     */
    public function getUpc()
    {
        return $this->upc;
    }

    /**
     * @param mixed $upc
     */
    public function setUpc($upc)
    {
        $this->upc = $upc;
    }

    /**
     * @return mixed
     */
    public function getGtin()
    {
        return $this->gtin;
    }

    /**
     * @param mixed $gtin
     */
    public function setGtin($gtin)
    {
        $this->gtin = $gtin;
    }

    /**
     * @return mixed
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * @param mixed $thumb
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
    }

    /**
     * @return mixed
     */
    public function getBrandId()
    {
        return $this->brandId;
    }

    /**
     * @param mixed $brandId
     */
    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;
    }

    /**
     * @return mixed
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param mixed $brandId
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return mixed
     */
    public function getbrandPicture()
    {
        return $this->brandPicture;
    }

    /**
     * @param mixed $brandPicture
     */
    public function setbrandPicture($brandPicture)
    {
        $this->brandPicture = $brandPicture;
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
