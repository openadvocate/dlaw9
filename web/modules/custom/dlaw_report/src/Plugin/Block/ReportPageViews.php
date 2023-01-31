<?php

namespace Drupal\dlaw_report\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_report_page_views",
 *  admin_label = @Translation("Report Page Views"),
 * )
 */
class ReportPageViews extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $last_year  = date('Y', strtotime('-1 year'));
    $prev_year  = date('Y', strtotime('-2 years'));

    $params = [
      'metrics' => ['ga:pageviews'],
      'dimensions' => ['ga:month'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['ga:month'],
      'start_date' => strtotime('1/1 -1 year'),
      'end_date' => strtotime('1/1 this year'),
    ];
    $feed = google_analytics_reports_api_report_data($params);

    $data_last_year = $data_prev_year = ['01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0,
      '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0];

    foreach ($feed->results->rows ?? [] as $row) {
      $data_last_year[$row['month']] = (int)$row['pageviews'];
    }

    // Get 1 year before last year.
    $params = [
      'metrics' => ['ga:pageviews'],
      'dimensions' => ['ga:month'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['ga:month'],
      'start_date' => strtotime('1/1 -2 years'),
      'end_date' => strtotime('1/1 -1 year'),
    ];
    $feed = google_analytics_reports_api_report_data($params);

    foreach ($feed->results->rows ?? [] as $row) {
      $data_prev_year[$row['month']] = (int)$row['pageviews'];
    }

    $data = [
      'prev_year' => [
        'label' => $prev_year,
        'views' => join(', ', array_values($data_prev_year)),
      ],
      'last_year' => [
        'label' => $last_year,
        'views' => join(', ', array_values($data_last_year)),
      ],
    ];

    $build = [
      '#theme' => 'dlaw_report_page_views',
      '#data' => $data,
    ];

    return $build;
  }

}
