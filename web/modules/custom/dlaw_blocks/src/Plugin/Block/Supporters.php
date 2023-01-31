<?php

namespace Drupal\dlaw_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *  id = "dlaw_blocks_supporters",
 *  admin_label = @Translation("Supporters"),
 * )
 */

class Supporters extends BlockBase {

  use HomeParagraphTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->getParagraph('supporters') ?? [];

    return $build;
  }

}
