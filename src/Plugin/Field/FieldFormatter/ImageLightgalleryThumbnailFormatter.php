<?php

namespace Drupal\lightgallery\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'image_lightgallery_thumbnail' formatter.
 */
#[FieldFormatter(
  id: 'image_lightgallery_thumbnail',
  label: new TranslatableMarkup('lightGallery thumbnails'),
  field_types: [
    'image',
  ],
)]
class ImageLightgalleryThumbnailFormatter extends ImageFormatterBase {

  use LightgalleryThumbnailFormatterTrait {
    defaultSettings as traitDefaultSettings;
    settingsForm as traitSettingsForm;
    settingsSummary as traitSettingsSummary;
  }

  /**
   * Class constructor.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   */
  public function __construct(string $plugin_id, array $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, string $label, string $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $settings = self::traitDefaultSettings();
    $settings['title_as_caption'] = FALSE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = $this->traitSettingsForm($form, $form_state);

    $form['general']['title_as_caption'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Title as caption'),
      '#description' => $this->t('Use the image title as caption.'),
      '#default_value' => (int) $this->getSetting('title_as_caption'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = $this->traitSettingsSummary();

    if ($this->getSetting('title_as_caption')) {
      $summary[] = $this->t('Use image title as caption.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getItemAttributes(ContentEntityInterface $entity): array {
    if (!$entity instanceof FileInterface) {
      return [];
    }

    $attributes = [
      'data-src' => $this->getImageUrl($entity, $this->getSetting('gallery_image_style')),
    ];

    if ($this->getSetting('title_as_caption') && !empty($entity->_referringItem->title)) {
      $attributes['data-sub-html'] = '<p>' . Html::escape($entity->_referringItem->title) . '</p>';
    }

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildItemContent(ContentEntityInterface $entity): array {
    if (!isset($entity->_referringItem) || !$entity->_referringItem instanceof ImageItem) {
      throw new \InvalidArgumentException('The entity must specify an image item via _referringItem');
    }

    $item = $entity->_referringItem;
    $attributes = $item->_attributes ?? [];
    $attributes['loading'] = $this->getSetting('thumbnail_loading');
    $item->_attributes = $attributes;

    return $this->buildImage(
      $item,
      $this->getSetting('thumbnail_image_style')
    );
  }

}
