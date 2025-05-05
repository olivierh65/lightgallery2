/**
 * @file
 * Initializes lightGallery.
 */

let lightgalleryInstances = {};

(function (Drupal, once, lightGallery) {

  'use strict';

  Drupal.behaviors.lightgalleryInit = {
    attach: function (context, settings) {
      if (settings.lightgallery === undefined) {
        return;
      }

      once('lightgallery-init', '.lightgallery-init[id]', context).forEach(function (element) {
        const id = element.getAttribute('id');
        const config = settings.lightgallery[id];

        if (config === undefined || config.settings === undefined) {
          return;
        }

        // Covert plugin names to variables.
        if (config.settings.plugins instanceof Array) {
          const plugins = config.settings.plugins;

          config.settings.plugins = [];
          for (const index in plugins) {
            if (window[plugins[index]] !== undefined) {
              config.settings.plugins.push(window[plugins[index]]);
            }
          }
        }

        // Find the items and container element.
        const items = element.querySelector('.lightgallery__items');
        const container = element.querySelector('.lightgallery__inline-container');

        if (items) {
          element = items;

          if (container) {
            config.settings.container = container;
          }
        }

        // Initialize lightGallery.
        const instance = lightGallery(element, config.settings);

        if (config.inline) {
          setTimeout(() => {
            instance.openGallery();
          }, 200);
        }

        // Store the instance in a global variable so other JavaScript can use it.
        lightgalleryInstances[id] = instance;
      });
    }
  };

})(Drupal, once, lightGallery);
