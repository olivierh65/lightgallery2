<?php

namespace Drupal\lightgallery\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides reusable logic for LightGallery settings forms.
 */
trait LightGallerySettingsTrait {

    /**
   * Returns an associative array mapping LightGallery plugin keys to their library names.
   *
   * This method provides a list of available LightGallery plugins and their corresponding
   * library identifiers, which can be used to load or reference specific plugins within
   * the LightGallery integration.
   *
   * @return array
   *   An associative array where the keys are plugin identifiers (e.g., 'autoplay', 'zoom')
   *   and the values are the corresponding library names (e.g., 'lgAutoplay', 'lgZoom').
   */

  protected function getPluginsLibrary(): array {
    return [
      'autoplay' => 'lgAutoplay',
      'comment' => 'lgComment',
      'fullscreen' => 'lgFullscreen',
      'hash' => 'lgHash',
      'medium-zoom' => 'lgMediumZoom',
      'pager' => 'lgPager',
      'relative-caption' => 'lgRelativeCaption',
      'rotate' => 'lgRotate',
      'share' => 'lgShare',
      'thumbnail' => 'lgThumbnail',
      'video' => 'lgVideo',
      'vimeo-thumbnail' => 'lgVimeoThumbnail',
      'zoom' => 'lgZoom',
    ];
  }

