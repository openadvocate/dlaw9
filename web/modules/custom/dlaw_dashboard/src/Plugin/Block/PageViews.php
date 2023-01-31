<?php

namespace Drupal\dlaw_dashboard\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_dashboard_page_views",
 *  admin_label = @Translation("Page Views"),
 * )
 */
class PageViews extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Pull GA data: Site Usage ************************************************
    // Get last 30 days.
    $params = [
      'metrics' => ['ga:sessions', 'ga:users', 'ga:pageviews'],
      'start_date' => strtotime('-30 days'),
      'end_date' => strtotime('-1 day'),
    ];

    $feed = google_analytics_reports_api_report_data($params);
    $result = $feed->results->rows[0] ?? NULL;

    if (empty($result)) {
      return ['#theme' => 'dlaw_dashboard_page_views'];
    }

    // Get 30 days before past 30 days for comparison.
    $params = [
      'metrics' => ['ga:sessions', 'ga:users', 'ga:pageviews'],
      'start_date' => strtotime('-60 days'),
      'end_date' => strtotime('-31 day'),
    ];

    $feed = google_analytics_reports_api_report_data($params);
    $result_prev = $feed->results->rows[0];

    // Visits.
    $site_usage['sessions']['count'] = number_format($result['sessions']);
    $change = round(($result['sessions'] - $result_prev['sessions'])/$result_prev['sessions']*100, 1);
    $site_usage['sessions']['change'] = ($change >= 0 ? '+' : '') . $change;

    // Unique Visitors.
    $site_usage['users']['count'] = number_format($result['users']);
    $change = round(($result['users'] - $result_prev['users'])/$result_prev['users']*100, 1);
    $site_usage['users']['change'] = ($change >= 0 ? '+' : '') . $change;

    // Pages/Visit.
    $page_visit = $result['pageviews'] / $result['sessions'];
    $page_visit_prev = $result_prev['pageviews'] / $result_prev['sessions'];

    $site_usage['page_visit']['count'] = round($page_visit, 1);
    $change = round(($page_visit - $page_visit_prev)/$page_visit_prev*100, 1);
    $site_usage['page_visit']['change'] = ($change >= 0 ? '+' : '') . $change;

    // Pull GA data: Page Views ************************************************
    $params = [
      'metrics' => ['ga:pageviews'],
      'dimensions' => ['ga:date'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['ga:date'],
      'start_date' => strtotime('-30 days'),
      'end_date' => strtotime('-1 day'),
    ];

    $feed = google_analytics_reports_api_report_data($params);

    $labels = $values = [];
    foreach ($feed->results->rows as $row) {
      $labels[] = date('j', strtotime($row['date']));
      $values[] = (int)$row['pageviews'];
    }

    if (empty($feed->error)) {
      $page_views = [
        'labels' => join(', ', $labels),
        'values' => join(', ', $values),
      ];
    }

    $build = [
      '#theme' => 'dlaw_dashboard_page_views',
      '#site_usage' => $site_usage,
      '#page_views' => $page_views,
    ];

    return $build;
  }
}
