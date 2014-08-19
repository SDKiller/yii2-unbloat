Post-update cleanup for Yii2-Composer project
===========================================================

Have you suffered from tons of not nesessary for you in production files (like `docs`, `tests`, etc.) installed by Composer with almost every package?
Unfortunately Composer has no 'ignore' section in `composer.json` (unlike Bower in `bower.json`) to exclude some files and folders.

This is a `post-update-cmd` composer script to cleanup Yii2 project of not nesessary files.



Installation
------------

```
composer.phar require --prefer-dist zyx/yii2-unbloat "*"
```

TBD


Usage
-----

TBD

Add the following to `composer.json` file in your project root:


```
    ...
    "scripts": {
        ...
        "post-update-cmd": [
            "zyx\\unbloat\\Helper::postCmd"
        ]
        ...
    },
    "extra": {
        ...
        "ignore": {
            "twbs/bootstrap": "type::bower",
            "swiftmailer/swiftmailer": [
                "/.*",
                "phpunit.xml.dist",
                "doc",
                "tests"
            ]
        }
        ...
    }
    ...
```


