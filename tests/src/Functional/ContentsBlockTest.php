<?php

namespace Drupal\Tests\localgov_guides\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests user blocks.
 *
 * @group localgov_guides
 */
class ContentsBlockTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'path',
    'options',
    'localgov_guides',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_guides_contents');
    $this->drupalLogout($this->adminUser);
  }

  /**
   * Tests that block is only visible on guide pages.
   */
  public function testContentsBlockVisibility() {
    $overview = $this->createNode([
      'title' => 'Guide overview',
      'type' => 'localgov_guides_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $page = $this->createNode([
      'title' => 'Guide page',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);

    $orphan = $this->createNode([
      'title' => 'Guide page',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet('node');
    $this->assertNoRaw('block-localgov-guides-contents');

    $this->drupalGet($overview->toUrl()->toString());
    $this->assertRaw('block-localgov-guides-contents');

    $this->drupalGet($page->toUrl()->toString());
    $this->assertRaw('block-localgov-guides-contents');

    $this->drupalGet($orphan->toUrl()->toString());
    $this->assertNoRaw('block-localgov-guides-contents');
  }

  /**
   * Test the contents list block.
   */
  public function testContentListBlock() {
    $overview = $this->createNode([
      'title' => 'Guide overview',
      'type' => 'localgov_guides_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->createNode([
      'title' => 'Guide page 1',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);
    $this->createNode([
      'title' => 'Guide page 2',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);
    $this->createNode([
      'title' => 'Guide page 3',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);

    $this->drupalGet($overview->toUrl()->toString());
    $this->assertText('Guide page 1');
    $this->assertText('Guide page 2');
    $this->assertText('Guide page 3');

    // Check caching.
    $this->createNode([
      'title' => 'Guide page 4',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertText('Guide page 4');
  }

}
