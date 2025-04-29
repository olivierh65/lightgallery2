<?php

namespace Drupal\lightgallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Trait for lightGallery thumbnail formatters.
 */
trait LightgalleryThumbnailFormatterTrait {

  use EntityReferenceLightgalleryFormatterTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'inline' => FALSE,
      'thumbnail_image_style' => NULL,
      'thumbnail_loading' => 'lazy',
      'gallery_image_style' => NULL,
      'custom_settings' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = parent::settingsForm($form, $form_state);

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

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#parents' => $parents,
    ];

    $form['advanced']['custom_settings'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom settings'),
      '#description' => $this->t('A JSON object with <a href=":url", target="_blank">lightGallery settings</a>. Plugins can be specified as strings, e.g. <code>@plugins</code>.<br />Note that the module overrides some lightGallery defaults. If you define those settings here as well, the value specified here will be used.', [
        ':url' => 'https://www.lightgalleryjs.com/docs/settings',
        '@plugins' => "{ 'plugins': ['lgThumbnail'] }",
      ]),
      '#default_value' => json_encode($this->getSetting('custom_settings'), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT),
      '#element_validate' => [[static::class, 'settingsFormCustomSettingsElementValidate']],
    ];

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
    $value = json_decode($element['#value'], TRUE, 2);

    if (is_array($value)) {
      $form_state->setValue($element['#parents'], $value);
    }
    else {
      $form_state->setError($element, t('The custom settings are not a valid JSON object.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

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
    }
    else {
      $summary[] = $this->t('Original image in gallery');
    }

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
    $settings += [
      'thumbnail' => $this->fieldDefinition->getFieldStorageDefinition()->getCardinality() !== 1,
    ];

    if ($settings['thumbnail'] && empty($settings['plugins']) || !in_array('lgThumbnail', $settings['plugins'], TRUE)) {
      $settings['plugins'][] = 'lgThumbnail';
    }

    return $settings;
  }

}
