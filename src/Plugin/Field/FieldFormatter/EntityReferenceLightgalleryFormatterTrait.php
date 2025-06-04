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
use Drupal\lightgallery\Traits\LightGallerySettingsTrait;

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
    $build['#settings'] = LightGallerySettingsTrait::getGeneralSettings($this->getSettings());
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
