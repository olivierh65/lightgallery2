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
        $options['row_height'] = ['default' => 200];
        $options['margins'] = ['default' => 10];
        $options['last_row'] = ['default' => 'justify'];

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

        // Récupère la liste des champs disponibles
        $field_options = [];
        foreach ($this->displayHandler->getHandlers('field') as $field_name => $handler) {
            $field_options[$field_name] = $handler->adminLabel();
        }


        // Champ pour l'image
        $form['image_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Image field'),
            '#options' => $field_options,
            '#default_value' => $this->options['image_field'],
            '#required' => TRUE,
        ];

        // Champ pour le titre
        $form['title_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Title field'),
            '#options' => ['' => $this->t('- None -')] + $field_options,
            '#default_value' => $this->options['title_field'],
            '#required' => FALSE,
        ];

        // Champ pour l'auteur
        $form['author_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Author field'),
            '#options' => ['' => $this->t('- None -')] + $field_options,
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
            '#default_value' => $this->options['last_row'],
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
            $title = $this->getFieldValue($index, $this->options['title_field']);
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

            // retrieve display settings for the image field to get settings
            // use the default display
            // TODO: allow selecting a specific display mode ?
            $display = \Drupal::service('entity_display.repository')->getViewDisplay('node', $row->_entity->bundle(), 'default');
            if ($display && $display->getComponent($this->options['image_field'])) {
                $node_settings = $display->getComponent($this->options['image_field'])['settings'];
            } else {
                $node_settings = [];
            }

            $album_id = 'album-item-' . rand();
            // Build the settings for the album
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
                    return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                }
            }
        } catch (\Exception $e) {
            \Drupal::logger('album_justified_gallery')->error('Media field error: @error', ['@error' => $e->getMessage()]);
        }

        return '';
    }

}
