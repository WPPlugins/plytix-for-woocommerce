<?php
require_once dirname(__FILE__) . '/../Models/ResponseModel.php';

/**
 * Bank Service Endpoint
 * Client to consume the Plytix Products Bank. You can search products and add them to your managed products.
 *
 * Class BankService
 */
class BankService extends BaseService {

    const BASE_ENDPOINT = 'bank';
    const BASE_SUBSCRIPTIONS = 'products_added';


    /**
     * Search products in the Plytix Products Bank.
     *
     * @param null $brandId Brand identifier.
     * @param null $folderId Folder identifier.
     * @param null $name Search by product name.
     * @param null $nameList Name(list) identifier.
     * @param null $productId Product identifier.
     * @param null $skuList Sku(list) identifiers.
     * @param null $sku Sku identifier.
     * @param null $eanList Ean(list) identifiers.
     * @param null $ean Ean identifier.
     * @param null $janList Jan(list) identifiers.
     * @param null $jan Jan identifier.
     * @param null $upcList Upc(list) identifiers.
     * @param null $upc Upc identifier.
     * @param null $gtinList Gtin(list) identifiers.
     * @param null $gtin Gtin identifier.
     * @param null $identifierList Identifier(list) identifiers.
     * @param null $identifier Identifier identifier.
     * @param null $operator [OPERATOR.AND, OPERATOR.OR] Modify the query behaviour to retrieve the union or the intersection of the parameters defined.
     * @param null $page Result's page.
     * @param null $pageLength The number of results by page.
     * @param null $fields Fields to retrieve.
     * @param null $sort Result's order.
     * @return ResponseModel ResponseList object of Product instances.
     */
    private function searchAux($brandId=null, $folderId=null, $name=null, $nameList=null, $productId=null,
                               $skuList = null, $sku = null,
                               $eanList = null, $ean = null,
                               $janList = null, $jan = null,
                               $upcList = null, $upc = null,
                               $gtinList = null, $gtin = null,
                               $identifierList = null, $identifier = null,
                               $operator=null, $page=null, $pageLength=null, $fields=null, $sort=null)
    {
        $data = array(
            INPUT_BANK_SEARCH_ACCOUNT => $brandId,
            INPUT_BANK_SEARCH_FOLDER => $folderId,
            INPUT_PRODUCTS_SEARCH_NAME => $name,
            INPUT_BANK_SEARCH_NAMELIST => $nameList ? array_values($nameList) : null,
            INPUT_PRODUCTS_SEARCH_OPERATOR => $operator,
            INPUT_BANK_SEARCH_PRODUCT => $productId,
            INPUT_BANK_SEARCH_SKULIST => $skuList ? array_values($skuList) : null,
            INPUT_BANK_SEARCH_EANLIST => $eanList ? array_values($eanList) : null,
            INPUT_BANK_SEARCH_JANLIST => $janList ? array_values($janList) : null,
            INPUT_BANK_SEARCH_UPCLIST => $upcList ? array_values($upcList) : null,
            INPUT_BANK_SEARCH_GTINLIST => $gtinList ? array_values($gtinList) : null,
            INPUT_BANK_SEARCH_IDENTIFIERLIST => $identifierList ? array_values($identifierList) : null,
            INPUT_BANK_SEARCH_SKU => $sku,
            INPUT_BANK_SEARCH_EAN => $ean,
            INPUT_BANK_SEARCH_JAN => $jan,
            INPUT_BANK_SEARCH_UPC => $upc,
            INPUT_BANK_SEARCH_GTIN => $gtin,
            INPUT_BANK_SEARCH_IDENTIFIER => $identifier
        );

        //Clean all null values
        $data = array_filter($data);

        $endpoint = self::BASE_ENDPOINT;
        $queryResponse = parent::_search($page, $pageLength, $fields, $endpoint, $sort, $data);

        $response = new ArrayObject();
        foreach ($queryResponse->getDataObjects(MODEL_PRODUCTS) as $product) {
            $response->append(new ProductModel($product));
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
     * Search products in the Plytix Products Bank.
     * Using this interface to keep search as in Pyton library
     *
     * @param null $arrayOfArguments
     * @return ResponseModel
     */
    public function search($arrayOfArguments = null) {

        $brandId         = (isset($arrayOfArguments['brand_id']))        ? $arrayOfArguments['brand_id'] : null;
        $folderId        = (isset($arrayOfArguments['folder_id']))       ? $arrayOfArguments['folder_id'] : null;
        $name            = (isset($arrayOfArguments['name']))            ? $arrayOfArguments['name'] : null;
        $nameList        = (isset($arrayOfArguments['name_list']))       ? $arrayOfArguments['name_list'] : null;
        $operator        = (isset($arrayOfArguments['operator']))        ? $arrayOfArguments['operator'] : null;
        $productId       = (isset($arrayOfArguments['product_id']))      ? $arrayOfArguments['product_id'] : null;
        $page            = (isset($arrayOfArguments['page']))            ? $arrayOfArguments['page'] : null;
        $pageLength      = (isset($arrayOfArguments['page_length']))     ? $arrayOfArguments['page_length'] : null;
        $fields          = (isset($arrayOfArguments['fields']))          ? $arrayOfArguments['fields'] : null;
        $sort            = (isset($arrayOfArguments['sort']))            ? $arrayOfArguments['sort'] : null;
        $skuList         = (isset($arrayOfArguments['sku_list']))        ? $arrayOfArguments['sku_list'] : null;
        $sku             = (isset($arrayOfArguments['sku']))             ? $arrayOfArguments['sku'] : null;
        $eanList         = (isset($arrayOfArguments['ean_list']))        ? $arrayOfArguments['ean_list'] : null;
        $ean             = (isset($arrayOfArguments['ean']))             ? $arrayOfArguments['ean'] : null;
        $janList         = (isset($arrayOfArguments['jan_list']))        ? $arrayOfArguments['jan_list'] : null;
        $jan             = (isset($arrayOfArguments['jan']))             ? $arrayOfArguments['jan'] : null;
        $upcList         = (isset($arrayOfArguments['upc_list']))        ? $arrayOfArguments['upc_list'] : null;
        $upc             = (isset($arrayOfArguments['upc']))             ? $arrayOfArguments['upc'] : null;
        $gtinList        = (isset($arrayOfArguments['gtin_list']))       ? $arrayOfArguments['gtin_list'] : null;
        $gtin            = (isset($arrayOfArguments['gtin']))            ? $arrayOfArguments['gtin'] : null;
        $identifierList  = (isset($arrayOfArguments['identifier_list'])) ? $arrayOfArguments['identifier_list'] : null;
        $identifier      = (isset($arrayOfArguments['identifier']))      ? $arrayOfArguments['identifier'] : null;

        return $this->searchAux($brandId,$folderId,$name,$nameList,$productId,$skuList,$sku,$eanList,$ean,$janList,$jan,$upcList,$upc,$gtinList,$gtin,$identifierList,$identifier,$operator,$page,$pageLength,$fields,$sort);
    }

    /**
     * Add products to your managed products. You must define your destination folder and a folder identifier's list or a product identifier's list.
     *
     * @param FolderModel $destinationFolder Folder where store the products to add.
     * @param null $folderList Folder identifier's list. All products contained in them will be added to your account.
     * @param null $productList Product identifier's list. All of them will be added to your account.
     * @return Array Return Array with pairs Plytix bank_id (Bank Product ID), product_id (My own Product / Subscription ID)
     * @throws Exception
     */
    public function addTo(FolderModel $destinationFolder, $folderList=null, $productList=null)
    {
        if ($folderList == null && $productList == null) {
            throw new Exception('No folder_list or products_list defined.');
        }

        $data = array(
            INPUT_BANK_ADD_DESTINATION => $destinationFolder->getId()
        );

        if ($productList) {
            $data[INPUT_BANK_ADD_PRODUCTS] = $productList;
        }

        if ($folderList) {
            $data[INPUT_BANK_ADD_FOLDERS] = $folderList;
        }

        $endpoint = self::BASE_ENDPOINT . '/add';

        $queryResponse = parent::_create(null, null, $endpoint, json_encode($data));

        $response = $queryResponse->getDataObjects(self::BASE_SUBSCRIPTIONS);

        return $response;
    }

}