  /**
   * Returns the LightGallery plugin definitions.
   *
   * @return array
   *   An array of plugin definitions including labels and form fields.
   */
  protected function getLightGalleryPluginDefinitions(): array {

    /**
     * Provides LightGallery settings and plugin configuration options.
     *
     * The $settings array defines the configuration structure for the LightGallery
     * core settings and its available plugins. Each configuration option includes
     * metadata such as label, description, form element type, and additional
     * parameters for rendering configuration forms.
     *
     * Structure:
     * - 'core': Core LightGallery settings, including license key, appearance,
     *   controls, animation, and behavior options.
     * - 'plugins': Plugin-specific settings, each with its own label, description,
     *   and configurable parameters. Supported plugins include:
     *   - 'zoom': Pinch to zoom, double-tap, and zoom controls.
     *   - 'thumbnails': Thumbnail generation, alignment, and navigation.
     *   - 'video': Video playback, autoplay, and YouTube player options.
     *   - 'hash': Hash navigation for slides.
     *   - 'autoplay': Automatic slide transitions and controls.
     *   - 'rotate': Image rotation and flipping.
     *   - 'pager': Pager navigation.
     *   - 'fullscreen': Fullscreen gallery mode.
     *
     * Each parameter within core and plugins specifies:
     *   - '#type': The form element type (e.g., 'textfield', 'checkbox', 'select', 'number', 'string').
     *   - '#title': The label for the form element.
     *   - '#description': (Optional) Additional information about the setting.
     *   - '#options': (For 'select' types) Available options for selection.
     *
     * This structure is intended for use in dynamic form generation and
     * configuration management within the LightGallery Drupal module.
     */
    $settings = [
      'core' => [
        'label' => $this->t('Core'),
        'open' => FALSE,
        'description' => $this->t('Core LightGallery settings.'),
        'activable' => FALSE,
        'params' => [
          'license_key' => [
            '#type' => 'textfield',
            '#title' => $this->t('License key'),
            '#required' => TRUE,
          ],
          'addClass' => [
            '#type' => 'textfield',
            '#title' => $this->t('Additional class'),
            '#description' => $this->t('Add a custom class to the gallery container.'),
            '#access' =>FALSE,
          ],
          'allowMediaOverlap' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Allow media overlap'),
            '#description' => $this->t('If true, toolbar, captions and thumbnails will not overlap with media element<br>
This will not effect thumbnails if animateThumb is false<br>
Also, toggle thumbnails button is not displayed if allowMediaOverlap is false'),
            '#access' => TRUE,
          ],
          'appendCounterTo' => [
            '#type' => 'select',
            '#title' => $this->t('Where the counter should be appended'),
            '#options' => [
              '.lg-toolbar' => $this->t('Toolbar'),
              '.lg-sub-html' => $this->t('Sub HTML'),
              '.lg-item' => $this->t('Item'),
            ],
            '#access' => TRUE,
          ],
          'appendSubHtmlTo' => [
            '#type' => 'select',
            '#title' => $this->t('Where the sub HTML should be appended'),
            '#options' => [
              '.lg-item' => $this->t('Item'),
              '.lg-sub-html' => $this->t('Sub HTML'),
            ],
            '#access' => TRUE,
          ],
          'backdropDuration' => [
            '#type' => 'number',
            '#title' => $this->t('Backdrop duration'),
            '#description' => $this->t('Duration of the backdrop animation in milliseconds.'),
            '#access' => FALSE,
            /* Don't use this option!!!
            when enabled, unable to reopen the gallery after closing it
            */
          ],
          'closable' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Closable'),
            '#description' => $this->t('Allow closing the gallery by clicking on the backdrop.'),
            '#access' => TRUE,
          ],
          'closeOnTap' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Close on tap'),
            '#description' => $this->t('Allow closing the gallery by tapping on the screen.'),
            '#access' => TRUE,
          ],
          'controls' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Controls'),
            '#description' => $this->t('Show next/prev controls.'),
          ],
          'counter' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Counter'),
            '#description' => $this->t('Show the current slide number and total slides.'),
          ],
          'defaultCaptionHeight' => [
            '#type' => 'number',
            '#title' => $this->t('Default caption height'),
            '#description' => $this->t('Height of the caption area when no caption is provided.'),
            '#access' => FALSE,
          ],
          'download' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Download'),
            '#description' => $this->t('Show the download button.'),
          ],
          'easing' => [
            '#type' => 'select',
            '#title' => $this->t('Easing'),
            '#description' => $this->t('Slide animation CSS easing property.'),
            '#options' => [
              'linear' => $this->t('Linear'),
              'ease' => $this->t('Ease'),
              'ease-in' => $this->t('Ease In'),
              'ease-out' => $this->t('Ease Out'),
              'ease-in-out' => $this->t('Ease In Out'),
            ],
            '#access' => TRUE,
          ],
          'enableDrag' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable drag'),
            '#description' => $this->t('Allow dragging to navigate through slides.'),
            '#access' => TRUE,
          ],
          'enableSwipe' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable swipe'),
            '#description' => $this->t('Allow swiping to navigate through slides.'),
            "#access" => TRUE,
          ],
          'escapeKey' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Escape key'),
            '#description' => $this->t('Close the gallery when the escape key is pressed.'),
            '#access' => TRUE,
          ],
          'height' => [
            '#type' => 'string',
            '#title' => $this->t('Height'),
            '#description' => $this->t('Set a fixed height for the gallery (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'hideBarsDelay' => [
            '#type' => 'number',
            '#title' => $this->t('Hide bars delay'),
            '#description' => $this->t('Time in milliseconds to hide the control bars after user interaction.'),
            '#access' => FALSE,
          ],
          'hideControlOnEnd' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Hide control on end'),
            '#description' => $this->t('Hide the next/prev controls when the gallery reaches the last slide.'),
            '#access' => FALSE,
          ],
          'hideScrollbars' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Hide scrollbars'),
            '#description' => $this->t('Hide the scrollbars when the gallery is open.'),
            '#access' => FALSE,
          ],
          'iframeHeight' => [
            '#type' => 'string',
            '#title' => $this->t('Iframe height'),
            '#description' => $this->t('Set a fixed height for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'iframeMaxHeight' => [
            '#type' => 'string',
            '#title' => $this->t('Iframe max height'),
            '#description' => $this->t('Set a maximum height for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'iframeWidth' => [
            '#type' => 'string',
            '#title' => $this->t('Iframe width'),
            '#description' => $this->t('Set a fixed width for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'iframeMaxWidth' => [
            '#type' => 'string',
            '#title' => $this->t('Iframe max width'),
            '#description' => $this->t('Set a maximum width for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'index' => [
            '#type' => 'number',
            '#title' => $this->t('Initial index'),
            '#description' => $this->t('The index of the first slide to display (0-based).'),
            '#default_value' => 0,
            '#access' => FALSE,
          ],
          'isMobile' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Is mobile'),
            '#description' => $this->t('Enable mobile-specific features.'),
            '#access' => TRUE,
          ],
          'keyPress' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Key press'),
            '#description' => $this->t('Enable keyboard navigation.'),
            '#access' => TRUE,
          ],
          'loadYoutubePoster' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Load YouTube poster'),
            '#description' => $this->t('Automatically load poster image for YouTube videos.'),
            '#access' => TRUE,
          ],
          'loop' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Loop'),
            '#description' => $this->t('Enable looping through slides.'),
            '#access' => TRUE,
          ],
          'mode' => [
            '#type' => 'select',
            '#title' => $this->t('Mode'),
            '#description' => $this->t('Slide transition mode.'),
            '#options' => [
              'lg-slide' => $this->t('Slide'),
              'lg-fade' => $this->t('Fade'),
              'lg-zoom-in-out' => $this->t('Zoom In/Out'),
            ],
            '#access' => TRUE,
          ],
          'mousewheel' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Mouse wheel'),
            '#description' => $this->t('Enable navigation using the mouse wheel.'),
            '#access' => TRUE,
          ],
          'nextHtml' => [
            '#type' => 'textfield',
            '#title' => $this->t('Next HTML'),
            '#description' => $this->t('Custom HTML for the next button.'),
            '#access' => TRUE,
          ],
          'prevHtml' => [
            '#type' => 'textfield',
            '#title' => $this->t('Previous HTML'),
            '#description' => $this->t('Custom HTML for the previous button.'),
            '#access' => TRUE,
          ],
          'numberOfSlideItemsInDom' => [
            '#type' => 'number',
            '#title' => $this->t('Number of slide items in DOM'),
            '#description' => $this->t('Number of slide items to keep in the DOM for performance.'),
            '#access' => TRUE,
          ],
          'preload' => [
            '#type' => 'select',
            '#title' => $this->t('Preload'),
            '#description' => $this->t('Number of slides to preload.'),
            '#options' => [
              '1' => $this->t('1 slide'),
              '2' => $this->t('2 slides'),
              '3' => $this->t('3 slides'),
              '4' => $this->t('4 slides'),
              '5' => $this->t('5 slides'),
              'all' => $this->t('All slides'),
            ],
          ],
          'resetScrollPosition' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Reset scroll position'),
            '#description' => $this->t('Reset the scroll position when the gallery is opened.'),
            '#access' => TRUE,
          ],
          'showBarsAfter' => [
            '#type' => 'number',
            '#title' => $this->t('Show bars after'),
            '#description' => $this->t('Time in milliseconds to show the control bars after user interaction.'),
            '#access' => FALSE,
          ],
          'showCloseIcon' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Show close icon'),
            '#description' => $this->t('Show the close icon in the gallery.'),
            '#access' => TRUE,
          ],
          'showMaximizeIcon' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Show maximize icon'),
            '#description' => $this->t('Show the maximize icon in the gallery.'),
            '#access' => FALSE,
             /* Don't use this option!!!
            Problem with some drupal elements that stays on the screen
            when enabled, unable to reopen the gallery after closing it
            */
          ],
          'slideDelay' => [
            '#type' => 'number',
            '#title' => $this->t('Slide delay'),
            '#description' => $this->t('Delay in milliseconds between slide transitions.'),
            '#access' => FALSE,
          ],
          'slideEndAnimatoin' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Slide end animation'),
            '#description' => $this->t('Enable slide end animation.'),
            '#access' => FALSE,
          ],
          'speed' => [
            '#type' => 'number',
            '#title' => $this->t('Speed'),
            '#description' => $this->t('Duration of the slide transition in milliseconds.'),
            '#access' => FALSE,
          ],
          'swipeThreshold' => [
            '#type' => 'number',
            '#title' => $this->t('Swipe threshold'),
            '#description' => $this->t('Threshold for swipe navigation in pixels.'),
            '#access' => TRUE,
          ],
          'swipeToClose' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Swipe to close'),
            '#description' => $this->t('Allow swiping down to close the gallery.'),
            '#access' => TRUE,
          ],
          'videoMaxSize' => [
            '#type' => 'string',
            '#title' => $this->t('Video max size'),
            '#description' => $this->t('Maximum size for video elements in pixels ("1280-720").'),
            '#access' => FALSE,
          ],
          'width' => [
            '#type' => 'string',
            '#title' => $this->t('Width'),
            '#description' => $this->t('Set a fixed width for the gallery (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'zoomFromOrigin' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Zoom from origin'),
            '#description' => $this->t('Enable zooming from the origin of the media element.'),
            '#access' => TRUE,
          ],
        ],
      ],
      'plugins' => [
        'zoom' => [
          'label' => $this->t('Zoom'),
          'open' => FALSE,
          'description' => $this->t('Enable pinch to zoom, double-tap, etc.'),
          'activable' => TRUE,
          'params' => [
            'actualSize' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Show actual size button'),
            ],
            'enableZoomAfter' => [
              '#type' => 'number',
              '#title' => $this->t('Enable zoom after'),
              '#description' => $this->t('Time in milliseconds to enable zoom after the gallery is opened.'),
            ],
            'infiniteZoom' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Infinite zoom'),
              '#description' => $this->t('Enable infinite zooming.'),
            ],
            'scale' => [
              '#type' => 'number',
              '#title' => $this->t('Scale'),
              '#description' => $this->t('Initial zoom scale.'),
            ],
          ],
        ],
        'thumbnail' => [
          'label' => $this->t('Thumbnail'),
          'open' => FALSE,
          'description' => $this->t('Generate thumbnails, animated support, etc.'),
          'activable' => TRUE,
          'params' => [
            'alignThumbnails' => [
              '#type' => 'select',
              '#title' => $this->t('Align thumbnails'),
              '#options' => [
                'left' => $this->t('Left'),
                'center' => $this->t('Center'),
                'right' => $this->t('Right'),
              ],
              '#access' => TRUE,
            ],
            'animateThumb' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Animate thumbnails'),
              '#description' => $this->t('Enable animated thumbnails.'),
              '#access' => TRUE,
            ],
            'appendThumbnailsTo' => [
              '#type' => 'select',
              '#title' => $this->t('Where to append thumbnails'),
              '#options' => [
                '.lg-outer' => $this->t('Outer container'),
                '.lg-item' => $this->t('Item container'),
              ],
              '#access' => FALSE,
            ],
            'currentPagerPosition' => [
              '#type' => 'select',
              '#title' => $this->t('Current pager position'),
              '#options' => [
                'left' => $this->t('Left'),
                'center' => $this->t('Center'),
                'right' => $this->t('Right'),
              ],
              '#access' => TRUE,
            ],
            'enableThumbDrag' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Enable thumbnail drag'),
              '#description' => $this->t('Allow dragging thumbnails to navigate.'),
              '#access' => TRUE,
            ],
            'enableThumbSwipe' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Enable thumbnail swipe'),
              '#description' => $this->t('Allow swiping thumbnails to navigate.'),
              '#access' => TRUE,
            ],
            'loadYoutubeThumbnail' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Load YouTube thumbnail'),
              '#description' => $this->t('Load the YouTube video thumbnail for thumbnails.'),
              '#access' => TRUE,
            ],
            'thumbHeight' => [
              '#type' => 'number',
              '#title' => $this->t('Thumbnail height'),
              '#description' => $this->t('Height of the thumbnails in pixels.'),
              '#access' => FALSE,
            ],
            'thumbMargin' => [
              '#type' => 'number',
              '#title' => $this->t('Thumbnail margin'),
              '#description' => $this->t('Margin between thumbnails in pixels.'),
              '#access' => FALSE,
            ],
            'thumbWidth' => [
              '#type' => 'number',
              '#title' => $this->t('Thumbnail width'),
              '#description' => $this->t('Width of the thumbnails in pixels.'),
              '#access' => FALSE,
            ],
            'thumbnailSwipeThreshold' => [
              '#type' => 'number',
              '#title' => $this->t('Thumbnail swipe threshold'),
              '#description' => $this->t('Threshold for thumbnail swipe navigation in pixels.'),
              '#access' => FALSE,
            ],
            'toggleThumb' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Toggle thumbnails'),
              '#description' => $this->t('Enable toggling thumbnails visibility.'),
              '#access' => TRUE,
            ],
            'youTubeThumbSize' => [
              '#type' => 'number',
              '#title' => $this->t('YouTube thumbnail size'),
              '#description' => $this->t('Size of the YouTube thumbnail (default is 1).'),
              '#access' => FALSE,
            ],
          ],
        ],
        'video' => [
          'label' => $this->t('Video'),
          'open' => FALSE,
          'description' => $this->t('Play videos, supports autoplay, controls, etc.'),
          'activable' => TRUE,
          'params' => [
            'autoplayFirstVideo' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Autoplay first video'),
              '#description' => $this->t('Automatically play the first video when the gallery is opened.'),
            ],
            'autoplayVideoOnSlide' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Autoplay video on slide change'),
              '#description' => $this->t('Automatically play the video when the slide changes.'),
            ],
            'gotoNextSlideOnVideoEnd' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Go to next video on end'),
              '#description' => $this->t('Automatically go to the next video when the current one ends.'),
            ],
            'youTubePlayer' => [
              'label' => $this->t('Video youTubePlayer'),
              'open' => FALSE,
              'description' => $this->t('Play videos, supports autoplay, controls, etc.'),
              'params' => [
                'modestbranding' => [
                  '#type' => 'checkbox',
                  '#title' => $this->t('Branding'),
                  '#description' => $this->t('Show YouTube branding.'),
                ],
                'rel' => [
                  '#type' => 'checkbox',
                  '#title' => $this->t('Related videos'),
                  '#description' => $this->t('Show related videos at the end.'),
                ],
                'showinfo' => [
                  '#type' => 'checkbox',
                  '#title' => $this->t('Show info'),
                  '#description' => $this->t('Show video information (title, uploader, etc.).'),
                ],
                'controls' => [
                  '#type' => 'checkbox',
                  '#title' => $this->t('Show controls'),
                  '#description' => $this->t('Show video controls.'),
                ],
              ],
            ],
          ],
        ],
        'hash' => [
          'label' => $this->t('Hash'),
          'open' => FALSE,
          'description' => $this->t('Enable hash navigation for the gallery.'),
          'activable' => TRUE,
          'params' => [
            'customSlideName' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Custom slide name'),
              '#description' => $this->t('Use custom slide names in the URL hash.'),
            ],
          ],
        ],
        'autoplay' => [
          'label' => $this->t('AutoPlay'),
          'open' => FALSE,
          'description' => $this->t('Enable automatic slide transitions.'),
          'activable' => TRUE,
          'params' => [
            'appendAutoplayControlsTo' => [
              '#type' => 'select',
              '#title' => $this->t('Where to append autoplay controls'),
              '#options' => [
                '.lg-toolbar' => $this->t('Toolbar'),
                '.lg-item' => $this->t('Item'),
              ],
            ],
            'autoplayControls' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Autoplay controls'),
              '#description' => $this->t('Show autoplay controls in the gallery.'),
            ],
            'forceSlideShowAutoplay' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Force slideshow autoplay'),
              '#description' => $this->t('Force autoplay even if the gallery is not in focus.'),
            ],
            'progessBar' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Progress bar'),
              '#description' => $this->t('Show a progress bar for autoplay.'),
            ],
            'slideShowAutoplay' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Slide show autoplay'),
              '#description' => $this->t('Enable automatic slide transitions.'),
            ],
            'slideShowDelay' => [
              '#type' => 'number',
              '#title' => $this->t('Slide show delay'),
              '#description' => $this->t('Delay between slide transitions in milliseconds.'),
            ],
          ],
        ],
        'rotate' => [
          'label' => $this->t('Rotate'),
          'open' => FALSE,
          'description' => $this->t('Enable rotation of images.'),
          'activable' => TRUE,
          'params' => [
            'flipHorizontal' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Flip horizontal'),
              '#description' => $this->t('Enable flipping images horizontally.'),
            ],
            'flipVertical' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Flip vertical'),
              '#description' => $this->t('Enable flipping images vertically.'),
            ],
            'rotateLeft' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Rotate left'),
              '#description' => $this->t('Enable rotating images to the left.'),
            ],
            'rotateRight' => [
              '#type' => 'checkbox',
              '#title' => $this->t('Rotate right'),
              '#description' => $this->t('Enable rotating images to the right.'),
            ],
            'rotateStep' => [
              '#type' => 'number',
              '#title' => $this->t('Rotate step'),
              '#description' => $this->t('Step in degrees for rotating images.'),
            ],
          ],
        ],
        'pager' => [
          'label' => $this->t('Pager'),
          'open' => FALSE,
          'description' => $this->t('Enable a pager for navigating through slides.'),
          'activable' => TRUE,
        ],
        'fullscreen' => [
          'label' => $this->t('Fullscreen'),
          'open' => FALSE,
          'description' => $this->t('Enable fullscreen mode for the gallery.'),
          'activable' => TRUE,
        ],
      ]
    ];

    return $settings;
  }

  /**
   * Builds the parameters form for a plugin.
   *
   * @param array $plugin_data
   *   The plugin data containing parameters and their definitions.
   * @param array $plugin_config
   *   The current configuration for the plugin.
   * @param string $label
   *   The label for the settings section.
   * @param string $prefix
   *   The prefix for the configuration keys.
   * @param bool $wrap
   *   Whether to wrap the form in a details container.
   *
   * @return array
   *   The form elements for the plugin parameters.
   */
  private function buildParamsForm(
    array $plugin_data,
    array $plugin_config,
    string $label,
    string $prefix = '',
    bool $wrap = TRUE
  ): array {
    $form = [];

    foreach ($plugin_data as $key => $element) {
      // Cas 1 : simple elemement with #type defined
      if (isset($element['#type'])) {
        $config_key = trim("$prefix.$key", '.');
        $element['#default_value'] = $plugin_config[$key] ?? '';
        $element['#config_target'] = "lightgallery.settings:$config_key";
        $form[$key] = $element;
      }

      // Cas 2 : sub-group contening "params"
      elseif (is_array($element) && isset($element['params'])) {
        $config_key = trim("$prefix.$key.params", '.');

        // Details conteneur for this group
        // If the element has a label, use it; otherwise, use the key as label.
        $wrapper = [
          '#type' => 'details',
          '#title' => $element['label'] ?? ucfirst($key),
          '#description' => $element['description'] ?? '',
          '#open' => $element['open'] ?? FALSE,
          '#tree' => TRUE,
        ];

        // add sub elements recursively
        $wrapper['params'] = $this->buildParamsForm(
          $element['params'],
          $plugin_config[$key]['params'] ?? [],
          $element['label'] ?? ucfirst($key),
          $config_key,
          FALSE
        );

        $form[$key] = $wrapper;
      }
    }

    // first level : wrap in a global details container
    if ($wrap) {
      return [
        '#type' => 'details',
        '#title' => $this->t('@label settings', ['@label' => $label]),
        '#open' => TRUE,
        '#tree' => TRUE,
      ] + $form;
    }

    return $form;
  }




  private function buildPluginForm(array $plugin_definitions, array $plugin_config, string $type, array $parents): array {
    $form = [];

    foreach ($plugin_definitions as $plugin_key => $plugin_data) {
      $config = $plugin_config[$plugin_key] ?? [];

if ($type !== 'core') {
        // If the plugin is not the core, we need to add a details container
        // to group the plugin settings.
      $form[$plugin_key] = [
        '#type' => 'details',
        '#title' => $plugin_data['label'] ?? ucfirst($plugin_key),
        '#description' => $plugin_data['description'] ?? '',
        '#open' => $plugin_data['open'] ?? FALSE,
        '#tree' => TRUE,
        '#parents' => array_merge($parents, [$plugin_key]),
      ];
    }
      // if this plugin is activable, add a checkbox
      // If the plugin has an 'activable' key, it means it can be enabled/disabled.
      if (!empty($plugin_data['activable'])) {
        $form[$plugin_key]['enabled'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#default_value' => $config['enabled'] ?? FALSE,
          '#config_target' => "lightgallery.settings:plugins.$plugin_key.enabled",
          # '#name' => "plugins[{$plugin_key}][enabled]",
          # DON'T use #name here, it will be handled by the form system,
          # based on the #parents.
          # If we use #name, values will not be saved correctly.
          '#parents' => array_merge($parents, [$plugin_key, 'enabled']),
        ];
      }

      // If the plugin has parameters, build the form for them.
      // The parameters are defined in the 'params' key of the plugin data.
      if (!empty($plugin_data['params'])) {
        $params_wrapper = $this->buildParamsForm(
          $plugin_data['params'],
          $config['params'] ?? [],
          $plugin_data['label'] ?? ucfirst($plugin_key),
          "$type.$plugin_key.params"
        );

        // If the plugin is activable, add states to the params wrapper.
        // This will make the parameters visible only when the plugin is enabled.
        // This is useful to hide parameters of plugins that are not enabled.
        if (!empty($plugin_data['activable'])) {
          $params_wrapper['#states'] =  $this->buildStatesFromParents(array_merge($parents, [$plugin_key, 'enabled']));
        }

        $form[$plugin_key]['params'] = $params_wrapper;
      }
    }

    return $form;
  }

  protected function buildCoreSettingsForm(array $plugin_definitions, $plugin_config, array $parents): array {
    $form = [];
    // Check if the plugin_config is a Config object or an array
    // If it's a Config object, we get the "core" configuration
    // If it's an array, we get the "core" configuration directly
    // Otherwise, we initialize an empty array
    if ($plugin_config instanceof \Drupal\Core\Config\Config) {
      $plugin_config = $plugin_config->get('core') ?? [];
    } elseif (is_array($plugin_config)) {
      $plugin_config = $plugin_config['core'] ?? [];
    } else {
      $plugin_config = [];
    }

    // Build the form for the "core" block with basic parameters
    // We use the buildPluginForm method to create the form for the core settings
    $form = $this->buildPluginForm(
      ['core' => $plugin_definitions['core']],
      ['core' => $plugin_config],
      '',
      $parents
    )['core'];

    return $form;
  }

  protected function buildPluginSettingsForm(array $plugin_definitions, $plugin_config, array $parents): array {
    $form = [];
    if ($plugin_config instanceof \Drupal\Core\Config\Config) {
      $plugin_config = $plugin_config->get('plugins') ?? [];
    } elseif (is_array($plugin_config)) {
      $plugin_config = $plugin_config['plugins'] ?? [];
    } else {
      $plugin_config = [];
    }
    // Build the form for the "plugins" block with all plugins
    // We use the buildPluginForm method to create the form for the plugins settings
    // The 'plugins' key in $plugin_definitions contains all the plugin definitions
    // The 'plugins' key in $plugin_config contains the current configuration for each plugin
    // The $parents array is used to build the form structure
    $form = $this->buildPluginForm(
      $plugin_definitions['plugins'],
      $plugin_config,
      'plugins',
      $parents
    );
    return $form;
  }

  /**
   * Builds a Drupal #states array for form elements based on parent keys.
   *
   * Constructs a flat input name from an array of parent keys and returns
   * a #states condition array that sets the 'visible' state when the corresponding
   * input is checked.
   *
   * @param array $parents
   *   An array of parent keys representing the hierarchy of form elements.
   * @param string $expected_value
   *   (optional) The expected value for the input to trigger the state. Defaults to '1'.
   *
   * @return array
   *   A #states array for use in Drupal form API, controlling visibility based on input state.
   */
  protected function buildStatesFromParents(array $parents, string $expected_value = '1'): array {
    $flat_name = '';
    foreach ($parents as $depth => $part) {
      $flat_name .= ($depth === 0 ? '' : '[') . $part . ($depth > 0 ? ']' : '');
    }

    return [
      'visible' => [
        ":input[name='{$flat_name}']" => ['checked' => TRUE],
      ],
    ];
  }
}
