<?php

namespace Drupal\news_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\news_importer\Service\NewsImportService;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class NewsImportForm extends FormBase implements ContainerInjectionInterface {

  protected $newsImportService;

  public function __construct(NewsImportService $newsImportService) {
    $this->newsImportService = $newsImportService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('news_importer.news_import_service')
    );
  }

  public function getFormId() {
    return 'news_import_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import News'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $count = $this->newsImportService->importNews();
    $this->messenger()->addMessage($this->t('@count articles imported.', ['@count' => $count]));
  }

}
