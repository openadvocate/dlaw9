<?php

namespace Drupal\dlaw_dashboard\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_dashboard_search_stats",
 *  admin_label = @Translation("Search Stats"),
 * )
 */
class SearchStats extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Search counts.
    $params = [
      'metrics' => ['ga:searchSessions'],
      'dimensions' => ['ga:date'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['ga:date'],
      'start_date' => strtotime('-30 days'),
      'end_date' => strtotime('-1 day'),
    ];

    $feed = google_analytics_reports_api_report_data($params);

    $search_count = $labels = $values = [];
    foreach ($feed->results->rows ?? [] as $row) {
      $labels[] = substr($row['date'], -2);
      $values[] = $row['searchSessions'];
    }
    $search_count['labels'] = join(',', $labels);
    $search_count['values'] = join(',', $values);

    // Top searches in the last 24 hours.
    $params = [
      'metrics' => ['ga:searchUniques'],
      'dimensions' => ['ga:searchKeyword'],
      'sort_metric' => ['-ga:searchUniques'],
      'start_date' => strtotime('-24 hours'),
      'end_date' => strtotime('now'),
      'max_results' => 25,
    ];

    $feed = google_analytics_reports_api_report_data($params);

    $search_1day = [];
    foreach ($feed->results->rows ?? [] as $row) {
      $search_1day[] = t($row['searchKeyword'] . ' <sup>' . $row['searchUniques'] . '</sup>');
    }

    // Top searches in the last 30 days.
    $params = [
      'metrics' => ['ga:searchUniques'],
      'dimensions' => ['ga:searchKeyword'],
      'sort_metric' => ['-ga:searchUniques'],
      'start_date' => strtotime('-30 days'),
      'end_date' => strtotime('now'),
      'max_results' => 50,
    ];

    $feed = google_analytics_reports_api_report_data($params);

    $search_30days = [];
    foreach ($feed->results->rows ?? [] as $row) {
      $search_30days[] = t($row['searchKeyword'] . ' <sup>' . $row['searchUniques'] . '</sup>');
    }

    $build = [
      '#theme' => 'dlaw_dashboard_search_stats',
      '#search_count' => $search_count,
      '#search_1day' => $search_1day,
      '#search_30days' => $search_30days,
    ];

    return $build;
  }
}
