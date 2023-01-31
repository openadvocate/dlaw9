<?php

namespace Drupal\dlaw_sections\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *  id = "dlaw_section_prevnextnav",
 *  admin_label = @Translation("DLAW Prev/Next Navigation"),
 * )
 */
class SectionPrevNextNavigation extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($page = \Drupal::routeMatch()->getParameter('node')) {
      $top_nid = \Drupal::database()->select('node__field_section', 's')
        ->fields('s', ['entity_id'])
        ->condition('field_section_target_id', $page->id())
        ->execute()
        ->fetchField();

      if ($top_nid && $section_top = Node::load($top_nid)) {
        $section_items = $section_top->get('field_section')->referencedEntities();

        $stack = [];
        foreach ($section_items as $offset => $item) {
          if ($item->id() == $page->id()) {
            $current_offset = $offset;
            break;
          }
        }

        if (isset($section_items[$current_offset - 1])) {
          $prev = $section_items[$current_offset - 1]->toLink('Prev')->toString();
        }
        else {
          $prev = 'Prev';
        }

        if (isset($section_items[$current_offset + 1])) {
          $next = $section_items[$current_offset + 1]->toLink('Next')->toString();
        }
        else {
          $next = 'Next';
        }

        $build = [
          '#theme' => 'item_list',
          '#items' => [$prev, $next],
          '#cache' => [
            'contexts' => ['url']
          ]
        ];
      }
    }

    $build['#cache']['contexts'] = ['url'];

    return $build;
  }
}
