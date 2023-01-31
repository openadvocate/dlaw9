<?php

/**
 * @file
 * Contains \Drupal\dlaw_dashboard\Controller\RoutesController.
 */

namespace Drupal\dlaw_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;

class RoutesController extends ControllerBase {

  public function pageRenderer() {
    return [
      '#theme' => 'dashboard_home',
    ];
  }

}
