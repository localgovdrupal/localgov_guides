<?php

namespace Drupal\localgov_guides\Plugin\Block;

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
    $layout_type = FALSE;

    if ($this->overview->hasField('localgov_guides_list_layout')) {
      $layout_type_field = $this->overview->get('localgov_guides_list_layout');
      if (!empty($layout_type_field) && isset($layout_type_field->getValue()[0])) {
        $layout_type = $layout_type_field->getValue()[0]['value'];
        $layout_type = str_replace('_', '-', $layout_type);
      }
    }

    $options = $this->node->id() == $this->overview->id() ? ['attributes' => ['class' => 'active']] : [];
    $links[] = $this->overview->toLink($this->overview->localgov_guides_section_title->value, 'canonical', $options);

    foreach ($this->guidePages as $guide_node) {
      $options = $this->node->id() == $guide_node->id() ? ['attributes' => ['class' => 'active']] : [];
      $links[] = $guide_node->toLink($guide_node->localgov_guides_section_title->value, 'canonical', $options);
    }

    $build = [];
    $build[] = [
      '#theme' => 'guides_contents_block',
      '#links' => $links,
      '#format' => $this->format,
    ];

    if ($layout_type) {
      $build['#attributes']['class'][] = 'block-localgov-guides-contents--' . $layout_type;
    }

    return $build;
  }

}
