<?php

namespace Drupal\lightgallery\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\Url;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Xss;
use Drupal\Component\Utility\Html;
use Drupal\lightgallery\Traits\LightGallerySettingsTrait;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\field\EntityField;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Album Justified Gallery style plugin.
 *
 * @ViewsStyle(
 *   id = "album_justified_gallery",
 *   title = @Translation("Album Justified Gallery"),
 *   help = @Translation("Displays albums in a justified gallery layout."),
 *   theme = "album_justified_gallery",
 *   display_types = {"normal"}
 * )
 */
class AlbumJustifiedGallery extends StylePluginBase {
    use LightGallerySettingsTrait;

    protected FileUrlGeneratorInterface $fileUrlGenerator;

    /**
     * {@inheritdoc}
     */
    protected $usesRowPlugin = TRUE;

    /**
     * {@inheritdoc}
     */
    protected $usesGrouping = FALSE;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUrlGeneratorInterface $file_url_generator) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->fileUrlGenerator = $file_url_generator;
    }


    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('file_url_generator')
        );
    }


    /**
     * {@inheritdoc}
     */
    protected function defineOptions() {
        $options = parent::defineOptions();
        $options['rowHeight'] = ['default' => 200];
        $options['margins'] = ['default' => 10];
        $options['lastRow'] = ['default' => 'justify'];

        $options['image_field'] = ['default' => ''];
        $options['title_field'] = ['default' => ''];
        $options['author_field'] = ['default' => ''];
        $options['url_field'] = ['default' => ''];

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(&$form, FormStateInterface $form_state) {
        parent::buildOptionsForm($form, $form_state);

        list($fields_text, $fields_media, $fields_taxo) = $this->getTextAndMediaFields($this->view);


        // Champ pour l'image
        $form['image_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Image field'),
            '#options' => $fields_media,
            '#default_value' => $this->options['image_field'],
            '#required' => TRUE,
        ];

        // Champ pour le titre
        $form['title_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Title field'),
            '#options' => ['' => $this->t('- None -')] + $fields_text,
            '#default_value' => $this->options['title_field'],
            '#required' => FALSE,
        ];

        // Champ pour l'auteur
        $form['author_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Author field'),
            '#options' => ['' => $this->t('- None -')] + $fields_taxo,
            '#default_value' => $this->options['author_field'],
        ];

        //  Options Justified Gallery
        $form['rowHeight'] = [
            '#type' => 'number',
            '#title' => $this->t('Row height (px)'),
            '#default_value' => $this->options['rowHeight'],
        ];

        $form['maxRowsCount'] = [
            '#type' => 'number',
            '#title' => $this->t('Max rows count'),
            '#default_value' => $this->options['maxRowsCount'] ?? 0,
            '#description' => $this->t('Maximum number of rows to display. Set to 0 for no limit.'),
        ];

        $form['margins'] = [
            '#type' => 'number',
            '#title' => $this->t('Margins'),
            '#default_value' => $this->options['margins'],
            '#description' => $this->t('Margin between items in pixels.'),
        ];
        $form['border'] = [
            '#type' => 'number',
            '#title' => $this->t('Border'),
            '#default_value' => $this->options['border'] ?? -1,
            '#description' => $this->t('Border around each item in pixels.'),
        ];

        $form['lastRow'] = [
            '#type' => 'select',
            '#title' => $this->t('Last row behavior'),
            '#options' => [
                'justify' => $this->t('Justify'),
                'nojustify' => $this->t('No justify'),
                'hide' => $this->t('Hide'),
                'center' => $this->t('Center'),
                'right' => $this->t('Right'),
            ],
            '#default_value' => $this->options['lastRow'],
        ];
        $form['captions'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Display captions'),
            '#default_value' => $this->options['captions'] ?? TRUE,
            '#description' => $this->t('Display captions for images.'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function render() {

        $id = rand();

        $build = [
            '#theme' => $this->themeFunctions(),
            '#rows' => [],
            '#options' => $this->options,
            '#attributes' => [
                'class' => ['album-justified-gallery', 'lightgallery-init'],
            ],
            '#attached' => [
                'library' => [
                    'lightgallery/lightgallery',
                    'lightgallery/init_view',
                    'lightgallery/justified_gallery',
                ],
                'drupalSettings' => [
                    'settings' => [
                        'lightgallery' => [
                            'inline' => false,
                            'galleryId' => $id,
                        ],
                        'justifiedGallery' => [
                            'rowHeight' => $this->options['rowHeight'],
                            'maxRowsCount' => $this->options['maxRowsCount'] ?? 0,
                            'border' => $this->options['border'] ?? -1,
                            'captions' => $this->options['captions'] ?? TRUE,
                            'margins' => $this->options['margins'],
                            'lastRow' => $this->options['lastRow'],
                        ],
                    ],
                ],
            ],
        ];


        foreach ($this->view->result as $index => $row) {
            $this->view->row_index = $index;

            // Get first media as presentation image
            $image_url = $this->getMediaImageUrl($row, $this->options['image_field']);

            // Text fields
            $title = !empty($this->options['title_field']) ? $this->getFieldValue($index, $this->options['title_field']) : '';
            $author = !empty($this->options['author_field']) ? $this->getFieldValue($index, $this->options['author_field']) : '';
            $description = !empty($this->options['description_field']) ? $this->getFieldValue($index, $this->options['description_field']) : '';
            $url = Url::fromRoute('entity.node.canonical', ['node' => $row->nid])->toString();

            // Get all media items associated with the row's entity
            $medias = [];
            if (
                isset($row->_entity)
                && $row->_entity instanceof \Drupal\Core\Entity\EntityInterface
                && $row->_entity->hasField($this->options['image_field'])
            ) {
                foreach ($row->_entity->get($this->options['image_field']) as $media_item) {
                    $media = $media_item->entity;
                    switch ($media->getSource()->getPluginId()) {
                        case 'image':
                            // Image media type
                            $file = $media->get('field_media_image')->entity;
                            if ($file instanceof \Drupal\file\FileInterface) {

                                $medias[] = [
                                    'url' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
                                    'mime_type' => $file->getMimeType(),
                                    'alt' => $media->get('field_media_image')->first()->get('alt')->getValue() ?? '',
                                    'title' => $media->get('field_media_image')->first()->get('title')->getValue() ?? '',
                                ];
                            }
                            break;
                        case 'video_file':
                            // Video file media type
                            $file = $media->get('field_media_video_file')->entity;
                            $thumbnail = $media->get('thumbnail')->entity;
                            if ($file instanceof \Drupal\file\FileInterface) {
                                $medias[] = [
                                    'url' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
                                    'mime_type' => $file->getMimeType(),
                                    'thumbnail' => $thumbnail ? $this->fileUrlGenerator->generateAbsoluteString($thumbnail->getFileUri()) : '',
                                    'title' => $media->get('field_media_video_file')->first()->get('description')->getValue() ?? '',
                                ];
                            }
                            break;
                        //TODO: Add support for other media types if needed
                        default:
                            // Unknown media type, log a warning
                            \Drupal::logger('album_justified_gallery')->warning('Unsupported media type: @type', ['@type' => $media->getSource()->getPluginId()]);
                            break;
                    }
                }
            }

            $renderer = $this->view->display_handler->getOption('fields')[$this->options['image_field']]['settings']['view_mode'];
            $display = \Drupal::service('entity_display.repository')->getViewDisplay('node', $row->_entity->bundle(), $renderer ?? 'default');
            if ($display && $display->getComponent($this->options['image_field'])) {
                $node_settings = $display->getComponent($this->options['image_field'])['settings'];
            } else {
                $node_settings = [];
            }

            $album_id = 'album-item-' . rand();
            // Build the settings for the album
            // Should be the sames settings for all albums, as they use the same formatter/renderer
            $build['#attached']['drupalSettings']['lightgallery']['albums'][$album_id] = static::getGeneralSettings($node_settings);
            //Build plugins list and attach libraries
            $plugins_mapping = static::getPluginsLibrary();
            $build['#attached']['drupalSettings']['lightgallery']['albums'][$album_id]['plugins'] = [];
            foreach ($node_settings['lightgallery_settings']['plugins'] ?? [] as $plugin_name => $plugin) {
                if (isset($plugin['enabled']) && $plugin['enabled'] == false) {
                    continue; // Skip disabled plugins
                }
                $build['#attached']['library'][] = 'lightgallery/lightgallery-' . $plugin_name ?? $plugin_name;
                $build['#attached']['drupalSettings']['lightgallery']['albums'][$album_id]['plugins'][] = $plugins_mapping[$plugin_name] ?? $plugin_name;
            }

            $build['#rows'][] = [
                'image_url' => $image_url,
                'title' => $title,
                'author' => $author,
                'description' => $description,
                'url' => $url,
                'medias' => $medias,
                'id' => $album_id,
            ];
        }

        unset($this->view->row_index);
        return $build;
    }

    private function buildJSONSettings($node_settings) {
        $settings = [];
        foreach ($node_settings as $key => $value) {
            if (is_array($value)) {
                $settings[$key] = json_encode($value);
            } else {
                $settings[$key] = PlainTextOutput::renderFromHtml(Xss::filter($value));
            }
        }
        return $settings;
    }


    protected function getMediaImageUrl($row, $field_name) {
        if (empty($field_name)) {
            return '';
        }

        # TODO: also take video?
        # TODO: Take into account that the field may not be set or may not be a media entity.
        try {
            $media_item = $row->_entity->get($field_name)->entity ?? NULL;
            if ($media_item instanceof \Drupal\media\MediaInterface) {
                if ($media_item->hasField('field_media_image') && !$media_item->get('field_media_image')->isEmpty()) {
                    $file = $media_item->get('field_media_image')->entity;
                    if ($file) {
                        // Return the absolute URL of the file
                        return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                    } else {
                        return \Drupal::request()->getSchemeAndHttpHost() . '/' . \Drupal::service('extension.list.module')->getPath('lightgallery') . '/images/Document_error.png';
                    }
                }
            }
        } catch (\Exception $e) {
            \Drupal::logger('album_justified_gallery')->error('Media field error: @error', ['@error' => $e->getMessage()]);
        }

        return '';
    }

    public function validateOptionsForm(&$form, FormStateInterface $form_state) {
        parent::validateOptionsForm($form, $form_state);

        $image_field = $form_state->getValue(['style_options', 'image_field']);
        $handlers = $this->displayHandler->getHandlers('field');

        if (empty($image_field) || !isset($handlers[$image_field])) {
            $form_state->setErrorByName('image_field', $this->t('You must select a valid image/media field.'));
            return;
        }

        // Récupérer le type d'entité et le bundle depuis la vue
        $entity_type = $this->view->storage->get('base_table'); // ex: 'node'
        if ($entity_type === 'node_field_data') {
            $entity_type = 'node';
        } elseif ($entity_type === 'media_field_data') {
            $entity_type = 'media';
        }
        $bundle = $this->view->display_handler->getOption('filters')['type']['value'] ?? NULL; // ex: 'article'
        if (is_array($bundle)) {
            $bundle = reset($bundle); // Prendre le premier bundle si c'est un tableau
        }

        // Charger la définition du champ
        if ($entity_type && $bundle) {
            $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
            if (isset($field_definitions[$image_field])) {
                $field_def = $field_definitions[$image_field];
                $type = $field_def->getType();
                if (
                    $type !== 'image' &&
                    !($type === 'entity_reference' && $field_def->getSetting('target_type') === 'media')
                ) {
                    $form_state->setErrorByName('image_field', $this->t('The selected field must be an image or a media reference.'));
                }
            }
        }
    }

    protected function getTextAndMediaFields(ViewExecutable $view) {
        $text_fields = [];
        $media_fields = [];
        $taxo_fields = [];

        // 1. Déterminer l'entité et le bundle
        $base_table = $view->storage->get('base_table');
        $entity_type_id = NULL;
        $table_to_entity = [
            'node_field_data' => 'node',
            'media_field_data' => 'media',
            'user_field_data' => 'user',
            'taxonomy_term_field_data' => 'taxonomy_term',
            // Ajoute d'autres cas si besoin
        ];
        if (isset($table_to_entity[$base_table])) {
            $entity_type_id = $table_to_entity[$base_table];
        } else {
            // Fallback: recherche dans les définitions d'entité
            foreach (\Drupal::entityTypeManager()->getDefinitions() as $id => $definition) {
                if ($definition->getBaseTable() === $base_table) {
                    $entity_type_id = $id;
                    break;
                }
            }
        }
        if (!$entity_type_id) {
            return [$text_fields, $media_fields, $taxo_fields];
        }

        // Récupérer le bundle (type de contenu)
        $bundle = $view->display_handler->getOption('filters')['type']['value'] ?? NULL;
        if (is_array($bundle)) {
            $bundle = reset($bundle);
        }
        if (!$bundle) {
            return [$text_fields, $media_fields, $taxo_fields];
        }

        // 2. Charger les définitions de champ pour ce bundle
        $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type_id, $bundle);

        // 3. Parcourir les handlers de champ de la vue
        foreach ($view->display_handler->getHandlers('field') as $field_id => $handler) {
            $field_name = $handler->field ?? NULL;
            if ($field_name && isset($field_definitions[$field_name])) {
                $field_def = $field_definitions[$field_name];
                $type = $field_def->getType();

                // 4. Tester le type du champ
                if (in_array($type, ['string', 'text', 'text_long', 'text_with_summary'])) {
                    $text_fields[$field_name] = (string)$field_def->getLabel();
                } elseif (
                    $type === 'entity_reference' &&
                    $field_def->getSetting('target_type') === 'media'
                ) {
                    $media_fields[$field_name] = (string)$field_def->getLabel();
                } elseif (
                    $type === 'entity_reference' &&
                    $field_def->getSetting('target_type') === 'taxonomy_term'
                ) {
                    $taxo_fields[$field_name] = (string)$field_def->getLabel();
                }
            }
        }

        return [$text_fields, $media_fields, $taxo_fields];
    }
}
