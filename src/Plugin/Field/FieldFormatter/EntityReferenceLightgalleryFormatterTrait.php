<?php

namespace Drupal\lightgallery\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\file\FileInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Trait for entity reference lightGallery formatters.
 *
 * @SuppressWarnings(PHPMD.LongClassNames)
 */
trait EntityReferenceLightgalleryFormatterTrait {

  /**
   * The image style options.
   *
   * @var string[]
   */
  protected static array $imageStyleOptions;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    // Early opt-out if the field is empty.
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    $entities = $this->getEntitiesToView($items, $langcode);

    if (empty($entities)) {
      return [];
    }

    // Build the list.
    $entity = $items->getEntity();
    $id_suffix = substr(md5($entity->getEntityTypeId() . '|' . $entity->id() . '|' . $this->fieldDefinition->getName()), 0, 10);

    $build = [];
    $build['#theme'] = 'lightgallery__' . str_replace('_lightgallery_', '_', $this->getBaseId()) . '__' . $entity->getEntityTypeId() . '__' . $this->fieldDefinition->getName();
    $build['#inline'] = $this->isInline();
    $build['#settings'] = $this->getGeneralSettings();
    $build['#settings'] += $this->getLightgallerySettings();
    $build['#settings'] += [
      'galleryId' => Html::getUniqueId('lightgallery-' . $id_suffix),
    ];

    if ($build['#inline']) {
      $build['#settings'] += [
        'closable' => FALSE,
        'showCloseIcon' => FALSE,
        'showMaximizeIcon' => TRUE,
      ];
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    // @phpstan-ignore-next-line
    foreach ($entities as $delta => $entity) {
      $build['#items'][$delta] = [
        'attributes' => $this->getItemAttributes($entity),
        'content' => $this->buildItemContent($entity),
        'item' => $entity->_referringItem ?? NULL,
      ];
    }

    return [
      0 => $build,
    ];
  }

  /**
   * Indicates if the gallery should be inline.
   *
   * @return bool
   *   True if the gallery should be inline.
   */
  abstract protected function isInline(): bool;

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
  private function getGeneralSettings(): array {

    // Add the core settings from the configuration.
    $core_settings_def = $this->getLightGalleryPluginDefinitions()['core']['params'];
    $core_settings = $this->getSetting('lightgallery_settings')['core']['params'] ?? [];
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
    $plugins_library = $this->getPluginsLibrary();
    $plugins_settings_def = $this->getLightGalleryPluginDefinitions()['plugins'];
    $plugins = [];
    $settings['plugins'] = [];
    $plugin_settings = $this->getSetting('lightgallery_settings')['plugins'] ?? [];
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

  /**
   * Get the lightGallery settings.
   *
   * @return array
   *   The lightGallery settings.
   */
  abstract protected function getLightgallerySettings(): array;

  /**
   * Get the item wrapper attributes.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   *
   * @return array
   *   The item wrapper attributes.
   */
  abstract protected function getItemAttributes(ContentEntityInterface $entity): array;

  /**
   * Build the content for an item.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   *
   * @return array
   *   The item content render array.
   */
  abstract protected function buildItemContent(ContentEntityInterface $entity): array;

  /**
   * Get the URL of an image.
   *
   * @param \Drupal\file\FileInterface $image
   *   The image file.
   * @param string|null $image_style_id
   *   The image style ID or NULL to get the original image.
   *
   * @return string
   *   An absolute URL of the image (style).
   */
  protected function getImageUrl(FileInterface $image, ?string $image_style_id = NULL): string {
    $uri = $image->getFileUri();

    if ($image_style_id === NULL) {
      return $this->getFileUrlGenerator()->generateAbsoluteString($uri);
    }

    /** @var \Drupal\image\ImageStyleInterface|null $image_style */
    $image_style = $this->getEntityTypeManager()->getStorage('image_style')->load($image_style_id);

    if ($image_style !== NULL && $image_style->supportsUri($uri)) {
      return $image_style->buildUrl($uri);
    }

    return $this->getFileUrlGenerator()->generateAbsoluteString($uri);
  }

  /**
   * Build an image render array.
   *
   * @param \Drupal\image\Plugin\Field\FieldType\ImageItem $image
   *   The image item.
   * @param string|null $image_style_id
   *   The image style ID.
   *
   * @return array
   *   The image render array.
   */
  protected function buildImage(ImageItem $image, ?string $image_style_id = NULL): array {
    $attributes = $image->_attributes ?? [];
    unset($image->_attributes);

    return [
      '#theme' => 'image_formatter__lightgallery',
      '#item' => $image,
      '#item_attributes' => $attributes,
      '#image_style' => $image_style_id,
    ];
  }

  /**
   * Get the image style options.
   *
   * @return string[]
   *   The image style options.
   */
  protected function getImageStyleOptions(): array {
    if (isset(static::$imageStyleOptions)) {
      return static::$imageStyleOptions;
    }

    $options = $this->getEntityTypeManager()->getStorage('image_style')->loadMultiple();
    $options = array_map(function (ImageStyleInterface $image_style): string {
      return (string) $image_style->label();
    }, $options);

    static::$imageStyleOptions = $options;

    return $options;
  }

  /**
   * Get the label of an image style.
   *
   * @param string $image_style
   *   The image style ID.
   *
   * @return string
   *   The image style label or specified ID if unknown.
   *
   * @SuppressWarnings(PHPMD.UndefinedVariable)
   */
  protected function getImageStyleLabel(string $image_style): string {
    if (isset(static::$imageStyleOptions[$image_style])) {
      return static::$imageStyleOptions[$image_style];
    }

    return $this->getEntityTypeManager()
      ->getStorage('image_style')
      ->load($image_style)
      ?->label() ?? $image_style;
  }

  /**
   * Get the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function getEntityTypeManager(): EntityTypeManagerInterface {
    if (!isset($this->entityTypeManager)) {
      // @phpstan-ignore-next-line
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }

    return $this->entityTypeManager;
  }

  /**
   * Get the file URL generator.
   *
   * @return \Drupal\Core\File\FileUrlGeneratorInterface
   *   The file URL generator.
   */
  protected function getFileUrlGenerator(): FileUrlGeneratorInterface {
    if (!isset($this->fileUrlGenerator)) {
      // @phpstan-ignore-next-line
      $this->fileUrlGenerator = \Drupal::service('file_url_generator');
    }

    return $this->fileUrlGenerator;
  }

}
