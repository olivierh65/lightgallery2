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

  public static function getPluginsLibrary(): array {
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
  public static function getLightGalleryPluginDefinitions(): array {

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
        'label' => t('Core'),
        'open' => FALSE,
        'description' => t('Core LightGallery settings.'),
        'activable' => FALSE,
        'params' => [
          'licenseKey' => [
            '#type' => 'textfield',
            '#title' => t('License key'),
            '#required' => TRUE,
          ],
          'addClass' => [
            '#type' => 'textfield',
            '#title' => t('Additional class'),
            '#description' => t('Add a custom class to the gallery container.'),
            '#access' => FALSE,
          ],
          'allowMediaOverlap' => [
            '#type' => 'checkbox',
            '#title' => t('Allow media overlap'),
            '#description' => t('If true, toolbar, captions and thumbnails will not overlap with media element<br>
This will not effect thumbnails if animateThumb is false<br>
Also, toggle thumbnails button is not displayed if allowMediaOverlap is false'),
            '#access' => TRUE,
          ],
          'appendCounterTo' => [
            '#type' => 'select',
            '#title' => t('Where the counter should be appended'),
            '#options' => [
              '.lg-toolbar' => t('Toolbar'),
              '.lg-sub-html' => t('Sub HTML'),
              '.lg-item' => t('Item'),
            ],
            '#access' => TRUE,
          ],
          'appendSubHtmlTo' => [
            '#type' => 'select',
            '#title' => t('Where the sub HTML should be appended'),
            '#options' => [
              '.lg-item' => t('Item'),
              '.lg-sub-html' => t('Sub HTML'),
            ],
            '#access' => TRUE,
          ],
          'backdropDuration' => [
            '#type' => 'number',
            '#title' => t('Backdrop duration'),
            '#description' => t('Duration of the backdrop animation in milliseconds.'),
            '#access' => FALSE,
            /* Don't use this option!!!
            when enabled, unable to reopen the gallery after closing it
            */
          ],
          'closable' => [
            '#type' => 'checkbox',
            '#title' => t('Closable'),
            '#description' => t('Allow closing the gallery by clicking on the backdrop.'),
            '#access' => TRUE,
          ],
          'closeOnTap' => [
            '#type' => 'checkbox',
            '#title' => t('Close on tap'),
            '#description' => t('Allow closing the gallery by tapping on the screen.'),
            '#access' => TRUE,
          ],
          'controls' => [
            '#type' => 'checkbox',
            '#title' => t('Controls'),
            '#description' => t('Show next/prev controls.'),
          ],
          'counter' => [
            '#type' => 'checkbox',
            '#title' => t('Counter'),
            '#description' => t('Show the current slide number and total slides.'),
          ],
          'defaultCaptionHeight' => [
            '#type' => 'number',
            '#title' => t('Default caption height'),
            '#description' => t('Height of the caption area when no caption is provided.'),
            '#access' => FALSE,
          ],
          'download' => [
            '#type' => 'checkbox',
            '#title' => t('Download'),
            '#description' => t('Show the download button.'),
          ],
          'easing' => [
            '#type' => 'select',
            '#title' => t('Easing'),
            '#description' => t('Slide animation CSS easing property.'),
            '#options' => [
              'linear' => t('Linear'),
              'ease' => t('Ease'),
              'ease-in' => t('Ease In'),
              'ease-out' => t('Ease Out'),
              'ease-in-out' => t('Ease In Out'),
            ],
            '#access' => TRUE,
          ],
          'enableDrag' => [
            '#type' => 'checkbox',
            '#title' => t('Enable drag'),
            '#description' => t('Allow dragging to navigate through slides.'),
            '#access' => TRUE,
          ],
          'enableSwipe' => [
            '#type' => 'checkbox',
            '#title' => t('Enable swipe'),
            '#description' => t('Allow swiping to navigate through slides.'),
            "#access" => TRUE,
          ],
          'escapeKey' => [
            '#type' => 'checkbox',
            '#title' => t('Escape key'),
            '#description' => t('Close the gallery when the escape key is pressed.'),
            '#access' => TRUE,
          ],
          'height' => [
            '#type' => 'string',
            '#title' => t('Height'),
            '#description' => t('Set a fixed height for the gallery (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'hideBarsDelay' => [
            '#type' => 'number',
            '#title' => t('Hide bars delay'),
            '#description' => t('Time in milliseconds to hide the control bars after user interaction.'),
            '#access' => FALSE,
          ],
          'hideControlOnEnd' => [
            '#type' => 'checkbox',
            '#title' => t('Hide control on end'),
            '#description' => t('Hide the next/prev controls when the gallery reaches the last slide.'),
            '#access' => FALSE,
          ],
          'hideScrollbars' => [
            '#type' => 'checkbox',
            '#title' => t('Hide scrollbars'),
            '#description' => t('Hide the scrollbars when the gallery is open.'),
            '#access' => FALSE,
          ],
          'iframeHeight' => [
            '#type' => 'string',
            '#title' => t('Iframe height'),
            '#description' => t('Set a fixed height for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'iframeMaxHeight' => [
            '#type' => 'string',
            '#title' => t('Iframe max height'),
            '#description' => t('Set a maximum height for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'iframeWidth' => [
            '#type' => 'string',
            '#title' => t('Iframe width'),
            '#description' => t('Set a fixed width for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'iframeMaxWidth' => [
            '#type' => 'string',
            '#title' => t('Iframe max width'),
            '#description' => t('Set a maximum width for iframes (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'index' => [
            '#type' => 'number',
            '#title' => t('Initial index'),
            '#description' => t('The index of the first slide to display (0-based).'),
            '#default_value' => 0,
            '#access' => FALSE,
          ],
          'isMobile' => [
            '#type' => 'checkbox',
            '#title' => t('Is mobile'),
            '#description' => t('Enable mobile-specific features.'),
            '#access' => TRUE,
          ],
          'keyPress' => [
            '#type' => 'checkbox',
            '#title' => t('Key press'),
            '#description' => t('Enable keyboard navigation.'),
            '#access' => TRUE,
          ],
          'loadYoutubePoster' => [
            '#type' => 'checkbox',
            '#title' => t('Load YouTube poster'),
            '#description' => t('Automatically load poster image for YouTube videos.'),
            '#access' => TRUE,
          ],
          'loop' => [
            '#type' => 'checkbox',
            '#title' => t('Loop'),
            '#description' => t('Enable looping through slides.'),
            '#access' => TRUE,
          ],
          'mode' => [
            '#type' => 'select',
            '#title' => t('Mode'),
            '#description' => t('Slide transition mode.'),
            '#options' => [
              'lg-slide' => t('Slide'),
              'lg-fade' => t('Fade'),
              'lg-zoom-in' => t('Zoom-In'),
              'lg-zoom-in-big' => t('Zoom-In-Big'),
              'lg-zoom-out' => t('Zoom-Out'),
              'lg-zoom-out-big' => t('Zoom-Out-Big'),
              'lg-zoom-out-in' => t('Zoom-Out-In'),
              'lg-zoom-in-out' => t('Zoom-In-Out'),
              'lg-soft-zoom' => t('Soft-Zoom'),
              'lg-scale-up' => t('Scale-Up'),
              'lg-slide-circular' => t('Slide-Circular'),
              'lg-slide-circular-vertical' => t('Slide-Circular-Vertical'),
              'lg-slide-vertical' => t('Slide-Vertical'),
              'lg-slide-vertical-growth' => t('Slide-Vertical-Growth'),
              'lg-slide-skew-only' => t('Slide-Skew-Only'),
              'lg-slide-skew-only-rev' => t('Slide-Skew-Only-Rev'),
              'lg-slide-skew-only-y' => t('Slide-Skew-Only-Y'),
              'lg-slide-skew-only-y-rev' => t('Slide-Skew-Only-Y-Rev'),
              'lg-slide-skew' => t('Slide-Skew'),
              'lg-slide-skew-rev' => t('Slide-Skew-Rev'),
              'lg-slide-skew-cross' => t('Slide-Skew-Cross'),
              'lg-slide-skew-cross-rev' => t('Slide-Skew-Cross-Rev'),
              'lg-slide-skew-ver' => t('Slide-Skew-Ver'),
              'lg-slide-skew-ver-rev' => t('Slide-Skew-Ver-Rev'),
              'lg-slide-skew-ver-cross' => t('Slide-Skew-Ver-Cross'),
              'lg-slide-skew-ver-cross-rev' => t('Slide-Skew-Ver-Cross-Rev'),
              'lg-lollipop' => t('Lollipop'),
              'lg-lollipop-rev' => t('Lollipop-Rev'),
              'lg-rotate' => t('Rotate'),
              'lg-rotate-rev' => t('Rotate-Rev'),
              'lg-tube' => t('Tube'),
            ],
            '#access' => TRUE,
          ],
          'mousewheel' => [
            '#type' => 'checkbox',
            '#title' => t('Mouse wheel'),
            '#description' => t('Enable navigation using the mouse wheel.'),
            '#access' => TRUE,
          ],
          'nextHtml' => [
            '#type' => 'textfield',
            '#title' => t('Next HTML'),
            '#description' => t('Custom HTML for the next button.'),
            '#access' => TRUE,
          ],
          'prevHtml' => [
            '#type' => 'textfield',
            '#title' => t('Previous HTML'),
            '#description' => t('Custom HTML for the previous button.'),
            '#access' => TRUE,
          ],
          'numberOfSlideItemsInDom' => [
            '#type' => 'number',
            '#title' => t('Number of slide items in DOM'),
            '#description' => t('Number of slide items to keep in the DOM for performance.'),
            '#access' => TRUE,
          ],
          'preload' => [
            '#type' => 'select',
            '#title' => t('Preload'),
            '#description' => t('Number of slides to preload.'),
            '#options' => [
              '1' => t('1 slide'),
              '2' => t('2 slides'),
              '3' => t('3 slides'),
              '4' => t('4 slides'),
              '5' => t('5 slides'),
              'all' => t('All slides'),
            ],
          ],
          'resetScrollPosition' => [
            '#type' => 'checkbox',
            '#title' => t('Reset scroll position'),
            '#description' => t('Reset the scroll position when the gallery is opened.'),
            '#access' => TRUE,
          ],
          'showBarsAfter' => [
            '#type' => 'number',
            '#title' => t('Show bars after'),
            '#description' => t('Time in milliseconds to show the control bars after user interaction.'),
            '#access' => FALSE,
          ],
          'showCloseIcon' => [
            '#type' => 'checkbox',
            '#title' => t('Show close icon'),
            '#description' => t('Show the close icon in the gallery.'),
            '#access' => TRUE,
          ],
          'showMaximizeIcon' => [
            '#type' => 'checkbox',
            '#title' => t('Show maximize icon'),
            '#description' => t('Show the maximize icon in the gallery.'),
            '#access' => FALSE,
            /* Don't use this option!!!
            Problem with some drupal elements that stays on the screen
            when enabled, unable to reopen the gallery after closing it
            */
          ],
          'slideDelay' => [
            '#type' => 'number',
            '#title' => t('Slide delay'),
            '#description' => t('Delay in milliseconds between slide transitions.'),
            '#access' => FALSE,
          ],
          'slideEndAnimatoin' => [
            '#type' => 'checkbox',
            '#title' => t('Slide end animation'),
            '#description' => t('Enable slide end animation.'),
            '#access' => FALSE,
          ],
          'speed' => [
            '#type' => 'number',
            '#title' => t('Speed'),
            '#description' => t('Duration of the slide transition in milliseconds.'),
            '#access' => FALSE,
          ],
          'swipeThreshold' => [
            '#type' => 'number',
            '#title' => t('Swipe threshold'),
            '#description' => t('Threshold for swipe navigation in pixels.'),
            '#access' => TRUE,
          ],
          'swipeToClose' => [
            '#type' => 'checkbox',
            '#title' => t('Swipe to close'),
            '#description' => t('Allow swiping down to close the gallery.'),
            '#access' => TRUE,
          ],
          'videoMaxSize' => [
            '#type' => 'string',
            '#title' => t('Video max size'),
            '#description' => t('Maximum size for video elements in pixels ("1280-720").'),
            '#access' => FALSE,
          ],
          'width' => [
            '#type' => 'string',
            '#title' => t('Width'),
            '#description' => t('Set a fixed width for the gallery (example: \'500px\', \'100%\').'),
            '#access' => FALSE,
          ],
          'zoomFromOrigin' => [
            '#type' => 'checkbox',
            '#title' => t('Zoom from origin'),
            '#description' => t('Enable zooming from the origin of the media element.'),
            '#access' => TRUE,
          ],
        ],
      ],
      'plugins' => [
        'zoom' => [
          'label' => t('Zoom'),
          'open' => FALSE,
          'description' => t('Enable pinch to zoom, double-tap, etc.'),
          'activable' => TRUE,
          'params' => [
            'actualSize' => [
              '#type' => 'checkbox',
              '#title' => t('Show actual size button'),
            ],
            'enableZoomAfter' => [
              '#type' => 'number',
              '#title' => t('Enable zoom after'),
              '#description' => t('Time in milliseconds to enable zoom after the gallery is opened.'),
            ],
            'infiniteZoom' => [
              '#type' => 'checkbox',
              '#title' => t('Infinite zoom'),
              '#description' => t('Enable infinite zooming.'),
            ],
            'scale' => [
              '#type' => 'number',
              '#title' => t('Scale'),
              '#description' => t('Initial zoom scale.'),
            ],
          ],
        ],
        'thumbnail' => [
          'label' => t('Thumbnail'),
          'open' => FALSE,
          'description' => t('Generate thumbnails, animated support, etc.'),
          'activable' => TRUE,
          'params' => [
            'alignThumbnails' => [
              '#type' => 'select',
              '#title' => t('Align thumbnails'),
              '#options' => [
                'left' => t('Left'),
                'center' => t('Center'),
                'right' => t('Right'),
              ],
              '#access' => TRUE,
            ],
            'animateThumb' => [
              '#type' => 'checkbox',
              '#title' => t('Animate thumbnails'),
              '#description' => t('Enable animated thumbnails.'),
              '#access' => TRUE,
            ],
            'appendThumbnailsTo' => [
              '#type' => 'select',
              '#title' => t('Where to append thumbnails'),
              '#options' => [
                '.lg-outer' => t('Outer container'),
                '.lg-item' => t('Item container'),
              ],
              '#access' => FALSE,
            ],
            'currentPagerPosition' => [
              '#type' => 'select',
              '#title' => t('Current pager position'),
              '#options' => [
                'left' => t('Left'),
                'center' => t('Center'),
                'right' => t('Right'),
              ],
              '#access' => TRUE,
            ],
            'enableThumbDrag' => [
              '#type' => 'checkbox',
              '#title' => t('Enable thumbnail drag'),
              '#description' => t('Allow dragging thumbnails to navigate.'),
              '#access' => TRUE,
            ],
            'enableThumbSwipe' => [
              '#type' => 'checkbox',
              '#title' => t('Enable thumbnail swipe'),
              '#description' => t('Allow swiping thumbnails to navigate.'),
              '#access' => TRUE,
            ],
            'loadYoutubeThumbnail' => [
              '#type' => 'checkbox',
              '#title' => t('Load YouTube thumbnail'),
              '#description' => t('Load the YouTube video thumbnail for thumbnails.'),
              '#access' => TRUE,
            ],
            'thumbHeight' => [
              '#type' => 'string',
              '#title' => t('Thumbnail height'),
              '#description' => t('Height of the thumbnails in pixels.'),
              '#access' => FALSE,
            ],
            'thumbMargin' => [
              '#type' => 'number',
              '#title' => t('Thumbnail margin'),
              '#description' => t('Margin between thumbnails in pixels.'),
              '#access' => FALSE,
            ],
            'thumbWidth' => [
              '#type' => 'number',
              '#title' => t('Thumbnail width'),
              '#description' => t('Width of the thumbnails in pixels.'),
              '#access' => FALSE,
            ],
            'thumbnailSwipeThreshold' => [
              '#type' => 'number',
              '#title' => t('Thumbnail swipe threshold'),
              '#description' => t('Threshold for thumbnail swipe navigation in pixels.'),
              '#access' => FALSE,
            ],
            'toggleThumb' => [
              '#type' => 'checkbox',
              '#title' => t('Toggle thumbnails'),
              '#description' => t('Enable toggling thumbnails visibility.'),
              '#access' => TRUE,
            ],
            'youTubeThumbSize' => [
              '#type' => 'number',
              '#title' => t('YouTube thumbnail size'),
              '#description' => t('Size of the YouTube thumbnail (default is 1).'),
              '#access' => FALSE,
            ],
          ],
        ],
        'video' => [
          'label' => t('Video'),
          'open' => FALSE,
          'description' => t('Play videos, supports autoplay, controls, etc.'),
          'activable' => TRUE,
          'params' => [
            'autoplayFirstVideo' => [
              '#type' => 'checkbox',
              '#title' => t('Autoplay first video'),
              '#description' => t('Automatically play the first video when the gallery is opened.'),
            ],
            'autoplayVideoOnSlide' => [
              '#type' => 'checkbox',
              '#title' => t('Autoplay video on slide change'),
              '#description' => t('Automatically play the video when the slide changes.'),
            ],
            'gotoNextSlideOnVideoEnd' => [
              '#type' => 'checkbox',
              '#title' => t('Go to next video on end'),
              '#description' => t('Automatically go to the next video when the current one ends.'),
            ],
            'youTubePlayer' => [
              'label' => t('Video youTubePlayer'),
              'open' => FALSE,
              'description' => t('Play videos, supports autoplay, controls, etc.'),
              'params' => [
                'modestbranding' => [
                  '#type' => 'checkbox',
                  '#title' => t('Branding'),
                  '#description' => t('Show YouTube branding.'),
                ],
                'rel' => [
                  '#type' => 'checkbox',
                  '#title' => t('Related videos'),
                  '#description' => t('Show related videos at the end.'),
                ],
                'showinfo' => [
                  '#type' => 'checkbox',
                  '#title' => t('Show info'),
                  '#description' => t('Show video information (title, uploader, etc.).'),
                ],
                'controls' => [
                  '#type' => 'checkbox',
                  '#title' => t('Show controls'),
                  '#description' => t('Show video controls.'),
                  '#access' => FALSE
                ],
              ],
            ],
          ],
        ],
        'hash' => [
          'label' => t('Hash'),
          'open' => FALSE,
          'description' => t('Enable hash navigation for the gallery.'),
          'activable' => TRUE,
          'params' => [
            'customSlideName' => [
              '#type' => 'checkbox',
              '#title' => t('Custom slide name'),
              '#description' => t('Use custom slide names in the URL hash.'),
            ],
          ],
        ],
        'autoplay' => [
          'label' => t('AutoPlay'),
          'open' => FALSE,
          'description' => t('Enable automatic slide transitions.'),
          'activable' => TRUE,
          'params' => [
            'appendAutoplayControlsTo' => [
              '#type' => 'select',
              '#title' => t('Where to append autoplay controls'),
              '#options' => [
                '.lg-toolbar' => t('Toolbar'),
                '.lg-item' => t('Item'),
              ],
            ],
            'autoplayControls' => [
              '#type' => 'checkbox',
              '#title' => t('Autoplay controls'),
              '#description' => t('Show autoplay controls in the gallery.'),
            ],
            'forceSlideShowAutoplay' => [
              '#type' => 'checkbox',
              '#title' => t('Force slideshow autoplay'),
              '#description' => t('Force autoplay even if the gallery is not in focus.'),
            ],
            'progessBar' => [
              '#type' => 'checkbox',
              '#title' => t('Progress bar'),
              '#description' => t('Show a progress bar for autoplay.'),
            ],
            'slideShowAutoplay' => [
              '#type' => 'checkbox',
              '#title' => t('Slide show autoplay'),
              '#description' => t('Enable automatic slide transitions.'),
            ],
            'slideShowDelay' => [
              '#type' => 'number',
              '#title' => t('Slide show delay'),
              '#description' => t('Delay between slide transitions in milliseconds.'),
            ],
          ],
        ],
        'rotate' => [
          'label' => t('Rotate'),
          'open' => FALSE,
          'description' => t('Enable rotation of images.'),
          'activable' => TRUE,
          'params' => [
            'flipHorizontal' => [
              '#type' => 'checkbox',
              '#title' => t('Flip horizontal'),
              '#description' => t('Enable flipping images horizontally.'),
            ],
            'flipVertical' => [
              '#type' => 'checkbox',
              '#title' => t('Flip vertical'),
              '#description' => t('Enable flipping images vertically.'),
            ],
            'rotateLeft' => [
              '#type' => 'checkbox',
              '#title' => t('Rotate left'),
              '#description' => t('Enable rotating images to the left.'),
            ],
            'rotateRight' => [
              '#type' => 'checkbox',
              '#title' => t('Rotate right'),
              '#description' => t('Enable rotating images to the right.'),
            ],
            'rotateStep' => [
              '#type' => 'number',
              '#title' => t('Rotate step'),
              '#description' => t('Step in degrees for rotating images.'),
            ],
          ],
        ],
        'pager' => [
          'label' => t('Pager'),
          'open' => FALSE,
          'description' => t('Enable a pager for navigating through slides.'),
          'activable' => TRUE,
        ],
        'fullscreen' => [
          'label' => t('Fullscreen'),
          'open' => FALSE,
          'description' => t('Enable fullscreen mode for the gallery.'),
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

      /**
   * Builds and returns the LightGallery settings array.
   *
   * This method compiles the configuration settings for the LightGallery plugin,
   * including core parameters and enabled plugin parameters. It ensures that only
   * accessible and non-empty settings are included. Plugin parameters are flattened
   * into the top-level settings array with underscore-separated keys. Additionally,
   * the method attaches the necessary JavaScript libraries for each enabled plugin.
   *
   * @return array
   *   The assembled settings array for LightGallery, including core settings,
   *   enabled plugins, their parameters, and attached libraries.
   */
  public static function getGeneralSettings(array $all_settings): array {

    // Add the core settings from the configuration.
    $core_settings_def = static::getLightGalleryPluginDefinitions()['core']['params'];
    $core_settings = $all_settings['lightgallery_settings']['core']['params'] ?? [];
    foreach ($core_settings as $key => $value) {
      if (isset($core_settings_def[$key]['#access']) && $core_settings_def[$key]['#access'] === FALSE) {
        // Skip settings that are not accessible.
        continue;
      }
      // Adds a non-empty value to the settings array.
      if (! empty($value)) {
        $settings[$key] = $value;
      }
    }


    // Add enabled plugins, their parameters and javascript libraries.
    $plugins_library = static::getPluginsLibrary();
    $plugins_settings_def = static::getLightGalleryPluginDefinitions()['plugins'];
    $plugins = [];
    $settings['plugins'] = [];
    $plugin_settings = $all_settings['lightgallery_settings']['plugins'] ?? [];
    foreach ($plugin_settings as $plugin_id => $plugin_config) {
      if (!empty($plugin_config['enabled'])) {
        $plugins[] = $plugin_id;
        $settings['plugins'][] = $plugins_library[$plugin_id];
        // Flatten nested plugin params into $settings at the top level.
        $params = $plugin_config['params'] ?? [];
        $iterator = new \RecursiveIteratorIterator(
          new \RecursiveArrayIterator($params),
          \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $key => $value) {
          // Build the full key path using underscores.
          $path = [];
          foreach (range(0, $iterator->getDepth()) as $depth) {
            $path[] = $iterator->getSubIterator($depth)->key();
          }
          $flat_key = implode('_', array_filter($path, 'strlen'));
          // Check access if defined.
          $def = $plugins_settings_def[$plugin_id]['params'];
          foreach ($path as $segment) {
            if (isset($def[$segment])) {
              $def = $def[$segment];
            }
          }
          if (isset($def['#access']) && $def['#access'] === FALSE) {
            continue;
          }
          if (!is_array($value) && !empty($value)) {
            $settings[$flat_key] = $value;
          }
        }
        $settings[$plugin_id] = true;
      }
    }
    return $settings;
  }

}
