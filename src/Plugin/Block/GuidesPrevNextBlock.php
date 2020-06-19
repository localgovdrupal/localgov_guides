<?php

namespace Drupal\localgov_guides\Plugin\Block;

/**
 * Provides a 'GuidesPrevNextBlock' block.
 *
 * @Block(
 *  id = "localgov_guides_prev_next_block",
 *  admin_label = @Translation("Guides prev next block"),
 * )
 */
class GuidesPrevNextBlock extends GuidesAbstractBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->setPages();
    $previous_url = '';
    $next_url = '';

    if ($this->node->bundle() == 'localgov_guides_overview' and count($this->guidePages) > 0) {
      $next_url = $this->guidePages[0]->toUrl();
    }

    if ($this->node->bundle() == 'localgov_guides_page') {
      $page_delta = array_search(['target_id' => $this->node->id()], $this->overview->localgov_guides_pages->getValue());
      if (!empty($this->guidePages[$page_delta - 1])) {
        $previous_url = $this->guidePages[$page_delta - 1]->toUrl();
      }
      else {
        $previous_url = $this->overview->toUrl();
      }
      if (!empty($this->guidePages[$page_delta + 1])) {
        $next_url = $this->guidePages[$page_delta + 1]->toUrl();
      }
    }

    $build = [];
    $build[] = [
      '#theme' => 'guides_prev_next_block',
      '#previous_url' => $previous_url,
      '#next_url' => $next_url,
    ];

    return $build;
  }

}
