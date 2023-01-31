<?php

namespace Drupal\dlaw_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *  id = "dlaw_blocks_testimonials",
 *  admin_label = @Translation("Testimonials"),
 * )
 */

class Testimonials extends BlockBase {

  use HomeParagraphTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->getParagraph('testimonials') ?? [];

    return $build;
  }

}
