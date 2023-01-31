<?php

namespace Drupal\dlaw_social_media_links\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SocialMediaLinksForm.
 */
class SocialMediaLinksForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dlaw_social_media_links';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['social_media_links_title'] = array(
      '#markup' => '<h3>Enter the</h3>',
    );

    $form['social_media_links_twitter_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_twitter_url', ''),
    );
    $form['social_media_links_facebook_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Facebook URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_facebook_url', ''),
    );
    $form['social_media_links_youtube_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Youtube URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_youtube_url', ''),
    );
    $form['social_media_links_vimeo_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Vimeo URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_vimeo_url', ''),
    );
    $form['social_media_links_flickr_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Flickr URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_flickr_url', ''),
    );
    $form['social_media_links_linkedin_url'] = array(
      '#type' => 'textfield',
      '#title' => t('LinkedIn URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_linkedin_url', ''),
    );
    $form['social_media_links_instagram_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Instagram URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_instagram_url', ''),
    );
    $form['social_media_links_pinterest_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Pinterest URL'),
      '#default_value' => \Drupal::state()->get('dlaw_social_media_links_pinterest_url', ''),
    );
    /*$form['social_media_links_rss_url'] = array(
      '#type' => 'textfield',
      '#title' => t('RSS Feed URL'),
      '#default_value' => variable_get('dlaw_social_media_links_rss_url', 'http://' . $_SERVER['SERVER_NAME'] . '/rss.xml'),
      '#description' => t('Leave this field blank to use default RSS feed url (http://' . $_SERVER['SERVER_NAME'] . '/rss.xml)'),
    );*/

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

    \Drupal::state()->set('dlaw_social_media_links_twitter_url', $values['social_media_links_twitter_url']);

    \Drupal::state()->set('dlaw_social_media_links_facebook_url', $values['social_media_links_facebook_url']);

    \Drupal::state()->set('dlaw_social_media_links_youtube_url', $values['social_media_links_youtube_url']);

    \Drupal::state()->set('dlaw_social_media_links_vimeo_url', $values['social_media_links_vimeo_url']);

    \Drupal::state()->set('dlaw_social_media_links_flickr_url', $values['social_media_links_flickr_url']);

    \Drupal::state()->set('dlaw_social_media_links_linkedin_url', $values['social_media_links_linkedin_url']);

    \Drupal::state()->set('dlaw_social_media_links_instagram_url', $values['social_media_links_instagram_url']);

    \Drupal::state()->set('dlaw_social_media_links_pinterest_url', $values['social_media_links_pinterest_url']);
  }

}
