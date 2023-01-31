<?php

namespace Drupal\dlaw_seo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class SeoReportController
 */
class SeoReportController extends ControllerBase {

  public function build() {
    $rows = [];

    // Site name ---------------------------------------------------------------
    $site_name = \Drupal::config('system.site')->get('name', '');
    $site_name_length = strlen($site_name);

    $status = ($site_name_length <= 60) ? 'Passed' : 'Needs work';

    $rows[] = [
      'data' => [
        t('Site Name'),
        t("<strong>$status</strong>"),
        t($site_name_length . ' characters'),
        t('Between 50 to 60 characters'),
        Link::createFromRoute('Edit', 'dlaw_dashboard.site_info', [], ['query' => \Drupal::service('redirect.destination')->getAsArray()]),
      ],
      'class' => array($status == 'Passed' ? 'bg-success' : 'bg-warning'),
    ];

    // Site slogan -------------------------------------------------------------
    $site_slogan = \Drupal::config('system.site')->get('slogan', '');
    $site_slogan_length = strlen($site_slogan);

    $status = ($site_slogan_length >= 70 and $site_slogan_length <= 110)
      ? 'Passed' : 'Needs work';

    $rows[] = [
      'data' => [
        t('Site Slogan'),
        t("<strong>$status</strong>"),
        t($site_slogan_length . ' characters'),
        t('Between 70 to 110 characters'),
        Link::createFromRoute('Edit', 'dlaw_dashboard.site_info', [], ['query' => \Drupal::service('redirect.destination')->getAsArray()]),
      ],
      'class' => array($status == 'Passed' ? 'bg-success' : 'bg-warning'),
    ];

    // Twitter account ---------------------------------------------------------
    $twitter = \Drupal::state()->get('dlaw_social_media_links_twitter_url', '');

    $status = $twitter ? 'Passed' : 'Needs work';

    $rows[] = [
      'data' => [
        t('Twitter Account'),
        t("<strong>$status</strong>"),
        t($twitter ? 'Linked' : 'Not set'),
        t('Create and add Twitter account'),
        Link::createFromRoute('Edit', 'dlaw_social_media_links.settings', [], ['query' => \Drupal::service('redirect.destination')->getAsArray()]),
      ],
      'class' => array($status == 'Passed' ? 'bg-success' : 'bg-warning'),
    ];

    // Facebook page -----------------------------------------------------------
    $facebook = \Drupal::state()->get('dlaw_social_media_links_facebook_url', '');

    $status = $facebook ? 'Passed' : 'Needs work';

    $rows[] = [
      'data' => [
        t('Facebook Page'),
        t("<strong>$status</strong>"),
        t($facebook ? 'Linked' : 'Not set'),
        t('Create and add Facebook page'),
        Link::createFromRoute('Edit', 'dlaw_social_media_links.settings', [], ['query' => \Drupal::service('redirect.destination')->getAsArray()]),
      ],
      'class' => array($status == 'Passed' ? 'bg-success' : 'bg-warning'),
    ];

    // Last Updated ------------------------------------------------------------
    $query = \Drupal::database()->select('node_field_data');
    $query->addExpression('MAX(changed)', 'changed_max');
    $time = $query->condition('status', 1)
      ->condition('type', 'page')
      ->execute()
      ->fetchField();

    $days_old = round((\Drupal::time()->getRequestTime() - $time)/86400);

    $status = ($days_old <= 90) ? 'Passed' : 'Needs work';

    $rows[] = [
      'data' => [
        t('Last Updated'),
        t("<strong>$status</strong>"),
        'Latest page is ' . \Drupal::translation()->formatPlural($days_old, '1 day', '@count days') . ' old',
        t('Update website at least every 90 days'),
        Link::createFromRoute('Check', 'system.admin_content', [], ['query' => \Drupal::service('redirect.destination')->getAsArray()]),
      ],
      'class' => array($status == 'Passed' ? 'bg-success' : 'bg-warning'),
    ];

    // Link checker ------------------------------------------------------------
    $ignore_response_codes = preg_split('/(\r\n?|\n)/', \Drupal::config('linkchecker.settings')->get('error.ignore_response_codes'));

    $query = \Drupal::database()->select('linkchecker_link', 'll');//->extend('PagerDefault');
    $query->fields('ll', []);
    $query->condition('ll.last_check', 0, '>');
    $query->condition('ll.status', 1);
    $query->condition('ll.code', $ignore_response_codes, 'NOT IN');

    $count = $query->countQuery()->execute()->fetchField();

    if ($count == 0) {
      $status = 'Passed';

      $result = 'No pages with broken links';
    }
    else {
      $status = 'Review';

      $count = $count > 10 ? (intdiv($count, 10) * 10) . '+': $count;

      $result = "There are $count broken links";
    }


    $rows[] = [
      'data' => [
        t('Broken External Link Check'),
        t("<strong>$status</strong>"),
        t($result),
        t('Fix broken links'),
        Link::fromTextAndUrl('Check', Url::fromUserInput('/admin/dashboard/seo/linkchecker')),
      ],
      'class' => array($status == 'Passed' ? 'bg-success' : 'bg-warning'),
    ];


    $build['table'] = [
      '#type' => 'table',
      '#header' => [
        t('Checkpoint'),
        t('Status'),
        t('Result'),
        t('Recommendation'),
        t(''),
      ],
      '#rows' => $rows,
      '#attached' => [
        'library' => ['dlaw_seo/dlaw_seo']
      ],
    ];

    return $build;
  }
}
