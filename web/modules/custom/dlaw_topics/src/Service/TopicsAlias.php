<?php

namespace Drupal\dlaw_topics\Service;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;

class TopicsAlias implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if (preg_match('#^/topics/(\d+)#', $path, $match)) {
      $path = '/taxonomy/term/' . $match[1];
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (preg_match('#^/taxonomy/term/(\d+)$#', $path, $match)) {
      if ($term = Term::load($match[1]) and $term->bundle() == 'topics') {
        $path = "/topics/{$match[1]}/" . \Drupal::service('pathauto.alias_cleaner')->cleanString($term->label());
      }
    }

    return $path;
  }

}
