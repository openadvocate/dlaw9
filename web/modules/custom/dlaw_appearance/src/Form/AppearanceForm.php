<?php

namespace Drupal\dlaw_appearance\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AppearanceForm.
 */
class AppearanceForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dlaw_appearance';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = array(
      '#markup' => '<h3>Choose Colour Options</h3><p>The colour you choose here will be used as the primary colour option for the website. Examples: polka dot background, primary buttons, active states for menu and table of content links, etc.</p>',
    );

    $options = [
      'maroon' => '<img src="http://placehold.jp/8D0101/ffffff/100x100.png?text=Maroon">',
      'royalblue' => '<img src="http://placehold.jp/024ac0/ffffff/100x100.png?text=Royal Blue">',
    ];

    $form['appearance_theme_palette'] = array(
      '#type' =>'radios',
      '#options' => $options,
      '#default_value' => \Drupal::state()->get('dlaw_appearance_theme_palette', 'red'),
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

    \Drupal::state()->set('dlaw_appearance_theme_palette', $values['appearance_theme_palette']);


  }

}
