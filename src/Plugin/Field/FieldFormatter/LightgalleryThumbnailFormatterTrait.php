<?php

namespace Drupal\lightgallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\lightgallery\Traits\LightGallerySettingsTrait;

/**
 * Trait for lightGallery thumbnail formatters.
 */
trait LightgalleryThumbnailFormatterTrait {

  use EntityReferenceLightgalleryFormatterTrait;
  use LightGallerySettingsTrait;

  /**
   * {@inheritdoc}
   */
  /**
   * Returns the default settings for the Lightgallery thumbnail formatter.
   *
   * This method retrieves configuration values from the 'lightgallery.settings'
   * configuration object and merges them with the parent class's default
   * settings. It sets up default values for inline display, image styles,
   * loading behavior, and custom settings. Additionally, it loads the core and
   * plugin settings for Lightgallery from configuration.
   *
   * @return array
   *   An associative array containing the default settings for the formatter.
   */
  public static function defaultSettings(): array {
    $config = \Drupal::config('lightgallery.settings');
    $settings = [
      'inline' => FALSE,
      'thumbnail_image_style' => NULL,
      'thumbnail_loading' => 'lazy',
      'gallery_image_style' => NULL,
      'custom_settings' => [],
      'video_attributes' => [
        'preload' => FALSE,
        'controls' => 'controls',
        'autoplay' => FALSE,
        'loop' => FALSE,
        'muted' => FALSE,
      ],
    ] + parent::defaultSettings();
    $settings['lightgallery_settings']['core'] = $config->get('core') ?? [];
    $settings['lightgallery_settings']['plugins'] = $config->get('plugins') ?? [];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = parent::settingsForm($form, $form_state);

    $config = \Drupal::config('lightgallery.settings');
    $enabled_plugins = $config->get('plugins') ?? [];
    $enabled_plugins = array_filter($enabled_plugins, function ($plugin) {
      return $plugin['enabled'] ?? FALSE;
    });

    /**
     * Merges global and entity-specific Lightgallery configuration settings.
     *
     * Retrieves global Lightgallery settings from configuration and merges them
     * with entity-specific settings for both core parameters and plugins.
     * Entity-specific settings take precedence over global settings.
     *
     * @var \Drupal\Core\Config\ImmutableConfig $global_config
     *   The global Lightgallery configuration object.
     * @var array $global_plugins
     *   The global plugins configuration array.
     * @var array $global_core
     *   The global core parameters configuration array.
     * @var array $entity_core
     *   The entity-specific core parameters configuration array.
     * @var array $entity_plugins
     *   The entity-specific plugins configuration array.
     * @var array $core_effective
     *   The merged core parameters, with entity settings overriding global ones.
     * @var array $plugins_effective
     *   The merged plugins configuration, with entity settings overriding global ones.
     * @var array $final_config
     *   The final merged configuration array to be used by Lightgallery.
     */
    $global_config = \Drupal::config('lightgallery.settings');
    $global_plugins = $global_config->get('plugins') ?? [];
    $global_core = $global_config->get('core.params') ?? [];

    $entity_core = $this->getSetting('lightgallery_settings')['core']['params'] ?? [];
    $entity_plugins = $this->getSetting('lightgallery_settings')['plugins'] ?? [];

    $core_effective = array_replace_recursive($global_core, $entity_core);
    $plugins_effective = array_replace_recursive($global_plugins, $entity_plugins);

    $final_config = [
      'core' => [
        'params' => $core_effective,
      ],
      'plugins' => $plugins_effective,
    ];


    /**
     * Builds the array of parent keys for accessing the field's settings edit form.
     *
     * @var array $parents
     *   An array containing the hierarchical keys to access the settings of the
     *   current field in the settings edit form. The field name is dynamically
     *   retrieved from the field definition.
     */
    $parents = ['fields', $this->fieldDefinition->getName(), 'settings_edit_form', 'settings'];
    $image_style_options = $this->getImageStyleOptions();

    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#open' => TRUE,
      '#parents' => $parents,
    ];

