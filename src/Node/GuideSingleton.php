<?php

namespace Drupal\bhcc_guide\Node;

use Drupal\bhcc_helper\Node\NodeBase;
use Drupal\bhcc_service_info\RelatedLinksInterface;
use Drupal\bhcc_service_info\RelatedTopicsInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\node\Entity\Node;

/**
 * Class GuideSingleton
 *
 * @package Drupal\bhcc_guide\Node
 */
class GuideSingleton extends NodeBase {

  /**
   * We inherit the parents title.
   *
   * @return mixed|string|null
   */
  public function getPageTitle() {
    return $this->getParent()->getPageTitle();
  }

  /**
   * We inherit the parents description.
   *
   * @return bool
   */
  public function getPageDescription() {
    return $this->getParent()->getPageDescription();
  }

  /**
   * Gets guide section title.
   *
   * @return bool
   */
  public function getGuideSectionTitle() {
    if (!$this->get('field_guide_section_title')->isEmpty()) {
      return $this->get('field_guide_section_title')->first()->getValue()['value'];
    }

    return false;
  }

  /**
   * Returns related topic terms.
   *
   * @return array
   */
  public function getTopics() {
    $topics = [];
    foreach ($this->get('field_topic_term')->getValue() as $topic) {
      $topics[] = $topic;
    }

    return $topics;
  }

  /**
   * Fetch the guide master node.
   *
   * @return \Drupal\bhcc_guide\Node\GuideMaster|bool
   */
  public function getParent() {
    try {
      if (!$this->get('field_guide_parent')->isEmpty()) {
        $node = Node::load($this->get('field_guide_parent')->first()->getValue()['target_id']);

        if ($node instanceof GuideMaster) {
          return $node;
        }
      }
    }
    catch (MissingDataException $exception) {
      return false;
    }

    return false;
  }

  /**
   * Helper function for listing all guide pages.
   *
   * @return array
   */
  public function listGuidePages() {
    return $this->getParent()->listGuidePages();
  }

  /**
   * Gets the delta for the current page in the list of guide pages.
   *
   * @return bool|int|string
   */
  public function getGuidePageDelta() {
    foreach ($this->listGuidePages() as $delta => $page) {
      if ($page->id() === $this->id()) { return $delta; }
    }

    return false;
  }

  /**
   * Returns a Url object for the next guide page.
   *
   * @return \Drupal\Core\Url|bool
   */
  public function getPreviousGuideUrl() {
    if ($this->getGuidePageDelta() >= 1) {
      return $this->listGuidePages()[$this->getGuidePageDelta() - 1]->toUrl();
    }

    return false;
  }

  /**
   * Returns a Url object for the next guide page.
   *
   * @return \Drupal\Core\Url|bool
   */
  public function getNextGuideUrl() {
    if ($this->getGuidePageDelta()+1 < count($this->listGuidePages())) {
      return $this->listGuidePages()[$this->getGuidePageDelta() + 1]->toUrl();
    }

    return false;
  }
}
