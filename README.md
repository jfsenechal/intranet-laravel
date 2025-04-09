structure inspiration 

https://aureuserp.com

**Dans composer.json** 

```bash
composer require "wikimedia/composer-merge-plugin"
```

```json
{ "extra": {
        "merge-plugin": {
            "include": [
                "plugins/*/*/composer.json"
            ]
        }
    }
}  
```
**Ajout dans /bootstrap**

add file plugins.php

```php
return [
    AcMarche\Support\SupportPlugin::class,
    AcMarche\Security\SecurityPlugin::class,
];
```

**Ajout dans /bootstrap/providers**

```php
return [
    //....
    \AcMarche\Support\Providers\SupportServiceProvider::class,
    AcMarche\Security\SecurityServiceProvider::class,
];
```

**Dans app/Providers/Filament/AdminPanelProvider**
```php
  $panel->plugins([PluginManager::make()])     
```
