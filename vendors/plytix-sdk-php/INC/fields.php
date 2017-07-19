<?php

# Retailers API v0.1 models
define('MODEL_ACCOUNTS', 'brands');
define('MODEL_ACCOUNT' , 'brand');
define('MODEL_FOLDERS' , 'folders');
define('MODEL_FOLDER'  , 'folder');
define('MODEL_PRODUCTS', 'products');
define('MODEL_PRODUCT' , 'product');
define('MODEL_SITES'   , 'sites');
define('MODEL_SITE'    , 'site');

# Retailers API v0.1 response fields
define('RESPONSE_METADATA', 'meta');
define('RESPONSE_SUCCESS' , 'success');
define('RESPONSE_MESSAGE' , 'message');

# Retailers API v0.1 pagination fields
define('RESPONSE_METADATA_PAGE'        , 'page');
define('RESPONSE_METADATA_TOTAL'       , 'total');
define('RESPONSE_METADATA_TOTAL_PAGES' , 'total_pages');

# Retailers API v0.1 input fields
define('INPUT_FIELDS'       , 'fields');
define('INPUT_SORT'         , 'sort');
define('INPUT_PAGE_LENGTH'  , 'page_length');

# Retailers API v0.1 product
define('INPUT_PRODUCTS_SHOW', 'show');

# Retailers API v0.1 product search fields
define('INPUT_PRODUCTS_SEARCH_FOLDERS_LIST'     , 'folders_list');
define('INPUT_PRODUCTS_SEARCH_GROUP_BY'         , 'group_by');
define('INPUT_PRODUCTS_SEARCH_NAME'             , 'name');
define('INPUT_PRODUCTS_SEARCH_NAME_LIST'        , 'name_list');
define('INPUT_PRODUCTS_SEARCH_OPERATOR'         , 'operator');
define('INPUT_PRODUCTS_SEARCH_PRODUCTS_LIST'    , 'product_list');
define('INPUT_PRODUCTS_SEARCH_SKU_LIST'         , 'sku_list');
define('INPUT_PRODUCTS_SEARCH_EAN_LIST'         , 'ean_list');
define('INPUT_PRODUCTS_SEARCH_JAN_LIST'         , 'jan_list');
define('INPUT_PRODUCTS_SEARCH_UPC_LIST'         , 'upc_list');
define('INPUT_PRODUCTS_SEARCH_GTIN_LIST'        , 'gtin_list');
define('INPUT_PRODUCTS_SEARCH_IDENTIFIER_LIST'  , 'identifier_list');
define('INPUT_PRODUCTS_SIZES'                   , 'sizes');


# Retailers API v0.1 product search constants
define('PRODUCTS_SEARCH_GROUP_BY_DISABLED', 'NONE');
define('PRODUCTS_SEARCH_GROUP_BY_BRAND'   , 'BRAND');

# Retailers API v0.1 bank fields
define('INPUT_BANK_SEARCH_ACCOUNT'          , 'brand_id');
define('INPUT_BANK_SEARCH_FOLDER'           , 'folder_id');
define('INPUT_BANK_SEARCH_NAME'             , 'name');
define('INPUT_BANK_SEARCH_NAMELIST'         , 'name_list');
define('INPUT_BANK_SEARCH_PRODUCT'          , 'product_id');
define('INPUT_BANK_SEARCH_SKULIST'          , 'sku_list');
define('INPUT_BANK_SEARCH_SKU'              , 'sku');
define('INPUT_BANK_SEARCH_EANLIST'          , 'ean_list');
define('INPUT_BANK_SEARCH_EAN'              , 'ean');
define('INPUT_BANK_SEARCH_JANLIST'          , 'jan_list');
define('INPUT_BANK_SEARCH_JAN'              , 'jan');
define('INPUT_BANK_SEARCH_UPCLIST'          , 'upc_list');
define('INPUT_BANK_SEARCH_UPC'              , 'upc');
define('INPUT_BANK_SEARCH_GTINLIST'         , 'gtin_list');
define('INPUT_BANK_SEARCH_GTIN'             , 'gtin');
define('INPUT_BANK_SEARCH_IDENTIFIERLIST'   , 'identifier_list');
define('INPUT_BANK_SEARCH_IDENTIFIER'       , 'identifier');
define('INPUT_BANK_SEARCH_OPERATOR'         , 'operator');

define('INPUT_BANK_ADD_DESTINATION', 'dest');
define('INPUT_BANK_ADD_FOLDERS'    , 'folders_list');
define('INPUT_BANK_ADD_PRODUCTS'   , 'products_list');

#Product Ownership "Enum"
define ('PRODUCT_OWNERSHIP_ALL'  , 'ALL');
define ('PRODUCT_OWNERSHIP_OWN'  , 'OWN');
define ('PRODUCT_OWNERSHIP_THIRD', 'THIRD');

# Retailers API v0.1 site fields
define ('INPUT_SITES_SEARCH_NAME'    , 'name');
define ('INPUT_SITES_SEARCH_PROTOCOL', 'protocol');
define ('INPUT_SITES_SEARCH_URL'     , 'url');