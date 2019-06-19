<?php

namespace Drupal\bhcc_guide\Node;

use Drupal\bhcc_helper\Node\NodeBase;
use Drupal\bhcc_service_info\RelatedLinksInterface;
use Drupal\bhcc_service_info\RelatedTopicsInterface;
use Drupal\node\Entity\Node;

/**
 * Class GuideMaster
 *
 * @package Drupal\bhcc_guide\Node
 */
class GuideMaster extends NodeBase {

  /**
   * Set page description.
   *
   * @return bool|string
   */
  public function getPageDescription() {
    return $this->getGuideDescription();
  }


  /**
   * The guide section title for guide master pages is always 'overview'
   *
   * @return string
   */
  public function getGuideSectionTitle() {
    return 'Overview';
  }

  /**
   * Get guide description.
   *
   * @return bool
   */
  public function getGuideDescription() {
    if (!$this->get('field_guide_description')->isEmpty()) {
      return $this->get('field_guide_description')->first()->getValue()['value'];
    }

    return false;
  }

  /**
   * Get all children pages.
   *
   * @return array
   */
  public function getChildren() {
    $children = [];
    foreach ($this->get('field_guide_pages')->getValue() as $page) {
      $children[] = Node::load($page['target_id']);
    }

    return $children;
  }

  /**
   * Add a new child page.
   *
   * @param \Drupal\bhcc_guide\Node\GuideSingleton $child
   *
   * @return $this
   */
  public function addChild(GuideSingleton $child) {
    if (!$this->hasChild($child)) {
      $this->set('field_guide_pages', $this->get('field_guide_pages')->getValue() + ['target_id' => $child->id()]);
    }

    return $this;
  }

  /**
   * Check if the guide already contains a page.
   *
   * @param \Drupal\bhcc_guide\Node\GuideSingleton $child
   *
   * @return bool
   */
  public function hasChild(GuideSingleton $child) {
    foreach ($this->get('field_guide_pages')->getValue() as $item) {
      if ($item['target_id'] == $child->id()) { return true; }
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
   * A complete collection of pages in this guide.
   *
   * @return array
   */
  public function listGuidePages() {
    return array_merge([$this], $this->getChildren());
  }

  /**
   * Returns a Url object for the next guide page.
   *
   * @return \Drupal\Core\Url|bool
   */
  public function getNextGuideUrl() {
    if (count($this->listGuidePages()) >= 2) {
      return $this->listGuidePages()[1]->toUrl();
    }

    return false;
  }
}
