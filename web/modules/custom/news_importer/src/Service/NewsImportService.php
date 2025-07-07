<?php

namespace Drupal\news_importer\Service;

use Drupal\node\Entity\Node;

class NewsImportService {

  public function importNews(): int {
    $count = 0;

    $wscall = \Drupal::entityTypeManager()
      ->getStorage('wscall')
      ->load('newsapicall');

    if (!$wscall || !method_exists($wscall, 'call')) {
      return 0;
    }

    $articles = $wscall->call();

    if (!is_array($articles)) {
      return 0;
    }

    foreach ($articles as $article) {
      if (empty($article['title'])) {
        continue;
      }

      $existing = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['title' => $article['title']]);

      if (!empty($existing)) {
        continue;
      }

      $node = Node::create([
        'type' => 'news',
        'title' => $article['title'],
        'field_author' => $article['author'] ?? '',
        'field_description' => $article['description'] ?? '',
        'field_content' => $article['content'] ?? '',
        'field_image_url' => $article['urlToImage'] ?? '',
        'field_published_at' => !empty($article['publishedAt']) ? strtotime($article['publishedAt']) : NULL,
        'field_source' => $article['source']['name'] ?? '',
        'field_url' => $article['url'] ?? '',
        'status' => 1,
      ]);
      $node->save();
      $count++;
    }

    return $count;
  }

}
