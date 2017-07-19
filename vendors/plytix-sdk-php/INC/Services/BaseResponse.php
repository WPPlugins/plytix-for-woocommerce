<?php
/**
 * Class BaseResponse
 * Take Unirest\Response to BaseResponse
 * We use predefined constants to access to the response Object
 */
class BaseResponse {

    /**
     * @var \Unirest\Response
     */
    protected $response;

    /**
     * @param Unirest\Response $raw_response
     * @throws Exception
     */
    function __construct(Unirest\Response $raw_response)
    {
        $this->response = $raw_response;
        $this->checkStatus();
    }

    /**
     * @return mixed
     */
    function getDataObjects($endpoint)
    {
        return $this->response->body->$endpoint;
    }

    /**
     * @return int
     */
    function getTotal()
    {
        return $this->response->body->{RESPONSE_METADATA}->{RESPONSE_METADATA_TOTAL};
    }

    /**
     * @return int
     */
    function getTotalPages()
    {
        return $this->response->body->{RESPONSE_METADATA}->{RESPONSE_METADATA_TOTAL_PAGES};
    }

    /**
     * @return int
     */
    function getPage()
    {
        return $this->response->body->{RESPONSE_METADATA}->{RESPONSE_METADATA_PAGE};
    }

    /**
     * @return int
     */
    function getCode()
    {
        return $this->response->code;
    }

    /**
     * @return bool
     */
    function getSuccess()
    {
        if (isset($this->response->body->meta->success) && ($this->response->body->meta->success == true)) {
            return $this->response->body->meta->success;
        } else {
            return false;
        }
    }

    /**
     * @return stdClass
     */
    function getMeta()
    {
        return $this->response->body->{RESPONSE_METADATA};
    }

    /**
     * Checks if there were any problem in the connection
     * It will throw an Exception
     * @throws Exception
     */
    private function checkStatus()
    {
        switch ($this->getCode()) {
            case 400 :
                throw new Exception('400 Bad Request');
                break;
            case 401 :
                throw new Exception('401 Unauthorized.: Account not found');
                break;
            case 404 :
                throw new Exception('404 Resource not found');
                break;
            case 500 :
                throw new Exception('500 Bad Response Error');
                break;
        }
    }
}
