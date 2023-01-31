<?php

namespace Drupal\dlaw_report\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_report_summary",
 *  admin_label = @Translation("Report Summary"),
 * )
 */
class ReportSummary extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
//    if ('UA-0000000-00' == variable_get('googleanalytics_account', 'UA-0000000-00')) {
//      return 'Google Analytics not configured yet.';
//    }

    $start_time = strtotime('1/1 last year');
    $end_time   = strtotime('1/1 this year');
    $last_year  = date('Y', strtotime('last year'));
    $prev_year  = date('Y', strtotime('-2 years'));

    // Pull GA data for Site Usage and Summary.
    $params = [
      'metrics' => ['ga:sessions', 'ga:users', 'ga:pageviews', 'ga:avgSessionDuration', 'ga:bounceRate'],
      'start_date' => $start_time,
      'end_date' => $end_time,
    ];
    $feed = google_analytics_reports_api_report_data($params);
    $result = $feed->results->rows[0];

    // Get 1 year before last year.
    $params = [
      'metrics' => ['ga:sessions', 'ga:users', 'ga:pageviews', 'ga:avgSessionDuration', 'ga:bounceRate'],
      'start_date' => strtotime('1/1 -2 years'),
      'end_date' => strtotime('1/1 -1 year'),
    ];
    $feed = google_analytics_reports_api_report_data($params);
    $result_prev = $feed->results->rows[0];

    $rows = [];

    // Site Usage: Page views.
    $change = round(($result['pageviews'] - $result_prev['pageviews'])/$result_prev['pageviews']*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $summary_prev_data_exists = $result_prev['pageviews'] ? TRUE : FALSE;
    $summary_views = number_format($result['pageviews']);
    $summary_views_change = $change;

    $rows[] = [
      'Page Views',
      number_format($result_prev['pageviews']),
      number_format($result['pageviews']),
      $change . '%',
    ];

    // Site Usage: Sessions.
    $change = round(($result['sessions'] - $result_prev['sessions'])/$result_prev['sessions']*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $summary_sessions = number_format($result['sessions']);
    $summary_sessions_change = $change;

    $rows[] = [
      'Sessions',
      number_format($result_prev['sessions']),
      number_format($result['sessions']),
      $change . '%',
    ];

    // Site Usage: Average Length of Sessions
    $change = round(($result['avgSessionDuration'] - $result_prev['avgSessionDuration'])/$result_prev['avgSessionDuration']*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $rows[] = [
      'Average Length of Sessions',
      number_format($result_prev['avgSessionDuration'], 1) . ' sec',
      number_format($result['avgSessionDuration'], 1) . ' sec',
      $change . '%',
    ];

    // Site Usage: Bounce Rate
    $change = round(($result['bounceRate'] - $result_prev['bounceRate'])/$result_prev['bounceRate']*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $rows[] = [
      'Bounce Rate',
      number_format($result_prev['bounceRate'], 1) . '%',
      number_format($result['bounceRate'], 1) . '%',
      $change . '%',
    ];

    // Site Usage: Unique visitors
    $change = round(($result['users'] - $result_prev['users'])/$result_prev['users']*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $summary_unique_visitors = number_format($result['users']);
    $summary_visitor_change = $change;

    $rows[] = [
      'Unique Visitors',
      number_format($result_prev['users']),
      number_format($result['users']),
      $change . '%',
    ];

    // Site Usage: Pages per session
    $pps_prev = $result_prev['pageviews'] / $result_prev['sessions'];
    $pps = $result['pageviews'] / $result['sessions'];
    $change = round(($pps - $pps_prev)/$pps_prev*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $rows[] = [
      'Pages per Session',
      number_format($result_prev['pageviews'] / $result_prev['sessions'], 2),
      number_format($result['pageviews'] / $result['sessions'], 2),
      $change . '%',
    ];

    $build['site_usage'] = [
      '#type' => 'table',
      '#header' => [
        t('Metric'),
        $prev_year,
        $last_year,
        t('Change'),
      ],
      '#rows' => $rows,
      '#attributes' => [
        'class' => [],
      ],
      '#prefix' => '<h2 class=""><i class="fa fa-tachometer"></i> Site Usage</h2>',
      '#weight' => 20,
    ];


    // Pull GA data for Device stats.
    $params = [
      'metrics' => ['ga:sessions'],
      'dimensions' => ['ga:deviceCategory'],
      'start_date' => $start_time,
      'end_date' => $end_time,
    ];
    $feed = google_analytics_reports_api_report_data($params);

    $result = ['total' => 0];
    foreach ($feed->results->rows ?? [] as $res) {
      $result[$res['deviceCategory']] = $res['sessions'];
      $result['total'] += $res['sessions'];
    }

    // Get 1 year before last year.
    $params = [
      'metrics' => ['ga:sessions'],
      'dimensions' => ['ga:deviceCategory'],
      'start_date' => strtotime('1/1 -2 years'),
      'end_date' => strtotime('1/1 -1 year'),
    ];
    $feed = google_analytics_reports_api_report_data($params);

    $result_prev = ['total' => 0];
    foreach ($feed->results->rows ?? [] as $res) {
      $result_prev[$res['deviceCategory']] = $res['sessions'];
      $result_prev['total'] += $res['sessions'];
    }

    $rows = [];

    $percent_prev = $result_prev['desktop'] / $result_prev['total'] * 100;
    $percent = $result['desktop'] / $result['total'] * 100;
    $change = round(($percent - $percent_prev)/$percent_prev*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;
    $rows[] = [
      'Device: Desktop',
      round($percent_prev, 1) . '%',
      round($percent, 1) . '%',
      $change . '%',
    ];

    $percent_prev = $result_prev['mobile'] / $result_prev['total'] * 100;
    $percent = $result['mobile'] / $result['total'] * 100;
    $change = round(($percent - $percent_prev)/$percent_prev*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;

    $summary_device_mobile = round($percent, 1);

    $rows[] = [
      'Device: Mobile',
      round($percent_prev, 1) . '%',
      round($percent, 1) . '%',
      $change . '%',
    ];

    $percent_prev = $result_prev['tablet'] / $result_prev['total'] * 100;
    $percent = $result['tablet'] / $result['total'] * 100;
    $change = round(($percent - $percent_prev)/$percent_prev*100, 1);
    $change = ($change >= 0 ? '+' : '') . $change;
    $rows[] = [
      'Device: Tablet',
      round($percent_prev, 1) . '%',
      round($percent, 1) . '%',
      $change . '%',
    ];

    $build['devices'] = [
      '#type' => 'table',
      '#header' => [
        t('Metric'),
        $prev_year,
        $last_year,
        t('Change'),
      ],
      '#rows' => $rows,
      '#prefix' => '<h2 class=""><i class="fa fa-mobile"></i> Devices</h2>',
      '#weight' => 30,
    ];


    // Report summary at top.
    $summary_last_year = date('Y', strtotime('Jan 1 last year'));
    $summary_prev_year = date('Y', strtotime('Jan 1 -2 years'));
    $summary_domain = $_SERVER['HTTP_HOST'];
    if ($summary_prev_data_exists) {
      $summary_visitor_inc_dec = $summary_visitor_change > 0 ? 'increase' : 'decrease';
      $summary_views_inc_dec = $summary_views_change > 0 ? 'increase' : 'decrease';
      $summary_sessions_inc_dec = $summary_sessions_change > 0 ? 'increase' : 'decrease';

      $summary =<<<OUT
      <div class="card">
        <h2 class="">Report Summary</h2>
        <p>In {$summary_last_year} the {$summary_domain} website had {$summary_unique_visitors} unique visitors (a {$summary_visitor_change}% {$summary_visitor_inc_dec} over {$summary_prev_year}) who viewed {$summary_views} (a {$summary_views_change}% {$summary_views_inc_dec} over {$summary_prev_year}) in {$summary_sessions} sessions (a {$summary_sessions_change}% {$summary_sessions_inc_dec} over {$summary_prev_year}). The website received {$summary_device_mobile}% of its traffic from mobile devices in {$summary_last_year}.</p>
      </div>
OUT;
    }
    elseif ($summary_views) {
      $summary =<<<OUT
      <div class="card">
        <h4>Report Summary</h4>
        <p>In {$summary_last_year} the {$summary_domain} website had {$summary_unique_visitors} unique visitors who viewed {$summary_views} in {$summary_sessions} sessions. The website received {$summary_device_mobile}% of its traffic from mobile devices in {$summary_last_year}.</p>
      </div>
OUT;
    }
    else {
      // No collected data.
      $summary = '';
    }

    $build['summary'] = [
      '#markup' => $summary,
      '#weight' => 10,
    ];

    return $build;
  }

  private function yearlyData() {
    $params = [
      'metrics' => ['ga:sessions', 'ga:users', 'ga:pageviews', 'ga:avgSessionDuration', 'ga:bounceRate'],
      'start_date' => strtotime('1/1 -2 years'),
      'end_date' => strtotime('1/1 -1 year'),
    ];
    $feed = google_analytics_reports_api_report_data($params);

    $result_prev = $feed->results->rows[0] ?? NULL;

    // $summary_prev_data_exists = $result_prev['pageviews'] ? TRUE : FALSE;

    kint($result_prev);
  }
}
