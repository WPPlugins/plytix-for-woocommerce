<?php
require_once dirname(__FILE__) . '/../Models/ProductModel.php';
require_once dirname(__FILE__) . '/../Models/ProductPicturesModel.php';
require_once dirname(__FILE__) . '/../Models/ResponseModel.php';

/**
 * Product Service Endpoint
 * Client to consume the products you manage in your account.
 *
 * Class ProductService
 */
class ProductService extends BaseService {

    const BASE_ENDPOINT = 'products';
    const BASE_PICTURES_ENDPOINT = 'pictures';
    const BASE_ENDPOINT_SINGULAR = 'product';

    /**
     * Get a Product by ID, retrieves a ProductModel
     *
     * @param string $id Site's identifier
     * @param null $fields Fields of the object to retrieve
     * @return ProductModel Site instance of the site requested
     */
    public function get($id, $fields=null)
    {
        $endpoint = self::BASE_ENDPOINT_SINGULAR;
        $queryResponse = parent::_get($id, $fields, $endpoint);
        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT_SINGULAR);

        return new ProductModel($response);
    }

    /**
     * @param $productList
     * @param $sizes
     * @return ResponseModel
     */
    public function pictures($productList, $sizes = null, $page = null, $pageLength = null, $fields=null, $sort = null)
    {
        $data = array(
            INPUT_PRODUCTS_SEARCH_PRODUCTS_LIST => $productList,
        );
        if ($sizes) {
            $data[INPUT_PRODUCTS_SIZES] = $sizes;
        }
        $endpoint = self::BASE_PICTURES_ENDPOINT;
        $queryResponse = parent::_search($page, $pageLength, $fields, $endpoint, $sort, $data);
        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT);

        $listOfPictures = new ArrayObject();
        foreach ($response as $picture) {
            $listOfPictures->append(new ProductPicturesModel($picture));
        }

        return new ResponseModel(
            $queryResponse->getTotal(),
            $queryResponse->getTotalPages(),
            $queryResponse->getPage(),
            $queryResponse->getCode(),
            $listOfPictures
        );
    }

    /**
     * @param $productListVersion
     * @return ResponseModel
     */
    public function update_latest_pictures($productListVersion, $sizes)
    {
        $idList = array();
        foreach ($productListVersion as $product_id => $info) {
            array_push($idList, $product_id);
        }

        $response_model = $this->pictures($idList, $sizes);
        $responseOfProducts = $response_model->getResults()? $response_model->getResults()->getArrayCopy() :  array();

        while ($response_model->getPage() < $response_model->getTotalPages()) {
            $response_model = $this->pictures($idList, $sizes, $response_model->getPage() + 1);
            $responseOfProducts = $response_model->getResults()? array_merge( $responseOfProducts, $response_model->getResults()->getArrayCopy() )  :  array();
        }

        $products_to_update = array();
        if (!empty($responseOfProducts)) {
            foreach ($responseOfProducts as $product) {
                $pictures_to_update = array();
                foreach ($product->getPictures() as $k => $picture) {
                    if  ((isset($productListVersion[$product->getProductId()][$picture->getPictureId()])) &&
                            ($productListVersion[$product->getProductId()][$picture->getPictureId()] != $picture->getVersion()))
                    {
                        $pictures_to_update['pictures'][$k]['original']['url_to_version'] = $picture->getOriginal()->url_to_version;
                        $pictures_to_update['pictures'][$k]['version']    = $picture->getVersion();
                        $pictures_to_update['pictures'][$k]['picture_id'] = $picture->getPictureId();
                        $products_to_update[$product->getProductId()] = $pictures_to_update;
                    }
                }
            }
        }
        return $products_to_update;
    }

    /**
     * Performs Search
     *
     * @param null $folderList Folder identifier's list.
     * @param null $groupBy Group results by
     * @param null $name Search by product name.
     * @param null $nameList Name identifier's list.
     * @param null $operator [OPERATOR.AND, OPERATOR.OR] Modify the query behaviour to retrieve the union or the intersection of the parameters defined.
     * @param null $productList Product identifier's list.
     * @param null $skuList SKU identifier's list.
     * @param null $skuList SKU identifier's list.
     * @param null $eanList EAN identifier's list.
     * @param null $janList JAN identifier's list.
     * @param null $upcList UPC identifier's list.
     * @param null $gtinList GTIN identifier's list.
     * @param null $identifierList Identifier identifier's list.
     * @param null $page Result's page.
     * @param null $pageLength The number of results by page.
     * @param null $fields Fields to retrieve.
     * @param null $sort Result's order.
     * @return ResponseModel ResponseList object of Product instances.
     */
    private function searchAux($folderList=null, $groupBy=null, $name=null, $nameList=null, $operator=null, $productList=null,
                               $skuList=null, $eanList = null, $janList = null, $upcList = null, $gtinList = null,  $identifierList = null,
                               $page=null, $pageLength=null, $fields=null, $sort=null)
    {
        $data = array(
            INPUT_PRODUCTS_SEARCH_FOLDERS_LIST      => $folderList,
            INPUT_PRODUCTS_SEARCH_GROUP_BY          => $groupBy,
            INPUT_PRODUCTS_SEARCH_NAME              => $name,
            INPUT_PRODUCTS_SEARCH_NAME_LIST         => $nameList,
            INPUT_PRODUCTS_SEARCH_OPERATOR          => $operator,
            INPUT_PRODUCTS_SEARCH_PRODUCTS_LIST     => $productList,
            INPUT_PRODUCTS_SEARCH_SKU_LIST          => $skuList,
            INPUT_PRODUCTS_SEARCH_EAN_LIST          => $eanList,
            INPUT_PRODUCTS_SEARCH_JAN_LIST          => $janList,
            INPUT_PRODUCTS_SEARCH_UPC_LIST          => $upcList,
            INPUT_PRODUCTS_SEARCH_GTIN_LIST         => $gtinList,
            INPUT_PRODUCTS_SEARCH_IDENTIFIER_LIST   => $identifierList
        );
        //Clean all null values
        $data = array_filter($data);

        $endpoint = self::BASE_ENDPOINT;
        $queryResponse = parent::_search($page, $pageLength, $fields, $endpoint, $sort, $data);

        /**
         * If we sort by brand, result changes and we need to output differently
         *  Brand A [id]
         *    Brand[name]
         *    Products
         *       - Product X
         *       - Product Y
         *  Brand B [id]
         *    Brand[name]
         *    Products
         *       - Product I
         *       - Product Z
         */
        if (isset($data[INPUT_PRODUCTS_SEARCH_GROUP_BY]) && $data[INPUT_PRODUCTS_SEARCH_GROUP_BY] == 'BRAND') {
            $responseByBrands = $queryResponse->getDataObjects(MODEL_ACCOUNTS);

            $response = new ArrayObject();
            foreach ($responseByBrands as $brand) {
                $response[$brand->brand->id]['name'] = $brand->brand->name;
                $listOfProducts = new ArrayObject();
                foreach ($brand->products as $product) {
                    $listOfProducts->append(new ProductModel($product));
                }
                $response[$brand->brand->id]['products'] = $listOfProducts;
            }
        } else {
            $response = new ArrayObject();
            foreach ($queryResponse->getDataObjects(self::BASE_ENDPOINT) as $brand) {
                $response->append(new ProductModel($brand));
            }
        }

        return new ResponseModel(
            $queryResponse->getTotal(),
            $queryResponse->getTotalPages(),
            $queryResponse->getPage(),
            $queryResponse->getCode(),
            $response
        );
    }

    /**
     * Using this interface to keep search as in Python library
     *
     * @param null $arrayOfArguments
     * @return ResponseModel
     */
    function search($arrayOfArguments = null) {

        $folderList     = (isset($arrayOfArguments['folderList']))      ? $arrayOfArguments['folderList']  : null;
        $groupBy        = (isset($arrayOfArguments['groupBy']))         ? $arrayOfArguments['groupBy']     : null;
        $name           = (isset($arrayOfArguments['name']))            ? $arrayOfArguments['name']        : null;
        $nameList       = (isset($arrayOfArguments['nameList']))        ? $arrayOfArguments['nameList']     : null;
        $operator       = (isset($arrayOfArguments['operator']))        ? $arrayOfArguments['operator']    : null;
        $productList    = (isset($arrayOfArguments['productList']))     ? $arrayOfArguments['productList'] : null;
        $skuList        = (isset($arrayOfArguments['skuList']))         ? $arrayOfArguments['skuList']     : null;
        $eanList        = (isset($arrayOfArguments['ean_list']))        ? $arrayOfArguments['ean_list'] : null;
        $janList        = (isset($arrayOfArguments['jan_list']))        ? $arrayOfArguments['jan_list'] : null;
        $upcList        = (isset($arrayOfArguments['upc_list']))        ? $arrayOfArguments['upc_list'] : null;
        $gtinList       = (isset($arrayOfArguments['gtin_list']))       ? $arrayOfArguments['gtin_list'] : null;
        $identifierList = (isset($arrayOfArguments['identifier_list'])) ? $arrayOfArguments['identifier_list'] : null;
        $page           = (isset($arrayOfArguments['page']))            ? $arrayOfArguments['page']        : null;
        $pageLength     = (isset($arrayOfArguments['page_length']))     ? $arrayOfArguments['page_length'] : null;
        $fields         = (isset($arrayOfArguments['fields']))          ? $arrayOfArguments['fields']      : null;
        $sort           = (isset($arrayOfArguments['sort']))            ? $arrayOfArguments['sort']        : null;

        return $this->searchAux($folderList, $groupBy, $name, $nameList, $operator, $productList, $skuList, $eanList, $janList, $upcList, $gtinList,  $identifierList, $page, $pageLength, $fields, $sort);
    }

    /**
     * Shortcut to group by Brands (search with forced Parameter)
     *
     * @param null $arrayOfArguments
     * @return ResponseModel
     */
    public function searchAndGroupByBrand($arrayOfArguments = null)
    {
        $arrayOfArguments['groupBy'] = 'BRAND';
        return $this->search($arrayOfArguments);
    }
}
