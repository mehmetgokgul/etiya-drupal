<?php

namespace Drupal\custom_phone_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\State\StateInterface;

class PhoneImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'phone_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['phone_json_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Phone JSON File'),
      '#description' => $this->t('Upload a JSON file containing phone data.'),
      '#upload_location' => 'public://phone_imports/',
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save for Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_ids = $form_state->getValue('phone_json_file');

    if (!empty($file_ids[0]) && $file = File::load($file_ids[0])) {
      $file->setPermanent();
      $file->save();

      $path = $file->getFileUri();

      // Save the file path in state for import processing
      \Drupal::state()->set('custom_phone_importer.file_path', $path);

      $this->messenger()->addStatus($this->t('File uploaded and saved for import.'));
    \Drupal::service('custom_phone_importer.importer')->run();
    }
    else {
      $this->messenger()->addError($this->t('File could not be loaded.'));
    }
  }

}
