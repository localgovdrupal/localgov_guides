<?php

/**
 * @file
 * Post update functions for LocalGov Guides.
 */

use Drupal\pathauto\Entity\PathautoPattern;
use Drupal\pathauto\PathautoPatternInterface;

/**
 * Update pathauto patterns to avoid inclusion of subdirectory, language, etc.
 */
function localgov_guides_post_update_pathauto_parent(): void {
  $alias = PathautoPattern::load('localgov_guides_page');
  if ($alias instanceof PathautoPatternInterface &&
    $alias->getPattern() == '[node:localgov_guides_parent:entity:url:relative]/[node:title]'
  ) {
    $alias->setPattern('[node:localgov_guides_parent:entity:url:path]/[node:title]');
    $alias->save();
  }
}
