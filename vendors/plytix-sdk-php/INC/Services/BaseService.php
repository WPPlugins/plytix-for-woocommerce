<?php
/**
 * Base Service to perfrom SDK calls
 *
 * Class BaseService
 */
class BaseService {

    const INPUT_LIMIT_NUMBER = 100;
    const RESPONSE_METADATA_PAGE = 1;

    protected $connection;

    function __construct(PlytixRetailersConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Search Resources
     * @param null $page Page
     * @param null $pageLength Items per page
     * @param null $fields Fields to retrieve
     * @param null $endpoint Endpoint resource
     * @param null $sort Fields to sort by
     * @param null $data Data to send
     * @return BaseResponse The network response
     */
    protected function _search ($page=null, $pageLength=null, $fields=null, $endpoint=null, $sort=null, $data=null)
    {
        $page       = (!is_null($page))       ? $page                 : self::RESPONSE_METADATA_PAGE;
        $pageLength = (!is_null($pageLength)) ? $pageLength           : self::INPUT_LIMIT_NUMBER;
        $fields     = (is_array($fields))     ? implode(',', $fields) : null;
        $sort       = (!is_null($sort))       ? $sort                 : null;
        $data       = (is_array($data))       ? json_encode($data)    : array();

        $qs = array (
            RESPONSE_METADATA_PAGE => $page,
            INPUT_PAGE_LENGTH => $pageLength,
            INPUT_FIELDS => $fields,
            INPUT_SORT => $sort
        );

        // if ($endpoint == 'pictures') $qs = array();

        return $this->connection->performPost($endpoint, $qs, $data);
    }

    /**
     * Get a List Of Resources
     * @param null $page Page
     * @param null $pageLength Items per page
     * @param null $fields Fields to retrieve
     * @param null $endpoint Endpoint resource
     * @param null $sort Fields to sort by
     * @param null $productOwnership (Only for Folders Case)
     * @return BaseResponse The network response
     */
    protected function _list($page=null, $pageLength=null, $fields=null, $endpoint=null, $sort=null, $productOwnership=null)
    {
        $page       = (!is_null($page))       ? $page                 : self::RESPONSE_METADATA_PAGE;
        $pageLength = (!is_null($pageLength)) ? $pageLength           : self::INPUT_LIMIT_NUMBER;
        $fields     = (is_array($fields))     ? implode(',', $fields) : null;
        $sort       = (is_null($sort))        ? $sort                 : null;

        $qs = array (
            RESPONSE_METADATA_PAGE => $page,
            INPUT_PAGE_LENGTH => $pageLength,
            INPUT_FIELDS => $fields,
            INPUT_SORT => $sort
        );

        /**
         * Case only for Get List of Folders
         */
        if ($productOwnership) {
            $qs[INPUT_PRODUCTS_SHOW] = $productOwnership;
        }
        return $this->connection->performGet($endpoint, $qs);
    }

    /**
     * Get a Resource
     *
     * @param $id Resource's identifier
     * @param null $fields Object's fields to retrieve
     * @param null $endpoint Endpoint
     * @param null $queryString Extra parameters to send as query string
     * @return BaseResponse The network response
     * @throws Exception
     */
    protected function _get($id, $fields=null, $endpoint=null, $queryString=null )
    {
        if ($this->validateId((string)$id)) {
            $fields = (is_array($fields)) ? implode(',', $fields) : null;
            $endpoint .= '/' . $id;

            $qs = array (
                INPUT_FIELDS => $fields,
            );
            if ($queryString) {
                $qs = array_merge($qs, $queryString);
            }

            return $this->connection->performGet($endpoint, $qs);
        }
    }

    /**
     * Create a resource
     *
     * @param AbstractModel $obj Object to save
     * @param null $fields Object's fields to retrieve
     * @param null $endpoint Endpoint
     * @param null $data Data to send IF we dont use AbstractModel object (like create folder)
     * @return BaseResponse The network response
     */
    protected function _create(AbstractModel $obj=null, $fields=null, $endpoint=null, $data=null)
    {
        $fields = (is_array($fields)) ? implode(',', $fields) : null;
        $qs = array (
            INPUT_FIELDS => $fields,
        );

        $data = (is_null($data)) ? $obj->modelToJson($obj) : $data;
        return $this->connection->performPost($endpoint, $qs, $data);
    }

    /**
     * Update a resource
     *
     * @param AbstractModel $obj Object to update
     * @param null $fields
     * @param null $endpoint
     * @param null $data
     * @return BaseResponse
     */
    protected function _update(AbstractModel $obj, $fields=null, $endpoint=null, $data=null)
    {
        $endpoint .= '/' . $obj->getId();
        $fields = (is_array($fields)) ? implode(',', $fields) : null;
        $qs = array (
            INPUT_FIELDS => $fields,
        );
        $data = (is_null($data)) ? $obj->modelToJson($obj) : $data;
        return $this->connection->performPut($endpoint, $qs, $data);
    }

    /**
     * It validates if id is a 12-byte string convertible to decimal
     * If is not It throws an Exception
     *
     * @param String $id
     * @return bool
     * @throws Exception
     */
    private function validateId($id)
    {
        if (strlen($id) == 24) {
            try {
                $this->hexToBin($id);
            } catch (Exception $e) {
                throw new Exception("Invalid ID: $id");
            }
        } else {
            throw new Exception('The id must be a 12-byte unique identifier.');
        }
        return true;
    }

    /**
     * Pings to Server to check if credentials are valid.
     *
     * @return BaseResponse
     */
    protected function _ping()
    {
        return $this->connection->performGet('', array());
    }

    /**
     * hex2bin backwards compatibility only works for PHP > 5.4
     * @param $hexstr
     * @return string
     */
    private function hexToBin($hexstr)
    {
        $n = strlen($hexstr);
        $sbin="";
        $i=0;
        while($i<$n)
        {
            $a =substr($hexstr,$i,2);
            $c = pack("H*",$a);
            if ($i==0){$sbin=$c;}
            else {$sbin.=$c;}
            $i+=2;
        }
        return $sbin;
    }
}
