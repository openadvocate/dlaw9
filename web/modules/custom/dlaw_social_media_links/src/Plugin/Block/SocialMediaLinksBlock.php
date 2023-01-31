<?php

namespace Drupal\dlaw_social_media_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *  id = "dlaw_social_media_links",
 *  admin_label = @Translation("Social Media Links Block"),
 * )
 */
class SocialMediaLinksBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $socialLinks = [];

    $socialLinks['twitter'] = \Drupal::state()->get('dlaw_social_media_links_twitter_url', '');

    $socialLinks['facebook'] = \Drupal::state()->get('dlaw_social_media_links_facebook_url', '');

    $socialLinks['youtube'] = \Drupal::state()->get('dlaw_social_media_links_youtube_url', '');

    $socialLinks['vimeo'] = \Drupal::state()->get('dlaw_social_media_links_vimeo_url', '');

    $socialLinks['flickr'] = \Drupal::state()->get('dlaw_social_media_links_flickr_url', '');

    $socialLinks['linkedin'] = \Drupal::state()->get('dlaw_social_media_links_linkedin_url', '');

    $socialLinks['instagram'] = \Drupal::state()->get('dlaw_social_media_links_instagram_url', '');

    $socialLinks['pinterest'] = \Drupal::state()->get('dlaw_social_media_links_pinterest_url', '');

    $build = [
      '#theme' => 'social_media_links_block',
      '#social_links' => $socialLinks
    ];

    return $build;
  }
}
