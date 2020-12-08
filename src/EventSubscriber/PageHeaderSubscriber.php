<?php

namespace Drupal\localgov_guides\EventSubscriber;

use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\localgov_core\Event\PageHeaderDisplayEvent;

/**
 * Class PageHeaderSubscriber.
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

    // Guide overview.
    if ($event->getEntity() instanceof Node &&
      $event->getEntity()->bundle() == 'localgov_guides_overview'
    ) {
      if ($event->getEntity()->get('localgov_guides_description')->value) {
        $event->setLede([
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $event->getEntity()->get('localgov_guides_description')->value,
        ]);
      }
    }

    // Guide page.
    if ($event->getEntity() instanceof Node &&
      $event->getEntity()->bundle() == 'localgov_guides_page' &&
      $event->getEntity()->localgov_guides_parent->entity
    ) {
      $overview = $event->getEntity()->localgov_guides_parent->entity;
      if (!empty($overview)) {
        $event->setTitle($overview->getTitle());
        if ($overview->get('localgov_guides_description')->value) {
          $event->setLede([
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => $overview->get('localgov_guides_description')->value,
          ]);
        }
      }
      else {
        $event->setLede('');
      }
    }
  }

}
