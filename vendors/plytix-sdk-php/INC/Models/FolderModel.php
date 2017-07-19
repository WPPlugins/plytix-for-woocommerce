<?php
require_once dirname(__FILE__) . '/AbstractModel.php';
require_once dirname(__FILE__) . '/ProductModel.php';

/**
 * Class FolderModel
 */
class FolderModel extends AbstractModel {

    protected $name;
    protected $id;
    protected $folders;
    protected $products;
    protected $parent;

    function __construct(stdClass $responseObject) {
        $this->setName($responseObject->name);
        $this->setParent($responseObject->parent);
        $this->setId($responseObject->id);
        $this->setFolders($responseObject->folders);
        $this->setProducts($responseObject->products);
    }

    /**
     * @return mixed
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * @param mixed $folders
     */
    private function setFolders($folders)
    {
        $this->folders = $folders;
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
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    private function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Gets all products and instanciate them into Product Models Objects
     * @param mixed $products
     */
    private function setProducts($products)
    {
        $productsArray = new ArrayObject();
        foreach ($products as $product) {
            $productsArray->append(new ProductModel($product));
        }
        $this->products = $productsArray;
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
