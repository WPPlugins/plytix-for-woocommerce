# Plytix Retailers SDK for PHP #

The Plytix Retailers SDK for PHP is a library designed to simplify the access to the Plytix Retailers API to applications developed in PHP. The library gives you an easy way to manage your authentication and to consume the Retailers API services. Thanks to the Plytix Retailers SDK for PHP, you can quickly integrate our platform in your site's back end.

You will find the all documentation and examples of how to use it at [the Plytix for developers page](https://plytix.com/developers/retailers_api/sdk/php/index.html).

## Plytix Retailers SDK for PHP is easy to use ##

```
#!php

    require_once dirname(__FILE__) . '/PHP/PlytixRetailersClient.php';
    $client = new PlytixRetailersClient('api-key','api-pwd');
```


Once you have the client, you can start to consume the Plytix Retailers API's services immediately. For example, you get a list of all sites you manage with:
   
```
#!php

    $listSites = $client->sites()->listSites()
    foreach $listSites as $site{
        echo $site->getName()
    }
```


Only a few lines are needed to get a list of the sites you manage at Plytix. Quick and easy. You can find more examples in the "examples.php" file


## Links ##
* [Plytix website](https://plytix.com)
* [Documentation](https://plytix.com/developers/retailers_api/sdk/php/index.html)
* [Development version](https://bitbucket.org/plytixdevs/plytix-sdk-php)