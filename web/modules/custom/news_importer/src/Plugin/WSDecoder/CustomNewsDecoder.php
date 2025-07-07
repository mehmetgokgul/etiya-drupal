<?php

namespace Drupal\news_importer\Plugin\WSDecoder;

use Drupal\wsdata\Plugin\WSDecoderBase;

/**
 * @WSDecoder(
 *   id = "custom_news_decoder",
 *   label = @Translation("Custom News Decoder")
 * )
 */
class CustomNewsDecoder extends WSDecoderBase {

  public function decode($data) {
    $decoded = json_decode($data, TRUE);
    if (!is_array($decoded)) {
      return [];
    }
    return $decoded['articles'] ?? [];
  }

}
