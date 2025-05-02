<?php

namespace Drupal\localgov_guides\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\localgov_core\Event\PageHeaderDisplayEvent;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Set page title.
 *
 * @package Drupal\localgov_guides\EventSubscriber
 */
class PageHeaderSubscriber implements EventSubscriberInterface {

  /**
   * PageHeaderSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository.
   */
  public function __construct(protected EntityRepositoryInterface $entityRepository) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PageHeaderDisplayEvent::EVENT_NAME => ['setPageHeader', 0],
    ];
  }

  /**
   * Set page title and lede.
   */
  public function setPageHeader(PageHeaderDisplayEvent $event) {

    $node = $event->getEntity();

    if (!$node instanceof NodeInterface) {
      return;
    }

    if ($node->bundle() !== 'localgov_guides_page') {
      return;
    }

    $overview = $node->localgov_guides_parent->entity ?? NULL;
    if ($overview instanceof NodeInterface) {
      $overview = $this->entityRepository->getTranslationFromContext($overview);
      $event->setTitle($overview->getTitle());
      if ($overview->hasField('body') && $overview->get('body')->summary) {
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

}