    $form['general']['inline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inline gallery'),
      '#description' => $this->t('Render the gallery inline. If checked, the thumbnails will only be visible if JavaScript is disabled.'),
      '#default_value' => (int) $this->getSetting('inline'),
    ];

    $default = $this->getSetting('gallery_image_style');

    $form['general']['gallery_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Gallery image style'),
      '#options' => $image_style_options,
      '#default_value' => isset($default, $image_style_options[$default]) ? $default : NULL,
      '#empty_value' => '',
      '#empty_option' => '- ' . $this->t('None (original image)') . ' -',
    ];

    $form['thumbnails'] = [
      '#type' => 'details',
      '#title' => $this->t('Thumbnails'),
      '#open' => TRUE,
      '#parents' => $parents,
      '#states' => [
        'visible' => [
          [
            ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][lightgallery_settings][plugins][thumbnail][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ];

    $default = $this->getSetting('thumbnail_image_style');

    $form['thumbnails']['thumbnail_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Thumbnail image style'),
      '#options' => $image_style_options,
      '#default_value' => isset($default, $image_style_options[$default]) ? $default : NULL,
      '#empty_value' => '',
      '#empty_option' => '- ' . $this->t('None (original image)') . ' -',
    ];

    $form['thumbnails']['thumbnail_loading'] = [
      '#type' => 'radios',
      '#title' => $this->t('Thumbnail loading attribute'),
      '#description' => $this->t('Select the loading attribute for images. <a href=":link">Learn more about the loading attribute for images.</a>', [
        ':link' => 'https://html.spec.whatwg.org/multipage/urls-and-fetching.html#lazy-loading-attributes',
      ]),
      '#options' => [
        'lazy' => $this->t('Lazy (<em>loading="lazy"</em>)'),
        'eager' => $this->t('Eager (<em>loading="eager"</em>)'),
      ],
      '#default_value' => $this->getSetting('thumbnail_loading'),
      '#required' => TRUE,
    ];

    $form['video'] = [
      '#type' => 'details',
      '#title' => $this->t('Video player'),
      '#description' => $this->t('Configure the video player settings.'),
      '#open' => TRUE,
      '#parents' => $parents,
      '#states' => [
        'visible' => [
          // Show only if the video plugin is enabled.
          [
            ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][lightgallery_settings][plugins][video][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ];
    $form['video']['video_attributes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Video attributes'),
      '#description' => $this->t('Select the attributes to add to the video element.'),
      '#options' => [
        'preload' => $this->t('Preload'),
        'controls' => $this->t('Controls'),
        'autoplay' => $this->t('Autoplay'),
        'loop' => $this->t('Loop'),
        'muted' => $this->t('Muted'),
      ],
      '#default_value' => $this->getSetting('video_attributes') ?? [],
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#parents' => $parents,
    ];

    $form['advanced']['custom_settings'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom settings'),
      '#description' => $this->t('A JSON object with <a href=":url" target="_blank">lightGallery settings</a>. Plugins can be specified as strings, e.g. <code>@plugins</code>.<br />Note that the module overrides some lightGallery defaults. If you define those settings here as well, the value specified here will be used.', [
        ':url' => 'https://www.lightgalleryjs.com/docs/settings',
        '@plugins' => "{ 'plugins': ['lgThumbnail'] }",
      ]),
      '#default_value' => json_encode($this->getSetting('custom_settings'), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT),
      '#element_validate' => [[static::class, 'settingsFormCustomSettingsElementValidate']],
    ];

    /**
     * Builds the LightGallery settings form elements for a field formatter.
     *
     * This section of the form allows users to configure core LightGallery settings
     * and plugin-specific options. It creates a details element for LightGallery
     * settings, including nested details for plugin configuration.
     *
     * - The 'lightgallery_settings' details element contains the main configuration.
     * - The 'core' sub-element is populated by buildCoreSettingsForm(), which
     *   provides core LightGallery options.
     * - The 'plugins' sub-element is a details element populated by
     *   buildPluginSettingsForm(), allowing configuration of individual plugins.
     *
     * @param array $parents
     *   The parent form element keys for proper nesting.
     * @param array $final_config
     *   The final configuration values for the LightGallery settings.
     *
     * @return array
     *   The form elements for LightGallery settings and plugins.
     */
    $parents_lg = $parents;
    $parents_lg[] = 'lightgallery_settings';

    $form['lightgallery_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('LightGallery settings'),
      '#description' => $this->t('Configure the LightGallery plugins and their options.'),
      '#open' => TRUE,
      '#tree' => TRUE,
      #'#parents' => $parents_lg,
      '#parents' => ['fields', $this->fieldDefinition->getName(), 'settings_edit_form', 'settings', 'lightgallery_settings'],
    ];

    // Core settings.
    // The core settings returned by buildCoreSettingsForm() are based on the
    // final configuration, which includes both global and entity-specific settings.
    // it include a details element with the title 'Core Settings' and a description.

    $form['lightgallery_settings']['core'] = $this->buildCoreSettingsForm(
      $this->getLightGalleryPluginDefinitions(),
      $final_config ?? [],
      ['fields', $this->fieldDefinition->getName(), 'settings_edit_form', 'settings', 'lightgallery_settings'],
    );

    $form['lightgallery_settings']['plugins'] = [
      '#type' => 'details',
      '#title' => $this->t('LightGallery Plugins'),
      '#description' => $this->t('Configure the LightGallery plugins.'),
      '#open' => TRUE,
      '#tree' => TRUE,
      # '#parents' => ['settings', 'lightgallery_settings', 'plugins'],
    ];
    $form['lightgallery_settings']['plugins'] += $this->buildPluginSettingsForm(
      $this->getLightGalleryPluginDefinitions(),
      $final_config ?? [],
      ['fields', $this->fieldDefinition->getName(), 'settings_edit_form', 'settings', 'lightgallery_settings', 'plugins'],
    );
    return $form;
  }

  /**
   * Element validation callback for the custom settings.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form array.
   */
  public static function settingsFormCustomSettingsElementValidate(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    $value = json_decode($element['#value'], TRUE, 3);

    if (is_array($value)) {
      $form_state->setValue($element['#parents'], $value);
    } else {
      $form_state->setError($element, 'The custom settings are not a valid JSON object.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

    $lightgallery = $this->getSetting('lightgallery_settings');
    # \Drupal::logger('lightgallery')->notice('<pre>' . print_r($lightgallery, TRUE) . '</pre>');


    if ($this->getSetting('inline')) {
      $summary[] = $this->t('Inline gallery');
    }

    if (!empty($this->getSetting('thumbnail_image_style'))) {
      $summary[] = $this->t('Thumbnail image style: %style', [
        '%style' => $this->getImageStyleLabel($this->getSetting('thumbnail_image_style')),
      ]);
    }

    if (!empty($this->getSetting('gallery_image_style'))) {
      $summary[] = $this->t('Gallery image style: %style', [
        '%style' => $this->getImageStyleLabel($this->getSetting('gallery_image_style')),
      ]);
    } else {
      $summary[] = $this->t('Original image in gallery');
    }

    $lightgallery = $this->getSetting('lightgallery_settings');
    $enabled_plugins = array_filter($lightgallery['plugins'] ?? [], fn($plugin) => ($plugin['enabled'] ?? FALSE));
    $summary[] = $this->t('Enabled plugins: @list', ['@list' => implode(', ', array_keys($enabled_plugins))]);


    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies(): array {
    $dependencies = parent::calculateDependencies();

    foreach (['thumbnail_image_style', 'gallery_image_style'] as $setting) {
      $image_style = $this->getSetting($setting);

      if (empty(!$image_style)) {
        $dependencies['config'][] = "image.style.$image_style";
      }
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  protected function isInline(): bool {
    return $this->getSetting('inline');
  }

  /**
   * {@inheritdoc}
   */
  protected function getLightgallerySettings(): array {

    $settings = $this->getSetting('custom_settings');

    // force thumbnail plugin if the field has multiple values
    $settings += [
      'thumbnail' => $this->fieldDefinition->getFieldStorageDefinition()->getCardinality() !== 1,
    ];

    if ($settings['thumbnail'] && empty($settings['plugins']) || !in_array('lgThumbnail', $settings['plugins'], TRUE)) {
      $settings['plugins'][] = 'lgThumbnail';
    }

    return $settings;
  }
}
