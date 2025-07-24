<?php

namespace Drupal\custom_phone_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\media\Entity\Media;
use Drupal\Core\File\FileSystemInterface;

class PhoneImportForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'phone_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
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
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $file_ids = $form_state->getValue('phone_json_file');
    if (!empty($file_ids[0]) && $file = File::load($file_ids[0])) {
      $file->setPermanent();
      $file->save();

      $path = $file->getFileUri();
      $json_content = file_get_contents($path);
      $data = json_decode($json_content, TRUE);

      if (json_last_error() !== JSON_ERROR_NONE) {
        $this->messenger()->addError($this->t('Invalid JSON file.'));
        return;
      }

      $file_system = \Drupal::service('file_system');

      foreach ($data as $item) {
        // Product 
        $product = Product::create([
          'type' => 'phone',
          'title' => $item['product_name'],
          'body' => [
            'value' => $item['product_description'],
            'format' => 'basic_html',
          ],
          'field_brand' => $item['brand'],
          'field_release_year' => $item['release_year'],
          'field_manufacturer_country' => $item['manufacturer_country'],
          'field_warranty_period' => $item['warranty_period'],
          'field_network_compatibility' => $item['network_compatibility'],
          'field_dual_sim' => $item['dual_sim'],
          'field_face_id' => $item['face_id'],
          'field_fingerprint_sensor' => $item['fingerprint_sensor'],
          'field_wireless_charging' => $item['wireless_charging'],
          'field_fast_charging' => $item['fast_charging'],
          'field_ip_rating' => $item['ip_rating'],
          'field_audio_jack' => $item['audio_jack'],
          'field_charging_port' => $item['charging_port'],
          'field_build_material' => $item['build_material'],
          'field_screen_refresh_rate' => $item['screen_refresh_rate'],
          'status' => 1,
        ]);
        $product->save();

        foreach ($item['variations'] as $var) {
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
            'status' => 1,
          ]);

          // Images
          $image_ids = [];

          foreach ($var['images'] as $image_url) {
            try {
              $image_data = @file_get_contents($image_url);

              if ($image_data !== false) {
                $filename = basename($image_url);
                $uri = 'public://phone_imports/' . $filename;

                $destination = $file_system->saveData($image_data, $uri, FileSystemInterface::EXISTS_RENAME);

                if ($destination) {
                  $file = File::create([
                    'uri' => $destination,
                    'status' => 1,
                  ]);
                  $file->save();

                  if ($file->id()) {
                    $media = Media::create([
                      'bundle' => 'image',
                      'name' => $filename,
                      'field_media_image' => [
                        'target_id' => $file->id(),
                        'alt' => $var['sku'],
                      ],
                      'status' => 1,
                    ]);
                    $media->save();

                    if ($media->id()) {
                      $image_ids[] = ['target_id' => $media->id()];
                    }
                  }
                }
              }

            } catch (\Exception $e) {
              \Drupal::logger('custom_phone_importer')->error('Image import failed: @message', ['@message' => $e->getMessage()]);
            }
          }

          $valid_image_ids = [];

          foreach ($image_ids as $item) {
            if (!empty($item['target_id']) && Media::load($item['target_id'])) {
              $valid_image_ids[] = $item;
            }
          }

          if (!empty($valid_image_ids)) {
            $variation->set('field_images', $valid_image_ids);
          }



          $variation->save();
          $product->addVariation($variation);
        }

        $product->save();
      }

      $this->messenger()->addStatus($this->t('All phone products have been successfully imported.'));
    } else {
      $this->messenger()->addError($this->t('File could not be loaded.'));
    }
  }

}
