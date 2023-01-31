<?php

namespace Drupal\dlaw_blocks\Plugin\Block;

use Drupal\node\Entity\Node;

trait HomeParagraphTrait {

  private function getParagraph($para_type) {
    $site_config = \Drupal::config('system.site');
    $front_path = explode('/node/', $site_config->get('page.front'));

    if (!empty($front_path[1]) && is_numeric($front_path[1])) {
      if ($front = Node::load($front_path[1]) and $front->getType() == 'landing_page') {
        foreach ($front->get('field_lp_components') as $component) {
          if ($component->entity->bundle() == $para_type) {
            return \Drupal::service('twig_tweak.entity_view_builder')->build($component->entity);
          }
        }
      }
    }
  }

}
