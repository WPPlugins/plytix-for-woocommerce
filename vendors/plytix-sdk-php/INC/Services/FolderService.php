<?php
require_once dirname(__FILE__) . '/../Models/FolderModel.php';
require_once dirname(__FILE__) . '/../Models/ResponseModel.php';

/**
 * Folder Service Endpoint
 * Client to consume the folders you manage in your account.
 *
 * Class FolderService
 */
class FolderService extends BaseService {

    const BASE_ENDPOINT          = 'folders';
    const BASE_ENDPOINT_SINGULAR = 'folder';

    /**
     * @param null $id Identifier of the folder to retrieve. If it is not defined, the method returns the root's folder.
     * @param string $show Filter the folder by the products ownership ALL|OWN|THIRD
     * @param null $fields Fields to retrieve.
     * @param null $pageLength The number of results by page.
     * @param null $page Result's page.
     * @param null $sort Result's order.
     * @return FolderModel Folder instance.
     */
    public function get($id=null, $show=PRODUCT_OWNERSHIP_ALL, $fields=null, $pageLength=null, $page=null, $sort=null)
    {
        $endpoint = self::BASE_ENDPOINT;

        if (is_null($id)) {
            $queryResponse = parent::_list($page, $pageLength, $fields, $endpoint, $sort, $show);
        } else {
            $qs = array (
                INPUT_PRODUCTS_SHOW    => $show,
                RESPONSE_METADATA_PAGE => $page,
                INPUT_SORT             => $sort,
                INPUT_PAGE_LENGTH      => $pageLength
            );
            $queryResponse = parent::_get($id, $fields, $endpoint, $qs);
        }

        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT_SINGULAR);
        return new FolderModel($response);
    }

    /**
     * @param $name :Name of the folder to add to your account.
     * @param null $parentId The identifier of the parent folder.
     * @param null $fields
     * @return FolderModel Folder instance of the folder has been created.
     * @throws Exception When Folder Exists, throw exception saying it.
     */
    public function create($name, $parentId=null, $fields=null)
    {
        $endpoint = self::BASE_ENDPOINT;
        if ($parentId) {
            $endpoint .= '/' . $parentId;
        }

        $data = json_encode(
            array(
                'name' => $name
            )
        );

        try {
            $queryResponse = parent::_create(null, $fields, $endpoint, $data);
        } catch (Exception $e) {
            throw new Exception ('Folder Already created');
        }
        $response = $queryResponse->getDataObjects(self::BASE_ENDPOINT_SINGULAR);
        return new FolderModel($response);
    }

}
