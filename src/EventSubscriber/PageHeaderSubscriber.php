<?php

namespace Drupal\localgov_guides\EventSubscriber;

use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\localgov_core\Event\PageHeaderDisplayEvent;

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
    if ($event->getEntity() instanceof Node &&
      $event->getEntity()->bundle() == 'localgov_guides_page' &&
      $event->getEntity()->localgov_guides_parent->entity
    ) {
      $overview = $event->getEntity()->localgov_guides_parent->entity;
      if (!empty($overview)) {
        $event->setTitle($overview->getTitle());
        if ($overview->get('body')->summary) {
          $event->setLede([
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => $overview->get('body')->summary,
          ]);
        }
      }
      else {
        $event->setLede('');
      }
    }
  }

}
