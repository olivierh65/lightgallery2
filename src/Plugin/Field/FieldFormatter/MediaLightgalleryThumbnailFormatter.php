<?php

namespace Drupal\lightgallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'media_lightgallery_thumbnail' formatter.
 */
#[FieldFormatter(
  id: 'media_lightgallery_thumbnail',
  label: new TranslatableMarkup('lightGallery thumbnails'),
  field_types: [
    'entity_reference',
  ],
)]
class MediaLightgalleryThumbnailFormatter extends EntityReferenceFormatterBase {

  use LightgalleryThumbnailFormatterTrait {
    defaultSettings as traitDefaultSettings;
    settingsForm as traitSettingsForm;
    settingsSummary as traitSettingsSummary;
    calculateDependencies as traitCalculateDependencies;
    getLightgallerySettings as traitGetLightgallerySettings;
  }

  /**
   * Supported source types.
   */
  protected const SOURCE_IMAGE = 'image';
  protected const SOURCE_VIDEO = 'video';

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected EntityDisplayRepositoryInterface $entityDisplayRepository;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

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
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(string $plugin_id, array $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, string $label, string $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, FileUrlGeneratorInterface $file_url_generator, EntityDisplayRepositoryInterface $entity_display_repository, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->renderer = $renderer;
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
      $container->get('file_url_generator'),
      $container->get('entity_display.repository'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $settings = self::traitDefaultSettings();
    $settings['caption_view_mode'] = NULL;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = $this->traitSettingsSummary();
    $caption_view_mode = $this->getSetting('caption_view_mode');

    if (!empty($caption_view_mode)) {
      $view_modes = $this->entityDisplayRepository->getViewModeOptions('media');
      $summary[] = $this->t('Use view mode %view_mode as caption.', [
        '%view_mode' => $view_modes[$caption_view_mode] ?? $caption_view_mode,
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form = $this->traitSettingsForm($form, $form_state);

    $default = $this->getSetting('caption_view_mode');
    $options = $this->entityDisplayRepository->getViewModeOptions('media');

    $form['general']['caption_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Caption view mode'),
      '#options' => $options,
      '#default_value' => isset($default, $options[$default]) ? $default : NULL,
      '#empty_value' => '',
      '#empty_option' => '- ' . $this->t('None') . ' -',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies(): array {
    $dependencies = $this->traitCalculateDependencies();
    $caption_view_mode = $this->getSetting('caption_view_mode');

    if (!empty($caption_view_mode)) {
      $dependencies['config'][] = "core.entity_view_mode.media.$caption_view_mode";
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  protected function getLightgallerySettings(): array {
    $settings = $this->traitGetLightgallerySettings();
    $settings += [
      'loadYouTubeThumbnail' => FALSE,
    ];

    if (empty($settings['plugins']) || !in_array('lgVideo', $settings['plugins'], TRUE)) {
      $settings['plugins'][] = 'lgVideo';
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function getItemAttributes(ContentEntityInterface $entity): array {
    if (!$entity instanceof MediaInterface) {
      return [];
    }

    $source = $entity->getSource()->getPluginId();
    $items = $this->getMediaSourceItems($entity);
    $attributes = [];

    // Caption.
    $caption_view_mode = $this->getSetting('caption_view_mode');

    if (!empty($caption_view_mode)) {
      $caption = $this->renderCaption($entity, $caption_view_mode);

      if ($caption !== NULL) {
        $attributes['data-sub-html'] = $caption;
      }
    }

    // Image media.
    if (in_array($source, $this->getSupportedSourcePluginIds(self::SOURCE_IMAGE), TRUE)) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $items->entity;
      if ($file === NULL) {
        return [];
      }

      $attributes['data-src'] = $this->getImageUrl($file, $this->getSetting('gallery_image_style'));

      return $attributes;
    }

    // Video media.
    /** @var \Drupal\file\FileInterface $thumbnail */
    $thumbnail = $entity->get('thumbnail')->entity;
    if ($thumbnail === NULL) {
      return [];
    }
    $attributes['data-poster'] = $this->getImageUrl($thumbnail, $this->getSetting('gallery_image_style'));

    switch ($source) {
      case 'oembed:video':
      case 'video_embed_field':
        $attributes['data-src'] = $items->first()->getString();
        break;

      case 'video_file':
        /** @var \Drupal\file\FileInterface $file */
        $file = $items->entity;
        $attributes['data-video'] = '{"source": [{"src":"' . 
              $this->getFileUrlGenerator()->generateAbsoluteString($file->getFileUri()) . '", "type": "video/mp4"}], "attributes": {"preload": false, "controls": true}}';
        // $attributes['data-poster'] = $this->getImageUrl($thumbnail, $this->getSetting('gallery_image_style'));
        break;
    }

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildItemContent(ContentEntityInterface $entity): array {
    if (!$entity instanceof MediaInterface) {
      return [];
    }

    /** @var \Drupal\image\Plugin\Field\FieldType\ImageItem $item */
    $item = $entity->get('thumbnail')->first();
    // @phpstan-ignore-next-line
    $item->_attributes = [
      'loading' => $this->getSetting('thumbnail_loading'),
      'data-is-video-thumbnail' => in_array(
        $entity->getSource()->getPluginId(),
        $this->getSupportedSourcePluginIds(self::SOURCE_VIDEO),
        TRUE
      ),
    ];

    return $this->buildImage(
      $item,
      $this->getSetting('thumbnail_image_style')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $source_plugins = $this->getSupportedSourcePluginIds();

    /** @var \Drupal\media\MediaInterface[] $entities */
    $entities = parent::getEntitiesToView($items, $langcode);
    $entities = array_filter($entities, function (MediaInterface $media) use ($source_plugins): bool {
      // Ensure a thumbnail is present.
      if (!$media->hasField('thumbnail') || $media->get('thumbnail')->isEmpty()) {
        return FALSE;
      }

      // Only certain sources are supported.
      if (!in_array($media->getSource()->getPluginId(), $source_plugins, TRUE)) {
        return FALSE;
      }

      return !$this->getMediaSourceItems($media)->isEmpty();
    });

    return $entities;
  }

  /**
   * Get the source field items of a media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The source field items.
   */
  protected function getMediaSourceItems(MediaInterface $media): FieldItemListInterface {
    $field_name = $media->getSource()->getConfiguration()['source_field'];

    return $media->get($field_name);
  }

  /**
   * Render the caption view of an entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   * @param string $view_mode
   *   The caption view mode.
   *
   * @return string|null
   *   The caption HTML or NULL if no caption.
   */
  protected function renderCaption(MediaInterface $media, string $view_mode): ?string {
    $options = $this->entityDisplayRepository->getViewModeOptionsByBundle('media', $media->bundle());

    if (!isset($options[$view_mode])) {
      return NULL;
    }

    $build = $this->getEntityTypeManager()
      ->getViewBuilder('media')
      ->view($media, $view_mode);

    $html = $this->renderer->renderInIsolation($build);

    // Ensure it's not only empty HTML tags.
    if (empty(trim(strip_tags($html)))) {
      return NULL;
    }

    // Only allow certain tags.
    $html = strip_tags($html, '<div><p><h3><h4><h5><h6><h6><em><strong><span><ul><ol><li>');

    // Remove all attributes.
    $html = preg_replace('#\s+[\w-]+="[^"]*"#', '', $html);

    // Remove empty tags.
    $html = preg_replace('#<[\w-]+\s*>\s*</[\w-]+\s*>#', '', $html);

    // Remove unneded whitespace.
    $html = preg_replace('#[\r\n\t]+#', '', $html);
    $html = preg_replace('#[ ]{2,}#', ' ', $html);

    return trim($html);
  }

  /**
   * Get a list of supported source plugin IDs.
   *
   * @param string|null $type
   *   The source type or NULL to get all supported source plugin IDs.
   *
   * @return string[]
   *   The source plugin IDs.
   */
  protected function getSupportedSourcePluginIds(?string $type = NULL): array {
    return match ($type) {
      self::SOURCE_IMAGE => ['image'],
      self::SOURCE_VIDEO => ['oembed:video', 'video_embed_field', 'video_file'],
      NULL => array_merge(
        $this->getSupportedSourcePluginIds(self::SOURCE_IMAGE),
        $this->getSupportedSourcePluginIds(self::SOURCE_VIDEO),
      ),
      default => [],
    };
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition): bool {
    return parent::isApplicable($field_definition) && $field_definition->getSetting('target_type') === 'media';
  }

}
