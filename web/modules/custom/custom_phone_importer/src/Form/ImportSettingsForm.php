<?php

namespace Drupal\custom_phone_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ImportSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['custom_phone_importer.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_phone_importer.settings');

    $form['publish_products'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ürünleri Yayınla'),
      '#default_value' => $config->get('publish_products'),
    ];

    $form['publish_variations'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Varyasyonları Yayınla'),
      '#default_value' => $config->get('publish_variations'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('custom_phone_importer.settings')
      ->set('publish_products', $form_state->getValue('publish_products'))
      ->set('publish_variations', $form_state->getValue('publish_variations'))
      ->save();

    $this->messenger()->addStatus($this->t('Ayarlar kaydedildi.'));
  }
}
