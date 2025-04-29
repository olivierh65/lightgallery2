lightGallery
============

Integrates image and media fields with lightGallery.


## Requirements

* [lightGallery](https://github.com/sachinchoolur/lightGallery)


## Installation

Start by adding asset-packagist and the required installer configuration to your `composer.json` file:

```json
{
    "extra": {
        "installer-paths": {
            "web/libraries/{$name}": [
                "type:drupal-library",
                "type:bower-asset",
                "type:npm-asset"
            ]
        },
        "installer-types": [
            "bower-asset",
            "npm-asset"
        ]
    },
    "repositories": {
        "asset-packagist": {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    }
}
```

Next download the latest release and its dependencies using `composer require drupal/lightgallery`.

Now enable the module as described in the [the module installation guide](https://www.drupal.org/docs/extending-drupal/installing-modules)
and configure your lightGallery license key at `/admin/config/user-interface/lightgallery`.


## Usage

### Field formatters

The module provides a lightGallery thumbnail formatter for both image and media fields.
Just enable and configure the lightGallery formatter on the desired field and you're done.


### Theme hook

Use the "lightgallery" theme hook to render a custom lightGallery.

```php
$build = [
  '#theme' => 'lightgallery',
  // Nested array of lightGallery items, each item had 2 keys:
  // - attributes: an array of attributes, e.g. "data-src" for the image to show.
  // - content: the render array of content to show outside the gallery.
  '#items' => [
    [
      'attributes' => [
        'data-src' => '',
      ],
      'content' => [
        '#theme' => '...'
      ],
    ]
  ],
  // Set to TRUE to render an inline gallery.
  '#inline' => TRUE,
  // Any lightGallery settings, see https://www.lightgalleryjs.com/docs/settings
  '#settings' => [
    'plugins' => ['thumbnail'],
    'speed' => 500,
  ],
  // Set to FALSE to disable automatic initialisation
  '#init' => FALSE
];
```
