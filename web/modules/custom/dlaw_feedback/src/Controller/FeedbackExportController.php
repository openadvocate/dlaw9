<?php

/**
 * @file
 * Contains \Drupal\dlaw_feedback\Controller\FeedbackExportController.
 */

namespace Drupal\dlaw_feedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class FeedbackExportController
 *
 * @package Drupal\dlaw_feedback\Controller
 */
class FeedbackExportController extends ControllerBase {

  /**
   * @return csv download
   */
  public function download() {
    $response = new StreamedResponse;

    $response->setCallback(function() {
      $handle = fopen("php://output", 'w+');

      $this->getData($handle);
    });

    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment;filename="feedback-' . date('Ymd-His') . '.csv"');

    return $response;
  }

  private function getData($handle) {
    fputcsv($handle, ['Date', 'Page', 'Feedback']);

    $query = \Drupal::database()->select('comment_field_data', 'c');
    $query->join('comment__comment_body', 'cb', 'c.cid = cb.entity_id');
    $query->fields('c', ['entity_id', 'created']);
    $query->addField('cb', 'comment_body_value', 'body');
    $query->condition('c.status', 1)
      ->orderBy('c.entity_id');

    $result = $query->execute();

    // Rows
    $prev_body = '';
    foreach ($result as $row) {
      if (strlen(trim($row->body)) < 10) continue;

      $date = date('Y-m-d H:i:s', $row->created);
      $page = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $row->entity_id);
      $body = $row->body;

      // Skip duplicate comments.
      if ($prev_body == $body) continue;
      $prev_body = $body;

      fputcsv($handle, [$date, $page, $body]);
    }
  }

}
