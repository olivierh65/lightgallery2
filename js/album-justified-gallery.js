(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.lightgalleryAlbums = {
    attach: function (context, settings) {
      console.log("lightgalleryAlbums behavior attached");
      $(once("lg-album", ".album-cover", context)).on("click", function (e) {
        e.preventDefault();
        var albumId = $(this).data("album-id");
        var $album = $("#" + albumId);

        // Récupère les settings spécifiques à cet album
        var albumSettings = drupalSettings.lightgallery?.albums?.[albumId] || {};

        if ($album.length && typeof window.lightGallery === "function") {
          if (!$album.data("lightGallery")) {
            // Initialisation native
            const instance = window.lightGallery($album[0], {
              selector: "a",
              plugins: drupalSettings.settings?.lightgallery?.["plugins"] || [],
              ...albumSettings
              // autres options...
            });
            $album.data("lightGallery", instance);
          }
          // Ouvre la galerie
          $album.data("lightGallery").openGallery();
        } else {
          console.error("LightGallery is not loaded!");
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings, window.once);
