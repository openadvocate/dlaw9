<?php

/**
 * @file
 * Contains \Drupal\dlaw_report\Controller\ReportController.
 */

namespace Drupal\dlaw_report\Controller;

use Drupal\Core\Controller\ControllerBase;

class ReportController extends ControllerBase {

  public function pageRenderer() {
    return [
      '#theme' => 'dlaw_report',
    ];
  }

}
