<?php

namespace Drupal\localgov_guides\Plugin\Block;

use Drupal\node\NodeInterface;

/**
 * Guide contents block.
 *
 * @package Drupal\localgov_guides\Plugin\Block
 *
 * @Block(
 *   id = "localgov_guides_contents",
 *   admin_label = "Guide contents"
 * )
 */
class GuidesContentsBlock extends GuidesAbstractBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->setPages();
    $links = [];

    foreach ($this->guidePages as $guide_node) {
      assert($guide_node instanceof NodeInterface);
      $options = $this->node->id() == $guide_node->id() ? ['attributes' => ['class' => 'active']] : [];
      $links[] = $guide_node->toLink($guide_node->localgov_guides_section_title->value, 'canonical', $options);
    }

    $build = [];
    $build[] = [
      '#theme' => 'guides_contents_block',
      '#links' => $links,
      '#format' => $this->format,
    ];

    return $build;
  }

}
