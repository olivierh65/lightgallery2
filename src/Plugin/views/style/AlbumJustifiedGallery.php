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
            '#options' => $field_options,
            '#default_value' => $this->options['title_field'],
            '#required' => TRUE,
        ];

        // Champ pour l'auteur
        $form['author_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Author field'),
            '#options' => $field_options,
            '#default_value' => $this->options['author_field'],
        ];

        // Champ pour l'URL
        $form['url_field'] = [
            '#type' => 'select',
            '#title' => $this->t('URL field'),
            '#options' => $field_options,
            '#default_value' => $this->options['url_field'],
            '#description' => $this->t('Typically the node URL field'),
        ];

        // Options Justified Gallery
        $form['row_height'] = [
            '#type' => 'number',
            '#title' => $this->t('Row height (px)'),
            '#default_value' => $this->options['row_height'],
        ];

        $form['row_height'] = [
            '#type' => 'number',
            '#title' => $this->t('Row height'),
            '#default_value' => $this->options['row_height'],
            '#description' => $this->t('Target height for each row in pixels.'),
        ];

        $form['margins'] = [
            '#type' => 'number',
            '#title' => $this->t('Margins'),
            '#default_value' => $this->options['margins'],
            '#description' => $this->t('Margin between items in pixels.'),
        ];

        $form['last_row'] = [
            '#type' => 'select',
            '#title' => $this->t('Last row behavior'),
            '#options' => [
                'justify' => $this->t('Justify'),
                'nojustify' => $this->t('No justify'),
                'hide' => $this->t('Hide'),
            ],
            '#default_value' => $this->options['last_row'],
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
                'id' => 'lightgallery-' . $id,
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
                            'lightgallery-' . $id => [
                                'inline' => TRUE,
                                'plugins' => ['lgJustifiedGallery'],
                                'galleryId' => $id,
                            ],
                            'rowHeight' => $this->options['row_height'],
                            'margins' => $this->options['margins'],
                            'lastRow' => $this->options['last_row'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($this->view->result as $index => $row) {
            $this->view->row_index = $index;

            // Image de couverture
            $image_url = $this->getMediaImageUrl($row, $this->options['image_field']);

            // Champs texte
            $title = $this->getFieldValue($index, $this->options['title_field']);
            $author = !empty($this->options['author_field']) ? $this->getFieldValue($index, $this->options['author_field']) : '';
            $description = !empty($this->options['description_field']) ? $this->getFieldValue($index, $this->options['description_field']) : '';
            $url = Url::fromRoute('entity.node.canonical', ['node' => $row->nid])->toString();

            // Récupère tous les médias de l'album (à adapter selon ta structure)
            $media_urls = [];
            if (
                isset($row->_entity)
                && $row->_entity instanceof \Drupal\Core\Entity\EntityInterface
                && $row->_entity->hasField($this->options['image_field'])
            ) {
                foreach ($row->_entity->get($this->options['image_field']) as $media_item) {
                    $media = $media_item->entity;
                    // Pour les images
                    if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
                        $file = $media->get('field_media_image')->entity;
                        if ($file instanceof \Drupal\file\FileInterface) {
                            $media_urls[] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                        }
                    }
                    // Pour les vidéos
                    elseif ($media->hasField('field_media_video') && !$media->get('field_media_video')->isEmpty()) {
                        $file = $media->get('field_media_video')->entity;
                        if ($file instanceof \Drupal\file\FileInterface) {
                            $media_urls[] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                        }
                    }
                }
            }

            // retrieve display settings for the image field to get settings
            $display = \Drupal::service('entity_display.repository')->getViewDisplay('node', $row->_entity->bundle(),'default');
            if ($display && $display->getComponent($this->options['image_field'])) {
                $node_settings = $display->getComponent($this->options['image_field'])['settings'];
            }
            else {
                $node_settings = [];
            }

            $album_id = 'album-item-' . rand();
            $build['#attached']['drupalSettings']['lightgallery']['albums'][$album_id] = $node_settings;

            $build['#rows'][] = [
                'image_url' => $image_url,
                'title' => $title,
                'author' => $author,
                'description' => $description,
                'url' => $url,
                'media_urls' => $media_urls,
                'id' => $album_id,
            ];
        }

        unset($this->view->row_index);
        return $build;
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
