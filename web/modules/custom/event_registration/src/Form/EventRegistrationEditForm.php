<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventRegistrationEditForm extends FormBase {

  
  public function getFormId() {
    return 'event_registration_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $connection = Database::getConnection();
    $record = $connection->select('event_registration_participants', 'e')
      ->fields('e', ['id', 'status'])
      ->condition('id', $id)
      ->execute()
      ->fetchObject();

    if (!$record) {
      $this->messenger()->addError($this->t('Participant not found.'));
      return [];
    }

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Participation Status'),
      '#options' => [
        'active' => $this->t('Active'),
        'inactive' => $this->t('Inactive'),
      ],
      '#default_value' => $record->status,
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $record->id,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('id');
    $status = $form_state->getValue('status');

    $connection = Database::getConnection();
    $connection->update('event_registration_participants')
      ->fields(['status' => $status])
      ->condition('id', $id)
      ->execute();

    $this->messenger()->addMessage($this->t('Participant status updated.'));
  }
}
