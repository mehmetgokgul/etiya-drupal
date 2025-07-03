<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventRegistrationListController extends ControllerBase
{

  protected $database;

  public function __construct(Connection $database)
  {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database')
    );
  }

  public function list()
  {
    $header = [
      'id' => $this->t('ID'),
      'name' => $this->t('Full Name'),
      'phone' => $this->t('Phone Number'),
      'email' => $this->t('Email'),
      'birth_date' => $this->t('Date of Birth'),
      'subscribe' => $this->t('Newsletter'),
      'status' => $this->t('Status'),
      'edit' => $this->t('Edit'),
    ];

    $query = $this->database->select('event_registration_participants', 'e')
      ->fields('e', ['id', 'first_name', 'last_name', 'phone', 'email', 'birth_date', 'subscribe', 'status']);

    $results = $query->execute()->fetchAll();

    $rows = [];
    foreach ($results as $row) {
      $rows[] = [
        'id' => $row->id,
        'name' => $row->first_name . ' ' . $row->last_name,
        'phone' => $row->phone,
        'email' => $row->email,
        'birth_date' => $row->birth_date,
        'subscribe' => $row->subscribe ? 'Yes' : 'No',
        'status' => ucfirst($row->status),
        'edit' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('Edit'),
            '#url' => \Drupal\Core\Url::fromRoute('event_registration.edit', ['id' => $row->id]),
          ],
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No registrations found.'),
    ];
  }
}
