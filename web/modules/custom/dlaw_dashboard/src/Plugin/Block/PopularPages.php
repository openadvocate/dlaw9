<?php

namespace Drupal\dlaw_dashboard\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_dashboard_popular_pages",
 *  admin_label = @Translation("Popular Pages"),
 * )
 */
class PopularPages extends BlockBase {

  protected const RATE_BAD  = 1;
  protected const RATE_OK   = 2;
  protected const RATE_GOOD = 3;

  /**
   * Feedback count breakdown for the ranked pages.
   *
   * @var array
   */
  protected $votes;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $pages = [];

    $params = [
      'metrics' => ['ga:pageviews'],
      'dimensions' => ['ga:pagePath', 'ga:pageTitle'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['-ga:pageviews'],
      'start_date' => strtotime('-30 days'),
      'end_date' => strtotime('-1 day'),
      'sort' => '-ga:pageviews',
      'filters' => 'ga:pageTitle!=(not set)',
      'max_results' => 41,
    ];
    $feed = google_analytics_reports_api_report_data($params);

    if (!empty($feed->results->rows)) {
      $this->setVoteSummary($feed->results->rows);

      foreach ($feed->results->rows as $row) {
        if ($row['pagePath'] == '/' or strpos($row['pagePath'], '/search') === 0) {
          continue;
        }

        // Node
        $node = NULL;
        if (preg_match('#^/node/(\d+)#', $row['pagePath'], $match)) {
          $node = Node::load($match[1]);
        }

        // Node title: Get the real page title, not what GA reports.
        if ($node) {
          $page['title'] = $node->toLink();
        }
        // Topic term title
        elseif (preg_match('#^/topics#', $row['pagePath'], $match)) {
          list($title, ) = explode(' | ', $row['pageTitle']);
          $page['title'] = Link::fromTextAndUrl($title, Url::fromUserInput($row['pagePath']));
        }
        else {
          list($title, ) = explode(' | ', $row['pageTitle']);
          $page['title'] = $title;
        }

        // Updated time.
        $ago = '--';
        $changed_color = 'text-muted';
        if ($node) {
          $interval = \Drupal::time()->getRequestTime() - $node->getChangedTime();
          $ago = \Drupal::service('date.formatter')->formatInterval($interval, 1) . ' ago';
          if ($interval > 86400 * 365) {
            $changed_color = 'text-danger';
          }
          elseif ($interval < 86400 * 90) {
            $changed_color = 'text-success';
          }
        }
        $page['changed'] = $ago;
        $page['changed_color'] = $changed_color;

        // Page views
        $rank_1_views = empty($rank_1_views) ? $row['pageviews'] : $rank_1_views;
        $page['views'] = number_format($row['pageviews']);
        $page['views_percent'] = round($row['pageviews'] / $rank_1_views * 100, 2);

        // Feedback
        $rate_good = $rate_ok = $rate_bad = '';
        $rate_good_percent = $rate_ok_percent = $rate_bad_percent = '';
        $rate_total = $tooltip = '';

        // Upperbound to scale bar chart. Use first row's view count.
        $rate_max = (int)($rank_1_views * 0.75);

        if ($node) {
          $rates = $this->getVotes($node->id());

          if ($rate_total = $rates[static::RATE_BAD] + $rates[static::RATE_OK] + $rates[static::RATE_GOOD]) {
            $rate_bad  = $rates[static::RATE_BAD];
            $rate_ok   = $rates[static::RATE_OK];
            $rate_good = $rates[static::RATE_GOOD];
            $scale = $rate_total / $rate_max * 0.9;

            $rate_good_percent = round(($bar1 = $rate_good/$rate_total*100) * $scale, 2);
            $rate_ok_percent   = round((($bar2 = $rate_ok/$rate_total*100) + $bar1) * $scale, 2);
            $rate_bad_percent  = round(($rate_bad/$rate_total*100 + $bar1 + $bar2) * $scale, 2);

            $tooltip = "Feedback:\n- Very Helpful: $rate_good\n- Somewhat Helpful: $rate_ok\n- Not Helpful: $rate_bad";
          }
        }

        $page['tooltip'] = $tooltip;
        $page['rate'] = [
          'total' => $rate_total ? number_format($rate_total) : '',
          'good'  => ['count' => $rate_good, 'percent' => $rate_good_percent],
          'ok'    => ['count' => $rate_ok,   'percent' => $rate_ok_percent],
          'bad'   => ['count' => $rate_bad,  'percent' => $rate_bad_percent],
        ];

        $pages[] = $page;
      }
    }

    $build['title'] = [
      '#markup' => '<h2><i class="fa fa-globe"></i> Most Visited Pages in last 30 days</h2>'
    ];

    $build['popular_pages'] = [
      '#theme' => 'dlaw_dashboard_popular_pages',
      '#pages' => $pages,
    ];

    return $build;
  }

  // Get votes for all passed node ids in a single query.
  private function setVoteSummary($rows) {
    $nids = [];

    foreach ($rows as $row) {
      if (preg_match('#^/node/(\d+)#', $row['pagePath'], $match)) {
        $nids[] = $match[1];
      }
    }

    // value:
    //   1 = Not helpful (Bad)
    //   2 = Somewhat helpful (Ok)
    //   3 = Very helpful (Good)
    $query = \Drupal::database()->select('votingapi_vote', 'v');
    $query->addExpression("CONCAT(entity_id, '-', value)");
    $query->addExpression('COUNT(value)');
    $query->condition('entity_id', $nids ?: [0], 'IN');
    $query->groupBy('entity_id');
    $query->groupBy('value');

    $this->votes = $query->execute()->fetchAllKeyed();
  }

  private function getVotes($nid) {
    return [
      static::RATE_BAD  => $this->votes[$nid . '-1'] ?? 0,
      static::RATE_OK   => $this->votes[$nid . '-2'] ?? 0,
      static::RATE_GOOD => $this->votes[$nid . '-3'] ?? 0,
    ];
  }

}
