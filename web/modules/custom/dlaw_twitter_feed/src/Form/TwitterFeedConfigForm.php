<?php

namespace Drupal\dlaw_twitter_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class TwitterFeedConfigForm.
 *
 * @package Drupal\dlaw_twitter_feed\Form
 */
class TwitterFeedConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dlaw_twitter_feed.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dlaw_twitter_feed_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dlaw_twitter_feed.settings');

    $url = Url::fromUri('https://apps.twitter.com/');
    $link = Link::fromTextAndUrl('apps.twitter.com', $url);
    $link_renderable = $link->toRenderable();
    $form['tip'] = [
      '#markup' => $this->t('You can get this information by registering an app with Twitter on %link.', ['%link' => render($link_renderable)]),
    ];
    $form['dlaw_twitter_feed_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Also called "Consumer Key"'),
      '#required' => TRUE,
      '#maxlength' => 50,
      '#size' => 50,
      '#default_value' => $config->get('dlaw_twitter_feed_api_key'),
    ];
    $form['dlaw_twitter_feed_api_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Secret'),
      '#description' => $this->t('Also called "Consumer Secret"'),
      '#required' => TRUE,
      '#maxlength' => 50,
      '#size' => 50,
      '#default_value' => $config->get('dlaw_twitter_feed_api_secret'),
    ];

    $timeago_locales = dlaw_twitter_feed_timeago_languages();
    $timeago_locales = ['None: English'] + $timeago_locales;

    $form['dlaw_twitter_feed_jquery_timeago_locale'] = [
      '#type' => 'select',
      '#title' => $this->t('jQuery Timeago locale'),
      '#default_value' => !empty($config->get('dlaw_twitter_feed_jquery_timeago_locale')) ? $config->get('dlaw_twitter_feed_jquery_timeago_locale') : 0,
      '#options' => $timeago_locales,
      '#description' => $this->t('This is the locale file to be loaded from
      the jQuery timeago module. Located at timeago/locales.
      Changing this setting requires a cache rebuild to take effect.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dlaw_twitter_feed.settings')
      ->set('dlaw_twitter_feed_api_key', $form_state->getValue('dlaw_twitter_feed_api_key'))
      ->set('dlaw_twitter_feed_api_secret', $form_state->getValue('dlaw_twitter_feed_api_secret'))
      ->set('dlaw_twitter_feed_jquery_timeago_locale', $form_state->getValue('dlaw_twitter_feed_jquery_timeago_locale'))
      ->save();
  }

}
