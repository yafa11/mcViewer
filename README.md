# mcViewer
A GUI/interface for finding and viewing the contents of keys on one or more memcache servers.

Installation
------------
### Install with composer
[Install composer](https://getcomposer.org/download/)

#### As a standalone aplication:

From the command line
    
        composer require yafa11/mc-viewer: @stable


#### As a library to an existing project:

In your projects composer.json file add:
    
    

        {
            "require": {
                "yafa11/mci-viewer": "@stable"
            }
        }



The packages adheres to the [SemVer](http://semver.org/) specification, and there will be full backward compatibility
between minor versions.

### Create a config.ini file
Copy /src/config/config.ini.example to /src/config/config.ini and modify it to match your cache server setup.


Usage
-----
### Stand alone usage:
From /src/public run php -S 127.0.0.1:8888
In a browser navigate to http://127.0.0.1:8888

### As part of another project
The view template of mcViewer is completely separate from the logic used to query and gather the cache keys and values. 
This means that usage of the included html scripts is completely optional. If you wish to resuse the html code, copy 
the /src/public/mcViewer folder into your projects public folder.  Then point your browser at 
\<your domain\>/mcViwer/mcViewer.html

    
