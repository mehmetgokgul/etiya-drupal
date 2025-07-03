<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class EventRegistrationForm extends FormBase
{

    public function getFormId()
    {
        return 'event_registration_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['first_name'] = [
            '#type' => 'textfield',
            '#title' => 'First Name',
            '#required' => TRUE,
        ];

        $form['last_name'] = [
            '#type' => 'textfield',
            '#title' => 'Last Name',
            '#required' => TRUE,
        ];

        $form['phone'] = [
            '#type' => 'textfield',
            '#title' => 'Phone Number',
            '#required' => TRUE,
        ];

        $form['email'] = [
            '#type' => 'email',
            '#title' => 'Email Address',
            '#required' => TRUE,
        ];

        $form['date'] = [
            '#type' => 'date',
            '#title' => 'Date of Birth',
            '#required' => TRUE,
        ];

        $form['subscribe'] = [
            '#type' => 'checkbox',
            '#title' => 'Subscribe to Newsletter',
        ];

        $form['status'] = [
            '#type' => 'select',
            '#title' => 'Participation Status',
            '#options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            '#required' => TRUE,
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit',
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $first_name = $form_state->getValue('first_name');
        $last_name = $form_state->getValue('last_name');
        $phone = $form_state->getValue('phone');
        $email = $form_state->getValue('email');
        $date = $form_state->getValue('date');
        $subscribe = $form_state->getValue('subscribe') ? 1 : 0;
        $status = $form_state->getValue('status');

        $connection = Database::getConnection();
        $connection->insert('event_registration_participants')
        ->fields([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'email' => $email,
                'birth_date' => $date,
                'subscribe' => $subscribe,
                'status' => $status,
            ])
            ->execute();

        $this->messenger()->addMessage($this->t('Your registration has been submitted.'));
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $phone = $form_state->getValue('phone');
        $email = $form_state->getValue('email');
        $first_name = $form_state->getValue('first_name');
        $last_name = $form_state->getValue('last_name');

        if (!ctype_digit($phone)) {
            $form_state->setErrorByName('phone', "Phone number must contain only digits.");
        }
        if (strlen($phone) !== 11) {
            $form_state->setErrorByName("phone", "Phone number must be exactly 11 digits long.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_state->setErrorByName('email', 'Please enter a valid email address.');
        }

        if (!ctype_alpha(str_replace(' ', '', $first_name))) {
            $form_state->setErrorByName('first_name', 'First name must contain only letters.');
        }

        if (!ctype_alpha(str_replace(' ', '', $last_name))) {
            $form_state->setErrorByName('last_name', 'Last name must contain only letters.');
        }

    }

}