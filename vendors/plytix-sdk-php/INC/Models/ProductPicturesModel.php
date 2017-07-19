<?php
require_once dirname(__FILE__) . '/AbstractModel.php';
require_once dirname(__FILE__) . '/PictureModel.php';

/**
 * Model For Products
 * This Class will handle all kind of (Model)responses.
 *
 * Class ProductModel
 */
class ProductPicturesModel extends AbstractModel {

    /**
     * @var
     */
    protected $product_id;
    /**
     * @var PictureModel
     */
    protected $pictures;

    /**
     * @param stdClass $object
     */
    function __construct(stdClass $object)
    {
        $this->setProductId($object->product_id);
        $pictures = array();
        foreach ($object->pictures as $picture) {
                $pictures[] = new PictureModel($picture);
        }
        $this->setPictures($pictures);
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param mixed $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * @return PictureModel
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param $pictures
     */
    public function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * ModelObject to Json *Not really needed
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
