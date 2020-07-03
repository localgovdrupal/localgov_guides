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
    $pages = [];
    for ($i = 0; $i < 3; $i++) {
      $pages[] = $this->createNode([
        'title' => 'Guide page ' . $i,
        'type' => 'localgov_guides_page',
        'status' => NodeInterface::PUBLISHED,
        'localgov_guides_parent' => ['target_id' => $overview->id()],
      ]);
    }

    // Check overview.
    $this->drupalGet($overview->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertEquals(4, count($results));
    $this->assertContains('Guide overview', $results[0]->getText());
    $this->assertNotContains($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertContains('Guide page 0', $results[1]->getText());
    $this->assertContains($pages[0]->toUrl()->toString(), $results[1]->getHtml());
    $this->assertContains('Guide page 1', $results[2]->getText());
    $this->assertContains($pages[1]->toUrl()->toString(), $results[2]->getHtml());
    $this->assertContains('Guide page 2', $results[3]->getText());
    $this->assertContains($pages[2]->toUrl()->toString(), $results[3]->getHtml());

    // Check page.
    $this->drupalGet($pages[0]->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertEquals(4, count($results));
    $this->assertContains('Guide overview', $results[0]->getText());
    $this->assertContains($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertContains('Guide page 0', $results[1]->getText());
    $this->assertNotContains($pages[0]->toUrl()->toString(), $results[1]->getHtml());
    $this->assertContains('Guide page 1', $results[2]->getText());
    $this->assertContains($pages[1]->toUrl()->toString(), $results[2]->getHtml());
    $this->assertContains('Guide page 2', $results[3]->getText());
    $this->assertContains($pages[2]->toUrl()->toString(), $results[3]->getHtml());

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
