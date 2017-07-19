<?php
require_once dirname(__FILE__) . '/../Models/BrandModel.php';
require_once dirname(__FILE__) . '/../Models/ResponseModel.php';
/**
 * Brand Service endpoint
 * Endpoint to consume brand resources.
 *
 * Class BrandService
 */
class BrandService extends BaseService {

    const BASE_ENDPOINT = 'brands';

    /**
     * Performing Search
     *
     * @param $name string Search by brand name
     * @param null $page int Result's page
     * @param null $pageLength int The number of results by Page
     * @param null $fields Fields to retrieve
     * @param null $sort Result's order
     * @return ResponseModel Returning Response Model object
     */
    function search($name, $page=null, $pageLength=null, $fields=null, $sort=null)
    {

        $data = array(
            INPUT_PRODUCTS_SEARCH_NAME => $name
        );
        $endpoint = self::BASE_ENDPOINT . '/search';

        $queryResponse = parent::_search($page, $pageLength, $fields, $endpoint, $sort, $data);

        $response = new ArrayObject();
        foreach ($queryResponse->getDataObjects(self::BASE_ENDPOINT) as $brand) {
            $response->append(new BrandModel($brand));
        }

        return new ResponseModel(
            $queryResponse->getTotal(),
            $queryResponse->getTotalPages(),
            $queryResponse->getPage(),
            $queryResponse->getCode(),
            $response
        );
    }
}
