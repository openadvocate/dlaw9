<?php

namespace Drupal\dlaw_twig_extension;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\media\OEmbed\ResourceException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DlawTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'dlaw_twig_extension';
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('oembed', [$this, 'oembed']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('dlaw_block_content', [$this, 'blockContent']),
    ];
  }

  /**
   * Convert video URL to iframed video via oembed.
   *
   * @param string $url
   *   URL of the remote video.
   *
   * @return string
   *   <iframe> tag containing the remote video.
   *
   * @see borrowed code from Drupal\media\Plugin\Field\FieldFormatter\OEmbedFormatter
   */
  public function oembed($url) {
    if (empty($url)) return '';

    $urlResolver = \Drupal::service('media.oembed.url_resolver');
    $resourceFetcher = \Drupal::service('media.oembed.resource_fetcher');
    $iFrameUrlHelper = \Drupal::service('media.oembed.iframe_url_helper');
    $renderer = \Drupal::service('renderer');
    $config =  \Drupal::service('config.factory')->get('media.settings');

    $max_width = $max_height = 0;

    try {
      $resource_url = $urlResolver->getResourceUrl($url);

      $resource = $resourceFetcher->fetchResource($resource_url);
    }
    catch (ResourceException $exception) {
      return t("Could not retrieve the remote URL (@url).", ['@url' => $url]);
    }

    $url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => $url,
        'max_width' => $max_width,
        'max_height' => $max_height,
        'hash' => $iFrameUrlHelper->getHash($url, $max_width, $max_height),
      ],
    ]);

    // Render videos and rich content in an iframe for security reasons.
    // @see: https://oembed.com/#section3
    $build = [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'data-src' => $url->toString(),
        'frameborder' => 0,
        'scrolling' => FALSE,
        'allowtransparency' => TRUE,
        'width' => $max_width ?: $resource->getWidth(),
        'height' => $max_height ?: $resource->getHeight(),
        'class' => ['media-oembed-content', 'lozad'],
      ],
      '#attached' => [
        'library' => [
          'media/oembed.formatter',
        ],
      ],
    ];

    CacheableMetadata::createFromObject($resource)
      ->addCacheTags($config->getCacheTags())
      ->applyTo($build);

    return $renderer->render($build);
  }

  /**
   * Get block content by "Block Description" as using Twig Tweak's drupal_block()
   * requires uuid, which may be different from environment to environemnt. It
   * is recommended to use machine name style (lower snake_case) for Block
   * description to indicate it is functional, not decorative.
   *
   * Example: contextual-region class is added in output.
   *   {{ lh_block_content('topics_page_header') }}
   *
   * @param string $block_name
   *   Block description.
   * @return render array
   */
  public function blockContent($block_name) {
    if ($block = $this->getBlockContentId($block_name)) {
      $twig_tweak = \Drupal::service('twig_tweak.twig_extension');

      // ':' at the end needed to avoid PHP Notice.
      // See https://www.drupal.org/project/twig_tweak/issues/3134193
      $id = "block_content:block_content=" . $block->id. ':';
      $contextual_links = $twig_tweak->drupalContextualLinks($id);

      $wrapper_class = [];
      if (!empty($contextual_links['#id'])) {
        $build['contextual_links'] = $contextual_links;
        $wrapper_class[] = 'contextual-region';
      }

      $wrapper_class[] = str_replace('_', '-', $block_name) . '__wrapper';
      $build['contextual_links']['#prefix'] = '<div class="' . join(' ', $wrapper_class) . '">';

      $build['content'] = $twig_tweak->drupalBlock('block_content:' . $block->uuid);
      $build['content']['#suffix'] = '</div>';

    }

    return $build ?? [];
  }

  /**
   * Map Block description to numeric block ID. Since the block can change
   * environment to environment, we use Block description as a unique identifier.
   *
   * @param string $block_name
   *   Block description.
   * @return object
   *   Contains block id and uuid.
   */
  protected function getBlockContentId($block_name) {
    $query = \Drupal::database()->select('block_content', 'b');
    $query->join('block_content_field_data', 'f', 'b.id = f.id');
    $query->fields('b', ['id', 'uuid']);
    $query->condition('f.info', $block_name);

    return $query->execute()->fetchObject();
  }

}
