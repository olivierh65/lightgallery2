<?php

namespace Drupal\lightgallery\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lightgallery\Traits\LightGallerySettingsTrait;

/**
 * Provides a form to edit the settings.
 */
class SettingsForm extends ConfigFormBase {
  use LightGallerySettingsTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lightgallery.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lightgallery_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    /* $form['plugins'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('LightGallery options'),
      '#description' => $this->t('Configure the LightGallery plugins and their options.'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ]; */

    $definitions = $this->getLightGalleryPluginDefinitions();
    $config = $this->config('lightgallery.settings');

    // Bloc Core
    $form['core'] = $this->buildCoreSettingsForm(
      $definitions,
      $config ?? [],
      [
        'lightgallery_settings',
        'core',
        'params',
      ]
    );

    // Bloc Plugins
    $form['plugins'] = [
      '#type' => 'details',
      '#title' => $this->t('LightGallery Plugins'),
      '#description' => $this->t('Configure the LightGallery plugins.'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['plugins'] += $this->buildPluginSettingsForm(
      $definitions,
      $config ?? [],
      [
        'lightgallery_settings',
        'plugins',
      ]
    );

    return parent::buildForm($form, $form_state);
  }

  private function buildParamElements(array $param_config, array $param_def, string $config_path): array {
    $elements = [];

    foreach ($param_def as $key => $def) {
      if (isset($def['#type']) && $def['#type'] === 'details' && isset($def['params'])) {
        // Cas récursif pour les sous-éléments
        $wrapper = $def;
        unset($wrapper['params']);

        $wrapper += [
          '#tree' => TRUE,
        ];

        $wrapper += $this->buildParamElements(
          $param_config[$key]['params'] ?? [],
          $def['params'],
          "$config_path.$key.params"
        );

        $elements[$key] = $wrapper;
      } else {
        $def['#default_value'] = $param_config[$key] ?? '';
        $def['#config_target'] = "lightgallery.settings:$config_path.$key";

        $elements[$key] = $def;
      }
    }

    return $elements;
  }
}
