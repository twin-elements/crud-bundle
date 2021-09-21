Install
```
composer require twin-elements/crud-bundle
```
    
in bundles.php add
```
TwinElements\CrudBundle\TwinElementsCrudBundle::class => ['dev' => true],
```

How it use
```
php bin/console make:make_new_crud EntityName
```
