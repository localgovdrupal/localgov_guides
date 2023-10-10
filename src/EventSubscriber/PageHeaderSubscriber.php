<?php

namespace Drupal\localgov_guides\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\localgov_core\Event\PageHeaderDisplayEvent;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Set page title.
 *
 * @package Drupal\localgov_guides\EventSubscriber
 */
class PageHeaderSubscriber implements EventSubscriberInterface {

  /**
   * @var Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function __construct($config_factory) {
    $this->settings = $config_factory->get('localgov_guides.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PageHeaderDisplayEvent::EVENT_NAME => ['setPageHeader', 0],
    ];
  }

  /**
   * Set page title and lede.
   */
  public function setPageHeader(PageHeaderDisplayEvent $event) {

    $node = $event->getEntity();

    if (!$node instanceof Node) {
      return;
    }

    if ($node->bundle() !== 'localgov_guides_page') {
      return;
    }

    $overview = $node->localgov_guides_parent->entity ?? NULL;
    if ($this->settings->get('legacy_header')) {
      // The legacy rendering uses overview content for Guide Page titles.
      if (!empty($overview)) {
        $event->setTitle($overview->getTitle());
        if ($overview->get('body')->summary) {
          $event->setLede([
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => $overview->get('body')->summary,
          ]);
        }
        $event->setCacheTags(Cache::mergeTags($node->getCacheTags(), $overview->getCacheTags()));
      }
      else {
        $event->setLede('');
      }
    }
    else {
      // The newer rendering uses the node's own content for Guide page titles.
      $event->setTitle([
        '#theme' => 'guides_page_header_title',
        '#title' => $node->getTitle(),
        '#node' => $node,
        '#overview' => $overview,
      ]);
      $event->setLede([
        '#theme' => 'guides_page_header_lede',
        '#lede' => $overview->getTitle(),
        '#node' => $node,
        '#overview' => $overview,
      ]);
    }
  }

}
