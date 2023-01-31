<?php

namespace Drupal\dlaw_sections\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *  id = "dlaw_section_directory",
 *  admin_label = @Translation("DLAW Section Directory"),
 * )
 */
class SectionDirectory extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($page = \Drupal::routeMatch()->getParameter('node')) {
      if ($directory = $this->directory($page)) {
        $top = $directory['top'];

        $build['top'] = [
          '#markup' => t(($directory['at_top'] ? '' : '&#x21E7; ')
            . Link::createFromRoute(
                $top->label(),
                'entity.node.canonical',
                ['node' => $top->id()],
                ['set_active_class' => TRUE] // Went to trouble to set "is-active" class.
              )->toString())
        ];

        $items = [];
        foreach ($directory['items'] as $item) {
          $items[] = Link::createFromRoute(
            $item->label(),
            'entity.node.canonical',
            ['node' => $item->id()],
            ['set_active_class' => TRUE] // Went to trouble to set "is-active" class.
          )->toString();
        }

        $build['items'] = [
          '#theme' => 'item_list',
          '#items' => $items,
        ];
      }
    }

    $build['#cache']['contexts'] = ['url'];

    return $build;
  }

  private function directory($page) {
    if ($section_items = $page->get('field_section')->referencedEntities()) {
      $section_top = $page;
      $at_top = TRUE;
    }
    else {
      $top_nid = \Drupal::database()->select('node__field_section', 's')
        ->fields('s', ['entity_id'])
        ->condition('field_section_target_id', $page->id())
        ->execute()
        ->fetchField();

      if ($top_nid && $section_top = Node::load($top_nid)) {
        $section_items = $section_top->get('field_section')->referencedEntities();

        $at_top = FALSE;
      }
    }

    if (!empty($section_top)) {
      return [
        'top'    => $section_top,
        'items'  => $section_items,
        'at_top' => $at_top
      ];
    }
  }

}
