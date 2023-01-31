<?php

namespace Drupal\dlaw_feedback\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * @Block(
 *  id = "dlaw_feedback_chart",
 *  admin_label = @Translation("Feedback Chart"),
 * )
 */
class FeedbackChart extends BlockBase {

  protected const HELPFUL1  = 1;
  protected const HELPFUL2   = 2;
  protected const HELPFUL3 = 3;

  protected const NOT_RELATED = 1;
  protected const NOT_ENOUGH  = 2;
  protected const NOT_CLEAR   = 3;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node_id = \Drupal::routeMatch()->getParameter('node');

    // How helpful: pie chart data
    $query = \Drupal::database()->select('votingapi_vote', 'v');
    $query->fields('v', ['value']);
    $query->addExpression('COUNT(value)');
    $query->condition('entity_id', $node_id);
    $query->groupBy('entity_id');
    $query->groupBy('value');

    $rates = $query->execute()->fetchAllKeyed();
    $rates[static::HELPFUL1] ??= 0;
    $rates[static::HELPFUL2] ??= 0;
    $rates[static::HELPFUL3] ??= 0;
    $total = array_sum($rates);

    $label = [];
    $label[] = "'" . round($rates[static::HELPFUL1]/$total*100, 0) . "% Not helpful'";
    $label[] = "'" . round($rates[static::HELPFUL2]/$total*100, 0) . "% Somewhat helpful'";
    $label[] = "'" . round($rates[static::HELPFUL3]/$total*100, 0) . "% Very helpful'";
    $how_helpful['labels'] = join(',', $label);

    $count = [];
    $count[] = $rates[static::HELPFUL1] ?? 0;
    $count[] = $rates[static::HELPFUL2] ?? 0;
    $count[] = $rates[static::HELPFUL3] ?? 0;
    $how_helpful['counts'] = join(',', $count);

    // Why not helpful: pie chart data
    $query = \Drupal::database()->select('comment_field_data', 'c');
    $query->join('comment__field_why_unhelpful', 'why', 'c.cid = why.entity_id');
    $query->addField('why', 'field_why_unhelpful_value', 'reason');
    $query->addExpression('COUNT(why.field_why_unhelpful_value)', 'votes');
    $query->condition('c.entity_id', $node_id);
    $query->groupBy('reason');
    $query->orderBy('reason');

    $rates = $query->execute()->fetchAllKeyed();
    $rates[static::NOT_RELATED] ??= 0;
    $rates[static::NOT_ENOUGH] ??= 0;
    $rates[static::NOT_CLEAR] ??= 0;
    $total = array_sum($rates);

    $label = [];
    $label[] = "'" . round($rates[static::NOT_RELATED]/$total*100, 0) . "% Not related to my issue'";
    $label[] = "'" . round($rates[static::NOT_ENOUGH]/$total*100, 0) . "% Not enough information'";
    $label[] = "'" . round($rates[static::NOT_CLEAR]/$total*100, 0) . "% Unclear information'";
    $why_not_helpful['labels'] = join(',', $label);

    $count = [];
    $count[] = $rates[static::NOT_RELATED] ?? 0;
    $count[] = $rates[static::NOT_ENOUGH] ?? 0;
    $count[] = $rates[static::NOT_CLEAR] ?? 0;
    $why_not_helpful['counts'] = join(',', $count);

    $build = [
      '#theme' => 'dlaw_feedback_chart',
      '#how_helpful' => $how_helpful,
      '#why_not_helpful' => $why_not_helpful,
    ];

    return $build;
  }
}
