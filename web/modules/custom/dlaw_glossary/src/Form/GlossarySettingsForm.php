<?php

namespace Drupal\dlaw_glossary\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dlaw_glossary\DlawGlossary;

/**
 * Class GlossarySettingsForm.
 */
class GlossarySettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dlaw_glossary_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = array(
      '#type' => 'item',
      '#title' => t('ReadClearly Glossary'),
      '#markup' => '<p>OpenAdvocate ReadCleary is a free Plain Language glossary for legal services websites. Once enabled, it will display plain language explanations for complex legal terms on this website. <a href="http://openadvocate.org/readclearly" target="_blank" class="ext">Learn more.</a></p>',
    );

    $form['enable'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable ReadClearly Glossary on Pages'),
      '#description' => t('Each Page can be configured to override the site settings.'),
      '#default_value' => \Drupal::state()->get('dlaw.glossary.enabled', 0),
    ];

    if ($glossaries = DlawGlossary::getGlossaries()) {
      if (!$default_value = \Drupal::state()->get('dlaw.glossary.default', '')) {
        $default_value = key($glossaries);
      }

      $form['glossary'] = [
        '#type' => 'radios',
        '#title' => t('Glossary'),
        '#default_value' => $default_value,
        '#options' => $glossaries,
      ];
    }

    $form['custom_glossary'] = [
      '#type' => 'textfield',
      '#title' => t('Custom glossary'),
      '#default_value' => \Drupal::state()->get('dlaw.glossary.custom_glossary', ''),
      '#description' => t('Contact us if you need to build your custom glossary. Leave blank if you use one of the list above.'),
      '#maxlength' => 100,
    ];

    $form['theme'] = [
      '#type' => 'radios',
      '#title' => t('Theme'),
      '#default_value' => \Drupal::state()->get('dlaw.glossary.theme', 'default'),
      '#options' => [
        'default' => t('Default'),
        'neutral' => t('Neutral'),
      ],
    ];

    $form['location'] = [
      '#type' => 'radios',
      '#title' => t('Location'),
      '#default_value' => \Drupal::state()->get('dlaw.glossary.location', 'bottom-left'),
      '#options' => [
        'top-left' => t('Top left'),
        'top-right' => t('Top right'),
        'bottom-left' => t('Bottom left'),
        'bottom-right' => t('Bottom right'),
      ],
    ];

    $form['footnotes'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable Footnotes mode'),
      '#default_value' => \Drupal::state()->get('dlaw.glossary.footnotes', 0),
    );

    $form['disabled_pages'] = array(
      '#type' => 'textarea',
      '#title' => t('Disable glossary on these pages'),
      '#description' => t('By default, ReadClearly is enabled on all Pages. List system path (/node/2 instead of /node/2/about-us) each per line.'),
      '#default_value' => \Drupal::state()->get('dlaw.glossary.disabled_pages', ''),
      '#attributes' => ['placeholder' => '/example/path (should start with /)'],
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save settings',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    \Drupal::state()->setMultiple([
      'dlaw.glossary.enabled' => $values['enable'],
      'dlaw.glossary.default' => $values['glossary'],
      'dlaw.glossary.custom_glossary' => trim($values['custom_glossary']),
      'dlaw.glossary.theme' => $values['theme'],
      'dlaw.glossary.location' => $values['location'],
      'dlaw.glossary.footnotes' => $values['footnotes'],
    ]);

    // Clean up disabled pages into system paths.
    $disabled_pages = $values['disabled_pages'];
    $disabled_pages = explode("\n", $disabled_pages);
    $disabled_pages = array_map('trim', $disabled_pages);
    $disabled_pages = array_filter($disabled_pages);

    foreach ($disabled_pages as $key => $path) {
      $disabled_pages[$key] = \Drupal::service('path_alias.manager')->getPathByAlias($path);
    }
    \Drupal::state()->set('dlaw.glossary.disabled_pages', join("\n", $disabled_pages));
  }

}
