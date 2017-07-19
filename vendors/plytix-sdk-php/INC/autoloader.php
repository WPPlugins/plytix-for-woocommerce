<?php
/**
 * Loading Fields Constants Map
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fields.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

/**
 * Loading Unirest Vendor
 */
require_once dirname(__FILE__) . '/../vendors/unirest-php/src/Unirest.php';

/**
 * Defining autoload rules
 */
//Define the paths to the directories holding class files
/**$paths = array(
    'INC/',
    'INC/Models/',
    'INC/Services/',
    'PHP/',
    'vendors/unirest-php/src/',
    'INC/Models/',
    'INC/Services/'
);

//Add the paths to the class directories to the include path.
set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));
//Add the file extensions to the SPL.
spl_autoload_extensions(".php");
//Register the default autoloader implementation in the php engine.
spl_autoload_register();**/





