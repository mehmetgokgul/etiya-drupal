<?php

namespace Drupal\custom_phone_importer\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;

/**
 * @QueueWorker(
 *   id = "phone_import_queue",
 *   title = @Translation("Phone Import Queue"),
 *   cron = {"time" = 60}
 * )
 */
class PhoneImportQueueWorker extends QueueWorkerBase {

  public function processItem($data) {
    try {
      // Product
      $product = Product::create([
        'type' => 'phone',
        'title' => $data['product_name'],
        'body' => [
          'value' => $data['product_description'],
          'format' => 'basic_html',
        ],
        'field_brand' => $data['brand'],
        'field_release_year' => $data['release_year'],
        'field_manufacturer_country' => $data['manufacturer_country'],
        'field_warranty_period' => $data['warranty_period'],
        'field_network_compatibility' => $data['network_compatibility'],
        'field_dual_sim' => $data['dual_sim'],
        'field_face_id' => $data['face_id'],
        'field_fingerprint_sensor' => $data['fingerprint_sensor'],
        'field_wireless_charging' => $data['wireless_charging'],
        'field_fast_charging' => $data['fast_charging'],
        'field_ip_rating' => $data['ip_rating'],
        'field_audio_jack' => $data['audio_jack'],
        'field_charging_port' => $data['charging_port'],
        'field_build_material' => $data['build_material'],
        'field_screen_refresh_rate' => $data['screen_refresh_rate'],
        'status' => $data['product_status'],
      ]);
      $product->save();

      foreach ($data['variations'] as $var) {
        $variation = ProductVariation::create([
          'type' => 'phone',
          'sku' => $var['sku'],
          'price' => [
            'number' => $var['price'],
            'currency_code' => 'TRY',
          ],
          'field_stock' => $var['stock'],
          'field_color' => $var['color']['val'],
          'field_color_hex' => $var['color']['hex'],
          'field_storage' => $var['storage']['val'] . ' ' . $var['storage']['val_unit'],
          'field_ram' => $var['ram']['val'] . ' ' . $var['ram']['val_unit'],
          'status' => $data['variation_status'],
        ]);

        // Image/media field 
        $media_references = $this->createMediaReferences($var['images'] ?? []);
        if (!empty($media_references)) {
          $variation->set('field_images', $media_references);
        }

        $variation->save();
        $product->addVariation($variation);
      }

      $product->save();

      \Drupal::logger('custom_phone_importer')->notice('Queued import completed for: @title', [
        '@title' => $data['product_name'],
      ]);
    }
    catch (\Exception $e) {
      \Drupal::logger('custom_phone_importer')->error('Queued import FAILED for: @title. Error: @msg', [
        '@title' => $data['product_name'],
        '@msg' => $e->getMessage(),
      ]);
    }
  }


  private function createMediaReferences(array $image_urls): array {
    $media_ids = [];
    $file_system = \Drupal::service('file_system');

    foreach ($image_urls as $image_url) {
      try {
        $image_data = file_get_contents($image_url);
        if (!$image_data) {
          continue;
        }

        $filename = basename($image_url);
        $uri = 'public://phone_imports/' . $filename;

        $destination = $file_system->saveData($image_data, $uri, FileSystemInterface::EXISTS_RENAME);
        if (!$destination) {
          continue;
        }

        $file = File::create([
          'uri' => $destination,
          'status' => 1,
        ]);
        $file->save();

        $media = Media::create([
          'bundle' => 'image',
          'name' => $filename,
          'field_media_image' => [
            'target_id' => $file->id(),
            'alt' => $filename,
          ],
          'status' => 1,
        ]);
        $media->save();

        $media_ids[] = ['target_id' => $media->id()];
      }
      catch (\Exception $e) {
        \Drupal::logger('custom_phone_importer')->error('Media import failed: @msg', [
          '@msg' => $e->getMessage(),
        ]);
      }
    }

    return $media_ids;
  }

}
