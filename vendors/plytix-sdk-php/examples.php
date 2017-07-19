<?php
require_once dirname(__FILE__) . '/PHP/PlytixRetailersClient.php';

$client = new PlytixRetailersClient('api-key','api-pwd');


/**
 * List sites
 */
//$listSites = $client->sites()->listSites()->toJson();
//var_dump($listSites);
//die;

/**
 * Test Site 123456
 */
//var_dump($client->sites()->get('123456'));

/**
 * Creation of a site
 */
//$large = new PictureSizeModel(500,500);
//$medium = new PictureSizeModel(300,300);
//$small = new PictureSizeModel(200,200);
//$newSite = array(
//    'name' => 'test_api_site_6',
//    'url' => 'my_site_6',
//    'protocol' => 'https',
//    'timezone' => 'Europe/Madrid',
//    'large_size' => $large,
//    'medium_size' => $medium,
//    'small_size' => $small
//);
//$siteModel = new SiteModel($newSite);
//$result = $client->sites()->create($siteModel);
//var_dump($result);die;

/**
 * Update site 123456
 *
 **/
//$siteToUpdate = $client->sites()->get('123456');
//$siteToUpdate->setName('Aoo_3');
//$siteToUpdate->setUrl('urSSsssupdated3');
//$siteToUpdate->getPictureSizes()->getLarge()->setHeight(40);
//$siteToUpdate->getPictureSizes()->getLarge()->setWidth(3);
//$result = $client->sites()->update($siteToUpdate);
//var_dump($result);die;

/**
 * Get product by ID: 123456
 **/
//$product_info = $client->products()->get('123456');
//var_dump($product_info->getFolder());
//var_dump($product_info->getThumb());die;


/**
* Search a product by name
**/
//$search = array(
//    'name' => 'bellota'
//);
//$result = $client->products()->search();
//var_dump($result->getTotal());
//var_dump($result->getResults());die;
//$result = $client->products()->searchAndGroupByBrand();

/**
 * Get list of folders
 */
//$folders = $client->folders()->get();
//var_dump($folders);die;

/**
 * Get Folder By ID
 */
//$folder = $client->folders()->get('123456');
//var_dump($folder);die;

/**
 * Create Folder / retrieves folderModel created
 */
//$newFolder = $client->folders()->create('test_root_5');
//var_dump($newFolder);die;

/**
 * Create folder in subfolder
 */
//$newFolder = $client->folders()->create('test_subfolder', '123456');
//var_dump($newFolder);die;

/**
 * Search bank by sku list
 */
//$search = array(
//    'sku_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by sku
 */
//$search = array(
//    'sku' => '123456'
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by name list
 */
//$search = array(
//    'name_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by ean list
 */
//$search = array(
//    'ean_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by ean
 */
//$search = array(
//    'ean' => '123456'
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by jan list
 */
//$search = array(
//    'jan_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by jan
 */
//$search = array(
//    'jan' => '123456'
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by upc list
 */
//$search = array(
//    'upc_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by upc
 */
//$search = array(
//    'upc' => '123456'
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by gtin list
 */
//$search = array(
//    'gtin_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by gtin
 */
//$search = array(
//    'gtin' => '123456'
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by identifier list
 */
//$search = array(
//    'identifier_list' => array('123456')
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 * Search bank by identifier
 */
//$search = array(
//    'identifier' => '123456'
//);
//$bank = $client->bank()->search($search);
//var_dump($bank);
//die;

/**
 *  Add list of products to your account
 */
//$destinationFolder = $client->folders()->get('123456');
//$search = array(
//    'brand_id' => '123456',
//    'page_length' => 4
//);
//$bankOfProducts = $client->bank()->search($search);
//$listOfProductsId = array();
//foreach ($bankOfProducts->getResults() as $product) {
//    $listOfProductsId[] = $product->getId();
//}

//$result = $client->bank()->addTo($destinationFolder, null, $listOfProductsId);
//var_dump($result);die;

/**
 * Add folder to destinationFolder
 */
//$destinationFolder = $client->folders()->get('123456');
//$folderList = array('123456');
//var_dump($client->bank()->addTo($destinationFolder, $folderList, null));

/**
 * Get list of pictures
 */
//$products = array('123456');
//$pictures = $client->products()->pictures($products, "http://www.<your-url>.com");
//
//foreach ($pictures->getResults() as $picture) {
//    var_dump($picture->getId());
//    var_dump($picture->getPictures()[0]->getLarge());
//}
//die;
