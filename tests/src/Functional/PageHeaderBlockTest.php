<?php

namespace Drupal\Tests\localgov_guides\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;

/**
 * Tests page header block.
 *
 * @group localgov_guides
 */
class PageHeaderBlockTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'block',
    'path',
    'options',
    'localgov_guides',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_page_header_block');
    $this->drupalLogout();
  }

  /**
   * Tests that the page header block displays the overview title.
   *
   * This applies to Guide overview pages and Guide pages that are part of a
   * guide, with the fallback to the guide page title if no parent.
   */
  public function testGuidePageHeaderBlock() {
    $overview_title = 'Guide overview - ' . $this->randomMachineName(8);
    $overview = $this->createNode([
      'title' => $overview_title,
      'type' => 'localgov_guides_overview',
      'status' => NodeInterface::PUBLISHED,
      'body' => [
        'summary' => 'Lede to show',
        'value' => '',
      ],
    ]);

    $page_title = 'Guide page - ' . $this->randomMachineName(8);
    $page = $this->createNode([
      'title' => $page_title,
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);

    $orphan_title = 'Guide page - ' . $this->randomMachineName(8);
    $orphan = $this->createNode([
      'title' => $orphan_title,
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($overview->toUrl()->toString());
    $query = $this->xpath('.//h1[contains(concat(" ",normalize-space(@class)," ")," header ")]');
    $found_title = $query[0]->getText();
    $this->assertEquals($found_title, $overview_title);
    $this->assertSession()->responseContains('Lede to show');

    $this->drupalGet($page->toUrl()->toString());
    $query = $this->xpath('.//h1[contains(concat(" ",normalize-space(@class)," ")," header ")]');
    $found_title = $query[0]->getText();
    $this->assertEquals($found_title, $overview_title);
    $this->assertNotEquals($found_title, $page_title);

    $this->drupalGet($orphan->toUrl()->toString());
    $query = $this->xpath('.//h1[contains(concat(" ",normalize-space(@class)," ")," header ")]');
    $found_title = $query[0]->getText();
    $this->assertNotEquals($found_title, $overview_title);
    $this->assertEquals($found_title, $orphan_title);

    $new_overview_title = 'Guide overview - ' . $this->randomMachineName(8);
    $overview->set('title', $new_overview_title);
    $overview->save();

    $this->drupalGet($page->toUrl()->toString());
    $this->assertSession()->responseNotContains($overview_title);
    $query = $this->xpath('.//h1[contains(concat(" ",normalize-space(@class)," ")," header ")]');
    $found_title = $query[0]->getText();
    $this->assertEquals($found_title, $new_overview_title);

    // Check lede.
    $this->drupalGet($page->toUrl()->toString());
    $this->assertSession()->responseContains('Lede to show');
    // Remove body field, check title and no lede.
    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'localgov_guides_overview');
    $field_definitions['body']->delete();
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseNotContains('Lede to show');
    // @todo remove this.
    // Issue https://github.com/localgovdrupal/localgov_core/issues/290
    drupal_flush_all_caches();
    $this->drupalGet($page->toUrl()->toString());
    $this->assertSession()->responseNotContains('Lede to show');
    $query = $this->xpath('.//h1[contains(concat(" ",normalize-space(@class)," ")," header ")]');
    $found_title = $query[0]->getText();
    $this->assertEquals($found_title, $new_overview_title);
  }

}
