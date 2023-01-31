<?php

namespace Drupal\dlaw_topics\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * @Block(
 *  id = "dlaw_topics_navigation",
 *  admin_label = @Translation("Topics Navigation"),
 * )
 */
class TopicsNavigation extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $tree = [];
    $this->buildTree($tree, Term::load($this->getTopTermId()));

    $build = [
      '#theme' => 'item_list',
      '#items' => $tree,
      '#prefix' => '<h2>' . Link::createFromRoute('Topics', 'view.topics_category.page_topics')->toString() . '</h2>',
      '#cache' => [
        'contexts' => ['url']
      ]
    ];

    return $build;
  }

  /**
   * Populates a tree array given a taxonomy term tree.
   */
  private function buildTree(&$tree, $term) {
    $tid = $term->id();

    $tree[$tid]['#markup'] = Link::createFromRoute(
      $term->label(),
      'entity.taxonomy_term.canonical',
      ['taxonomy_term' => $tid],
      ['set_active_class' => TRUE] // Went to trouble to set "is-active" class.
    )->toString();

    $children = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadChildren($tid);
    
    usort($children, function ($item1, $item2) {
      return $item1->getWeight() <=> $item2->getWeight();
    });

    foreach ($children as $child) {
      $this->buildTree($tree[$tid]['children'], $child);
    }
  }

  private function getTopTermId() {
    $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
    $ancestors = \Drupal::entityTypeManager()->getStorage("taxonomy_term")->loadAllParents($tid);

    return array_key_last($ancestors);
  }
}
