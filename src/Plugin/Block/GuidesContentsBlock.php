<?php

namespace Drupal\localgov_guides\Plugin\Block;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
class GuidesContentsBlock extends GuidesAbstractBaseBlock implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->setPages();
    $links = [];
    $overviewOptions = [];

    if ($this->node->id() == $this->overview->id()) {
      $overviewOptions = ['attributes' => ['class' => 'active']];
      if (!$this->node->isPublished()) {
        $this->overview->localgov_guides_section_title->value .= ' ' . $this->t('(Unpublished)');
        $overviewOptions['attributes']['class'] = trim($overviewOptions['attributes']['class'] . ' unpublished');
      }
    }
    $links[] = $this->overview->toLink($this->overview->localgov_guides_section_title->value, 'canonical', $overviewOptions);

    foreach ($this->guidePages as $guide_node) {
      $options = $this->node->id() == $guide_node->id() ? ['attributes' => ['class' => 'active']] : [];
      if (!$guide_node->isPublished()) {
        $guide_node->localgov_guides_section_title->value .= ' ' . $this->t('(Unpublished)');
        $options['attributes']['class'] = trim($options['attributes']['class'] . ' unpublished');
      }
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
