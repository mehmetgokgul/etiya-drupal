<?php

namespace Drupal\custom_phone_importer\Service;

use Drupal\Core\State\StateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

class PhoneImporter {

  protected StateInterface $state;
  protected ConfigFactoryInterface $configFactory;
  protected LoggerInterface $logger;



public function __construct(
  StateInterface $state,
  ConfigFactoryInterface $config_factory,
  LoggerChannelFactoryInterface $logger_factory
) {
  $this->state = $state;
  $this->configFactory = $config_factory;
  $this->logger = $logger_factory->get('custom_phone_importer');
}


  /**
   * Run the import process.
   */
  public function run(): string {
    $file_path = $this->state->get('custom_phone_importer.file_path');
  
    if (empty($file_path) || !file_exists($file_path)) {
      $this->logger->warning('No import file found or file is missing.');
      return 'No file to import.';
    }
  
    $json_content = file_get_contents($file_path);
    $data = json_decode($json_content, TRUE);
  
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
      $this->logger->error('Invalid JSON file at @path', ['@path' => $file_path]);
      return 'Invalid or empty JSON.';
    }
  
    $config = $this->configFactory->get('custom_phone_importer.settings');
    $product_status = $config->get('publish_products') ? 1 : 0;
    $variation_status = $config->get('publish_variations') ? 1 : 0;
  
    $queue = \Drupal::queue('phone_import_queue');
  
    foreach ($data as $item) {
      $queue_data = $item;
      $queue_data['product_status'] = $product_status;
      $queue_data['variation_status'] = $variation_status;
  
      $queue->createItem($queue_data);
      $this->logger->notice('Queued import item: @title', ['@title' => $item['product_name']]);
    }
  
    // State'i temizle (bir kez import edildi artık)
    $this->state->delete('custom_phone_importer.file_path');
  
    return 'Import items queued successfully.';
  }
  

}
