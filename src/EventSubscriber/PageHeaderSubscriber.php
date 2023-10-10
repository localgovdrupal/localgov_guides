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
    if (!empty($overview)) {
      $event->setTitle([
        // Replace direct call to getTitle() with an inline Twig template. With
        // this, we can provide the node and overview info to the header block
        // template.
        '#type' => 'inline_template',
        '#template' => '{{ title }}',
        '#context' => [
          'title' => $overview->getTitle(),
          'node' => $node,
          'overview' => $overview,
        ],
      ]);
      if ($overview->get('body')->summary) {
        $event->setLede([
          // localgov_drupal/localgov_base uses this render array's '#value'
          // property directly, so we can't remove it. But we can provide extra
          // data to templates in the contents of guide_data.
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $overview->get('body')->summary,
          'guide_data' => [
            '#node' => $node,
            '#overview' => $overview,
          ],
        ]);
      }
      $event->setCacheTags(Cache::mergeTags($node->getCacheTags(), $overview->getCacheTags()));
    }
    else {
      $event->setLede('');
    }
  }

}
