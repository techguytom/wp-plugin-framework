# WordPress Plugin Framework

A modern, object oriented framework for developing WordPress plugins

### Example Directory Structure
```
|-- <project root>
|   |-- content
|   |   |-- plugins
|   |   |   |-- your-plugin-name
|   |   |   |   |-- resources
|   |   |   |   |   |-- views <twig templates in here>
|   |   |   |   |   |-- css
|   |   |   |   |   |-- js
|   |   |   |   |
|   |   |   |   |-- src
|   |   |   |   |   |-- YourPluginNamespace
|   |   |   |   |   |   |-- Controller
|   |   |   |   |   |   |   |-- ApiController.php
|   |   |   |   |   |   |   |-- HomeController.php
|   |   |   |   |
|   |   |   |   |-- vendor <composer dependencies>
|   |   |   |   |
|   |   |   |   |-- composer.json
|   |   |   |   |
|   |   |   |   |-- your-plugin-name.php
```

### Installation with composer
```json
{
    "require": {
        "nerdery/wp-plugin-framework": "dev-master"
    }
}
```

### Your plugin file

```php

/**
 * File your-plugin-name.php
 */

/*
 * Create a new plugin factory
 * 
 * Configure the plugin by passing an array of settings
 * to the Factory.
 * 
 * - templatePath, this should be the path to where you will
 *   put your Twig templates. Recommend: "resources/scripts/"
 *
 * - prefix, any database tables that this plugin creates will
 *   add this prefix to the table names, the plugin will also
 *   prepend the WordPress prefix to this.
 *
 * - slug, This is an arbitrary slug that this plugin will use
 *   to generate custom hooks and events. This slug should be
 *   unique.
 */
$factory = new \Nerdery\Plugin\Factory\Factory(array(
    'templatePath' => dirname(__FILE__) . '/resources/views',
    'prefix' => 'plugins_database_prefix_',
    'slug' => 'this_plugins_slug_name',
));

// Create a new plugin
$plugin = $factory->make();

/*
 * Register controllers
 *
 * All of these controllers are loaded immediately every time this plugin
 * is loaded. Keep this in mind when considering performance. However, as
 * the controllers are the "meat and potatoes" of the plugin, it only makes
 * sense that they be loaded immediately so that they can hook into the
 * necessary WordPress event calls they need to respond to.
 */
$plugin['controller.home'] = new \YourPluginNamespace\Controller\HomeController($plugin);
$plugin['controller.api'] = new \YourPluginNamespace\Controller\ApiController($plugin);

// Define a route...
$route = new \Nerdery\Plugin\Router\Route(
   '^api/user/register.json?',
   'controller.api:registerUserAction'
);
$plugin->registerRoute($route);

// Run the plugin!
$plugin->run();
```
