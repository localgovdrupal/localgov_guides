<?php

namespace Drupal\localgov_guides\Plugin\PreviewLinkAutopopulate;

use Drupal\node\NodeInterface;
use Drupal\preview_link\PreviewLinkAutopopulatePluginBase;

/**
 * Auto-populate Guide preview links.
 *
 * @PreviewLinkAutopopulate(
 *   id = "localgov_guides",
 *   label = @Translation("Add all the pages for this guide"),
 *   description = @Translation("Add guide overview and page nodes to preview link."),
 *   supported_entities = {
 *     "node" = {
 *       "localgov_guides_overview",
 *       "localgov_guides_page",
 *     }
 *   },
 * )
 */
class Guides extends PreviewLinkAutopopulatePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPreviewEntities(): array {
    $overview = NULL;
    $guide_nodes = [];

    // Find guide overview.
    $node = $this->getEntity();
    if ($node->bundle() == 'localgov_guides_overview') {
      $overview = $node;
    }
    elseif ($node->bundle() == 'localgov_guides_page') {
      $overview = $node->get('localgov_guides_parent')->entity;
    }

    if ($overview instanceof NodeInterface) {
      $guide_nodes[] = $overview;

      // Find guide pages.
      $guide_pages = $overview->get('localgov_guides_pages')->referencedEntities();
      foreach ($guide_pages as $guide_page) {
        if ($guide_page instanceof NodeInterface && $guide_page->access('view')) {
          $guide_nodes[] = $guide_page;
        }
      }
    }

    return $guide_nodes;
  }

}
