<?php

namespace Drupal\dlaw_404\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

class Custom404Controller extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $params = [
      'metrics' => ['ga:pageviews'],
      'dimensions' => ['ga:pagePath', 'ga:pageTitle'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['-ga:pageviews'],
      'start_date' => strtotime('-30 days'),
      'end_date' => strtotime('-1 day'),
      'sort' => '-ga:pageviews',
      'filters' => 'ga:pageTitle!=(not set)',
      'max_results' => 25,
    ];
    $feed = google_analytics_reports_api_report_data($params);

    $list = $duplicate_check = [];
    foreach ($feed->results->rows ?? [] as $row) {
      if ($row['pagePath'] == '/') continue;

      if (preg_match('#^/node/(\d+)#', $row['pagePath'], $match)) {
        if ($node = Node::load($match[1])) {
          if (!in_array($node->id(), $duplicate_check)) {
            $duplicate_check[] = $node->id();
            $list[] = $node->toLink();
          }
        }
      }
    }

    $build = [
      '#prefix' => '<div class="block__404">',
      '#suffix' => '</div>',
      'note' => [
        '#markup' => '<p>' . t('Please @search or explorer these popular pages.', ['@search' => Link::createFromRoute('search the website', 'view.search.search')->toString()]) . '</p>',
      ],
      'popular_pages' => [
        '#theme' => 'item_list',
        '#items' => $list,
      ],
      '#cache' => [
        'max-age' => 60 * 60 * 24
      ]
    ];

    return $build;
  }
}
