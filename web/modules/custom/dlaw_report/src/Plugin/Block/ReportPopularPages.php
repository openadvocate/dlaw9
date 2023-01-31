<?php

namespace Drupal\dlaw_report\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_report_popular_pages",
 *  admin_label = @Translation("Report Popular Pages"),
 * )
 */
class ReportPopularPages extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $start_time = strtotime('1/1 -1 year');
    $end_time   = strtotime('1/1 this year');
    $last_year  = date('Y', strtotime('-1 year'));
    $prev_year  = date('Y', strtotime('-2 years'));

    // Pull GA data for Most Visited Pages
    $params = [
      'metrics' => ['ga:pageviews'],
      'dimensions' => ['ga:pagePath', 'ga:pageTitle'],
      'segment' => 'gaid::-1',
      'sort_metric' => ['-ga:pageviews'],
      'start_date' => $start_time,
      'end_date' => $end_time,
      'sort' => '-ga:pageviews',
      'filters' => 'ga:pageTitle!=(not set)',
      'max_results' => 100,
    ];

    $feed = google_analytics_reports_api_report_data($params);

    $list = $counts = $titles = [];
    foreach ($feed->results->rows ?? [] as $row) {
      if ($row['pagePath'] == '/') {
        $title = 'Home page';
      }
      else {
        list($title, ) = explode(' | ', $row['pageTitle']);
      }

      if (isset($counts[$row['pagePath']])) {
        $counts[$row['pagePath']] += $row['pageviews'];
      }
      else {
        $counts[$row['pagePath']] = $row['pageviews'];
        $titles[$row['pagePath']] = $this->cleanUpTitle($row['pagePath'], $title);
      }
    }

    arsort($counts);
    $counts = array_slice($counts, 0, 50);

    foreach ($counts as $path => $views) {
      $list[] = t('<span class="ga-link">' . Link::fromTextAndUrl($titles[$path], Url::fromUserInput($path))->toString() . '</span>' . '<span class="ga-num">' . number_format($views) . '</span>');
    }

    $build['most_visited'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ol',
      '#items' => $list,
      '#attributes' => [],
      '#prefix' => '<h2 class=""><i class="fa fa-desktop"></i> Most Visited Pages in ' . $last_year . '</h2>',
    ];


    // Pull GA data for Referral Traffic Sources.
    $params = [
      'metrics' => ['ga:sessions'],
      'dimensions' => ['ga:source', 'ga:medium'],
      'sort_metric' => ['-ga:sessions'],
      'filters' => 'ga:medium==referral',
      'start_date' => $start_time,
      'end_date' => $end_time,
      'max_results' => 25,
    ];

    $feed = google_analytics_reports_api_report_data($params);

    $list = [];
    foreach ($feed->results->rows ?? [] as $row) {
      $list[] = t('<span class="ga-link">' . $row['source'] . '</span>' . '<span class="ga-num">' . number_format($views) . '</span>');
    }

    $build['referral_traffic'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ol',
      '#items' => $list,
      '#attributes' => [],
      '#prefix' => '<h2 class=""><i class="fa fa-truck"></i> Referral Traffic Sources in ' . $last_year . '</h2>',
    ];


    // Pull GA data for Google searches.
    $params = [
      'metrics' => ['ga:searchUniques'],
      'dimensions' => ['ga:searchKeyword'],
      'sort_metric' => ['-ga:searchUniques'],
      'start_date' => $start_time,
      'end_date' => $end_time,
      'max_results' => 25,
    ];

    $feed = google_analytics_reports_api_report_data($params);

    // If empty result (because the search at GA was configured recently).
    if (empty($feed->results->rows)) {
      $params['end_date'] = strtotime('now');
      $feed = google_analytics_reports_api_report_data($params);
    }

    $list = [];
    foreach ($feed->results->rows ?? [] as $row) {
      $list[] = t('<span class="ga-link">' . $row['searchKeyword'] . '</span><span class="ga-num">' . number_format($row['searchUniques']) . '</span>');
    }

    $build['google_searches'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ol',
      '#items' => $list,
      '#attributes' => [],
      '#prefix' => '<h2 class=""><i class="fa fa-globe"></i> Google Searches in ' . $last_year . '</h2>',
    ];

    return $build;
  }

  private function cleanUpTitle($path, $title) {
    if (preg_match('#^/node/(\d+)#', $path, $match)) {
      if ($node = Node::load($match[1])) {
        return $node->label();
      }
    }

    return $title;
  }

}
