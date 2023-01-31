<?php

namespace Drupal\dlaw_filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_dlaw_sslimg",
 *   title = @Translation("DLAW SSL image filter"),
 *   description = @Translation("Converts unsecure image URL to https."),
 *   settings = {
 *   },
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class SslImageFilter extends FilterBase {

  public function process($text, $langcode) {
    $text = preg_replace('#(src=[\'"])(http://)#', '${1}https://', $text);    

    return new FilterProcessResult($text);
  }

}
