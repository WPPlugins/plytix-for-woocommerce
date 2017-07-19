<?php
require_once dirname(__FILE__) . '/AbstractModel.php';

/**
 * Model For Responses
 * This Class will handle all kind of (Model)responses.
 *
 * Class ResponseModel
 */
class ResponseModel extends AbstractModel {

    protected $total;
    protected $totalPages;
    protected $page;
    protected $code;
    protected $arrayModels;

    /**
     * @param $total int Total of results
     * @param $totalPages int Total number of pages
     * @param $page int Current Page
     * @param $code int Response Code
     * @param $arrayModels ArrayObject List of Model Objects
     */
    function __construct($total, $totalPages, $page, $code, $arrayModels)
    {
        $this->setTotal($total);
        $this->setTotalPages($totalPages);
        $this->setPage($page);
        $this->setCode($code);
        $this->setArrayModels($arrayModels);
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return ArrayObject
     */
    public function getResults()
    {
        return $this->arrayModels;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $totalPages
     */
    private function setTotalPages($totalPages)
    {
        $this->totalPages = $totalPages;
    }

    /**
     * @param int $total
     */
    private function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @param int $page
     */
    private function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @param int $code
     */
    private function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param ArrayObject $arrayModels
     */
    private function setArrayModels($arrayModels)
    {
        $this->arrayModels = $arrayModels;
    }

    /**
     * Return $obj Model as Json
     *
     * @param AbstractModel $obj
     * @return mixed|string|void
     */
    function modelToJson(AbstractModel $obj)
    {
        $response = array();
        foreach ($obj as $result) {
            $response[] = $this->objectToArray($result);
        }
        return json_encode($response);
    }


    /**
     * Return results as Json
     *
     * @return mixed|string|void
     */
    function toJson()
    {
        $response = array();
        foreach ($this->getResults() as $result) {
            $response[] = $this->objectToArray($result);
        }
        return json_encode($response);
    }

}
