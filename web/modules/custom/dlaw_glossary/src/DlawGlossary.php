<?php

namespace Drupal\dlaw_glossary;

class DlawGlossary {

  const READCLEARLY_GLOSSARIES_URL = 'https://writeclearly.openadvocate.org/oarc/glossary_list.csv';

  public static function getGlossaries() {
    $csv_file = file(static::READCLEARLY_GLOSSARIES_URL);
    $csv = [];

    foreach (array_map('str_getcsv', $csv_file) as $item) {
      if (!empty($item[0])) {
        $label = explode('; ', $item[1], 2);
        $label = $label[0] . (!empty($label[1]) ? '<div>' . $label[1] . '</div>' : '');
        $csv[$item[0]] = $label;
      }
    }

    return $csv;
  }

  public static function showWidgetOnPage($node) {
    if (\Drupal::state()->get('dlaw.glossary.enabled', 0)) {
      $disabled_pages = \Drupal::state()->get('dlaw.glossary.disabled_pages', '');
      $disabled_pages = explode("\n", $disabled_pages);
      $disabled_pages = array_map('trim', $disabled_pages);
      $disabled_pages = array_filter($disabled_pages);

      if ($node->isNew() or !in_array('/node/' . $node->id(), $disabled_pages)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
