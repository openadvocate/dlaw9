<?php

namespace Drupal\dlaw_node\Service;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Drupal\path_alias\AliasRepository;

class NodeAlias implements InboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $admin_paths = ['edit', 'feedback', 'votes', 'delete', 'node-rating', 'revisions', 'quick_clone'];
    $path_alias_repository = \Drupal::service('path_alias.repository');
    $pieces = explode('/' , $path);
    //Check whether or not the alias exists
    if (preg_match('#^/node/(\d+)#', $path, $match) && !$path_alias_repository->lookupByAlias($path, 'en') && !in_array(end($pieces), $admin_paths)) {
      if ($node = Node::load($match[1]) and $node->bundle() == 'page') {
        //Get registered alias for this node
        $path = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$match[1]);
      }
    }
    return $path;
  }
}