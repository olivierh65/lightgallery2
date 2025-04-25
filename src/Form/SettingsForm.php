<?php

namespace Drupal\lightgallery\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to edit the settings.
 */
class SettingsForm extends ConfigFormBase {

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
    $config = $this->config('lightgallery.settings');

    $form['license_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('License key'),
      '#default_value' => $config->get('license_key'),
      '#required' => TRUE,
      '#config_target' => 'lightgallery.settings:license_key',
    ];

    return parent::buildForm($form, $form_state);
  }

}
