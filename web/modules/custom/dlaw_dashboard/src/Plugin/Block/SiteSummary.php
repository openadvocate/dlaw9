<?php

namespace Drupal\dlaw_dashboard\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_dashboard_site_summary",
 *  admin_label = @Translation("Site Summary"),
 * )
 */
class SiteSummary extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $changed = \Drupal::database()->query("SELECT MAX(changed) FROM node_field_data WHERE status = 1 AND type = 'page'")->fetchField();
    $value = round((\Drupal::time()->getRequestTime() - $changed)/86400);
    if ($value > 90) {
      $value = '<span class="text-danger">' . number_format($value) . '</span>';
    }
    elseif ($value < 30) {
      $value = '<span class="text-success">' . number_format($value) . '</span>';
    }
    $data[] = [$value, 'Days since last update'];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node_field_data WHERE type = 'page' AND status = 1"),
      Link::createFromRoute('Total Published Pages', 'system.admin_content', [], ['query' => ['status' => '1']])->toString()
    ];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node_field_data WHERE type = 'page' AND status = 0"),
      Link::createFromRoute('Drafts', 'system.admin_content', [], ['query' => ['status' => '0']])->toString()
    ];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node_field_data WHERE type = 'page' AND promote = 1"),
      Link::createFromRoute('Promoted Pages', 'system.admin_content', [], ['query' => ['promote' => '1']])->toString()
    ];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node_field_data WHERE type = 'page' AND status = 1 AND FROM_UNIXTIME(changed) < TIMESTAMPADD(YEAR, -1, NOW())"),
      Link::createFromRoute('Pages more than 1 year old', 'system.admin_content', [], ['query' => ['type' => 'page', 'status' => '1', 'order' => 'changed', 'sort' => 'asc']])->toString()
    ];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node_field_data WHERE type = 'page' AND status = 1 AND FROM_UNIXTIME(changed) < TIMESTAMPADD(YEAR, -1, NOW())"),
      Link::createFromRoute('Pages more than 1 year old', 'system.admin_content', [], ['query' => ['type' => 'page', 'status' => '1', 'order' => 'changed', 'sort' => 'asc']])->toString()
    ];

    $data[] = '--';

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node__field_news WHERE field_news_value = 1"),
      'News'
    ];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node__field_date WHERE delta = 0"),
      'Events'
    ];

    $data[] = [
      $this->query("SELECT COUNT(DISTINCT entity_id) FROM node__field_section"),
      'Sections'
    ];

    $data[] = [
      $this->query("SELECT COUNT(*) FROM comment_field_data WHERE FROM_UNIXTIME(created) > TIMESTAMPADD(DAY, -30, NOW())"),
      'Comments in last 30 days'
    ];

    $data[] = '--';

    $data[] = [
      $this->query("SELECT COUNT(*) FROM node WHERE type = 'contact'"),
      Link::createFromRoute('Contacts', 'system.admin_content', [] , ['query' => ['type' => 'contact']])->toString()
    ];

    $data[] = '--';

    $sql = "SELECT COUNT(tid) FROM taxonomy_term_data td WHERE vid = :name";

    $data[] = [
      $this->query($sql, array(':name' => 'topics')),
      Link::createFromRoute('Topics', 'entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'topics'])->toString()
    ];

    $data[] = [
      $this->query($sql, array(':name' => 'tags')),
      Link::createFromRoute('Tags', 'entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'tags'])->toString()
    ];

    $data[] = [
      $this->query($sql, array(':name' => 'region')),
      Link::createFromRoute('Regions', 'entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'region'])->toString()
    ];

    $data[] = [
      $this->query($sql, array(':name' => 'zipcode')),
      Link::createFromRoute('ZIP Codes', 'entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'zipcode'])->toString()
    ];

    $data[] = [
      $this->query($sql, array(':name' => 'language')),
      Link::createFromRoute('Languages', 'entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'language'])->toString()
    ];

    $data[] = '--';

    $sql = "SELECT COUNT(u.uid) FROM users u JOIN user__roles ur ON u.uid = ur.entity_id WHERE ur.roles_target_id = :role";

    $data[] = [
      $this->query($sql, [':role' => 'manager']),
      'Managers'
    ];

    $data[] = [
      $this->query($sql, [':role' => 'editor']),
      'Editors'
    ];

    $list = [];
    foreach ($data as $item) {
      if ($item == '--') {
        $list[] = ['#markup' => '<hr>'];
      }
      else {
        $value = is_numeric($item[0]) ? number_format($item[0]) : $item[0];
        $label = $item[1];

        $list[] = ['#markup' => "<div class=\"summary-value\">{$value}</div><div class=\"summary-label\">{$label}</div>"];
      }
    }

    $build['title'] = [
      '#markup' => '<h2><i class="fa fa-tasks"></i> Website Summary</h2>'
    ];

    $build['summary'] = [
      '#theme' => 'item_list',
      '#items' => $list,
      '#attributes' => ['class' => ['site-summary', 'clearfix']],
    ];

    return $build;
  }

  private function query($query, $options = []) {
    return \Drupal::database()->query($query, $options)->fetchField();
  }
}
