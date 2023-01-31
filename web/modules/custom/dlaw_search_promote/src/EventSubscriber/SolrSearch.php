<?php

namespace Drupal\dlaw_search_promote\EventSubscriber;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\google_analytics\Event\PagePathEvent;
use Drupal\google_analytics\Constants\GoogleAnalyticsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds Drupal Messages to GA Javascript.
 *
 * This is altered version of google_analytics/src/EventSubscriber/PagePath/Search.php
 * google_analytics only supports Drupal core search.
 * This version works for SOLR search.
 *  - Uncheck Google Analytics "Track internal search" checkbox unless you need it.
 *  - Search page route name should be "view.search.search" or update code accordingly.
 *  - Search query parameter is "s" or update code accordingly.
 */
class SolrSearch implements EventSubscriberInterface {

  /**
   * Drupal Config Factory
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * @var \GuzzleHttp\Psr7\Request
   */
  protected $request;

  /**
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute;

  /**
   * DrupalMessage constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory for Google Analytics Settings.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request, ModuleHandler $module_handler, CurrentRouteMatch $current_route) {
    $this->config = $config_factory->get('google_analytics.settings');
    $this->request = $request->getCurrentRequest();
    $this->moduleHandler = $module_handler;
    $this->currentRoute = $current_route;

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[GoogleAnalyticsEvents::PAGE_PATH][] = ['onCustomPagePath'];
    return $events;
  }

  /**
   * Adds a new event to the Ga Javascript
   *
   * @param \Drupal\google_analytics\Event\PagePathEvent $event
   *   The event being dispatched.
   *
   * @throws \Exception
   */
  public function onCustomPagePath(PagePathEvent $event) {
    // Site search tracking support.
    if (($this->currentRoute->getRouteName() == 'view.search.search') && $keys = ($this->request->query->has('s') ? trim($this->request->get('s')) : '')) {

      $pager_manager = \Drupal::service('pager.manager');
      if ($pager_manager->getPager() and $items = $pager_manager->getPager()->getTotalItems()) {
        $url_custom = Json::encode(Url::fromRoute('view.search.search', [], ['query' => ['search' => $keys]])
          ->toString());
      }
      else {
        $url_custom = Json::encode(Url::fromRoute('view.search.search', [], [
            'query' => [
              'search' => 'no-results:' . $keys,
              'cat' => 'no-results'
            ]
        ])->toString());
      }

      $event->setPagePath($url_custom);
      $event->stopPropagation();
    }
  }
}
