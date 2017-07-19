<?php
require_once dirname(__FILE__) . '/../Models/SiteModel.php';
require_once dirname(__FILE__) . '/../Models/ResponseModel.php';
require_once dirname(__FILE__) . '/../timezones.php';

/**
 * Client Service Endpoint
 * Client to consume the sites you manage in your account.
 *
 * Class SiteService
 */
class SiteService extends BaseService{

    const BASE_ENDPOINT = 'sites';
    /**
     * When you perform a GET, API retrieves only a site (in singular)
     */
    const BASE_ENDPOINT_SINGULAR = 'site';

    /**
     * Lists all your sites
     *
     * @param null $page Result's page
     * @param null $pageLength Result's limit
     * @param null $fields Fields to retrieve
     * @param null $sort Result's order
     * @return ResponseModel Returning Response Model object
     */
    public function listSites($page=null, $pageLength=null, $fields=null, $sort=null)
    {
        $endpoint = self::BASE_ENDPOINT;
        $queryResponse = parent::_list($page, $pageLength, $fields, $endpoint, $sort);
        $response = new ArrayObject();

        foreach ($queryResponse->getDataObjects(self::BASE_ENDPOINT) as $site) {
            $response->append(new SiteModel($site));
        }

        return new ResponseModel(
            $queryResponse->getTotal(),
            $queryResponse->getTotalPages(),
            $queryResponse->getPage(),
            $queryResponse->getCode(),
            $response);
    }

    /**
     * Performs Search
     *
     * @param null $name Name
     * @param null $url Url
     * @param null $protocol Protocol
     * @param null $page Result's page
     * @param null $pageLength The number of results by page
     * @param null $fields Fields to retrieve
     * @param null $sort Result's order
     * @return ResponseModel ResponseList object of Product instances.
     */
    private function searchAux($name=null, $url=null, $protocol=null, $page=null, $pageLength=null, $fields=null, $sort=null)
    {
        $data = array(
            INPUT_SITES_SEARCH_NAME     => $name,
            INPUT_SITES_SEARCH_PROTOCOL => $protocol,
            INPUT_SITES_SEARCH_URL      => $url,
        );
        //Clean all null values
        $data = array_filter($data);

        $endpoint = self::BASE_ENDPOINT . '/search';
        $queryResponse = parent::_search($page, $pageLength, $fields, $endpoint, $sort, $data);

        $response = new ArrayObject();
        foreach ($queryResponse->getDataObjects(self::BASE_ENDPOINT) as $site) {
            $response->append(new SiteModel($site));
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
    public function search($arrayOfArguments = null)
    {
        $name       = (isset($arrayOfArguments['name']))        ? $arrayOfArguments['name']        : null;
        $url        = (isset($arrayOfArguments['url']))         ? $arrayOfArguments['url']         : null;
        $protocol   = (isset($arrayOfArguments['protocol']))    ? $arrayOfArguments['protocol']    : null;
        $page       = (isset($arrayOfArguments['page']))        ? $arrayOfArguments['page']        : null;
        $pageLength = (isset($arrayOfArguments['page_length'])) ? $arrayOfArguments['page_length'] : null;
        $fields     = (isset($arrayOfArguments['fields']))      ? $arrayOfArguments['fields']      : null;
        $sort       = (isset($arrayOfArguments['sort']))        ? $arrayOfArguments['sort']        : null;

        return $this->searchAux($name, $url, $protocol, $page, $pageLength, $fields, $sort);
    }

    /**
     * Get a Site by ID, retrieves a SiteModel
     *
     * @param string $id Site's identifier
     * @param null $fields Fields of the object to retrieve
     * @return SiteModel Site instance of the site requested
     */
    public function get($id, $fields=null)
    {
        $endpoint = self::BASE_ENDPOINT_SINGULAR;
        $queryResponse = parent::_get($id, $fields, $endpoint);

        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT_SINGULAR);
        return new SiteModel($response);
    }

    /**
     * Create a new site
     *
     * @param SiteModel $site Site to be added
     * @param null $fields
     * @return SiteModel Responses with the new SiteModel created
     * @throws Exception
     */
    public function create(SiteModel $site, $fields=null)
    {
        if (!$site instanceof SiteModel) {
            throw new Exception('Site Not Valid');
        }
        $endpoint = self::BASE_ENDPOINT;
        $queryResponse = parent::_create($site, $fields, $endpoint);
        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT_SINGULAR);
        return new SiteModel($response);
    }

    /**
     * @param SiteModel $site Site to be Updated
     * @param null $fields
     * @return SiteModel Responses with the siteModel Modified
     * @throws Exception
     */
    public function update(SiteModel $site, $fields=null)
    {
        if (!$site instanceof SiteModel) {
            throw new Exception('Site Not Valid');
        }
        $endpoint = self::BASE_ENDPOINT_SINGULAR;
        $queryResponse = parent::_update($site, $fields, $endpoint);
        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT_SINGULAR);
        return new SiteModel($response);
    }

    /**
     * It returns all TimeZones Supported
     *
     * @return string
     */
    public function getTimeZones()
    {
        $time_zones = new TimeZones();
        return json_encode($time_zones->getTimeZones());
    }
}
