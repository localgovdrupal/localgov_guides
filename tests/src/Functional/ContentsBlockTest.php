<?php

namespace Drupal\Tests\localgov_guides\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;

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
  protected static $modules = [
    'block',
    'path',
    'options',
    'system',
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
    $this->drupalPlaceBlock('localgov_guides_contents');
    $this->drupalLogout();
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
    $this->assertSession()->responseNotContains('Guide overview');

    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('Guide page');

    $this->drupalGet($page->toUrl()->toString());
    $this->assertSession()->responseContains('Guide overview');

    $this->drupalGet($orphan->toUrl()->toString());
    $this->assertSession()->responseNotContains('Guide overview');
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
    $this->assertCount(4, $results);
    $this->assertStringContainsString('Guide overview', $results[0]->getText());
    $this->assertStringNotContainsString($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertStringContainsString('Guide page 0', $results[1]->getText());
    $this->assertStringContainsString($pages[0]->toUrl()->toString(), $results[1]->getHtml());
    $this->assertStringContainsString('Guide page 1', $results[2]->getText());
    $this->assertStringContainsString($pages[1]->toUrl()->toString(), $results[2]->getHtml());
    $this->assertStringContainsString('Guide page 2', $results[3]->getText());
    $this->assertStringContainsString($pages[2]->toUrl()->toString(), $results[3]->getHtml());

    // Check page.
    $this->drupalGet($pages[0]->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(4, $results);
    $this->assertStringContainsString('Guide overview', $results[0]->getText());
    $this->assertStringContainsString($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertStringContainsString('Guide page 0', $results[1]->getText());
    $this->assertStringNotContainsString($pages[0]->toUrl()->toString(), $results[1]->getHtml());
    $this->assertStringContainsString('Guide page 1', $results[2]->getText());
    $this->assertStringContainsString($pages[1]->toUrl()->toString(), $results[2]->getHtml());
    $this->assertStringContainsString('Guide page 2', $results[3]->getText());
    $this->assertStringContainsString($pages[2]->toUrl()->toString(), $results[3]->getHtml());

    // Check caching.
    $pages[] = $this->createNode([
      'title' => 'Guide page 3',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);
    $this->drupalGet($overview->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(5, $results);
    $this->assertSession()->responseContains('Guide page 3');
    // Change title.
    $pages[2]->title = 'New title page 2';
    $pages[2]->save();
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseNotContains('Guide page 2');
    $this->assertSession()->responseContains('New title page 2');

    // Another overview.
    $overview_2 = $this->createNode([
      'title' => 'Guide overview 2',
      'type' => 'localgov_guides_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    // Move a current revision to it.
    $pages[0]->localgov_guides_parent = ['target_id' => $overview_2->id()];
    $pages[0]->setNewRevision();
    $pages[0]->save();
    // Check overview.
    $this->drupalGet($overview->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(4, $results);
    $this->assertStringNotContainsString('Guide page 0', $results[1]->getText());
    // Check new overview.
    $this->drupalGet($overview_2->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(2, $results);
    $this->assertStringContainsString('Guide overview', $results[0]->getText());
    $this->assertStringNotContainsString($overview_2->toUrl()->toString(), $results[0]->getHtml());
    $this->assertStringContainsString('Guide page 0', $results[1]->getText());
    $this->assertStringContainsString($pages[0]->toUrl()->toString(), $results[1]->getHtml());

    // Unpublish a page.
    $pages[1]->status = NodeInterface::NOT_PUBLISHED;
    $pages[1]->save();
    // Still linked.
    $content_admin = $this->drupalCreateUser(['bypass node access']);
    $this->drupalLogin($content_admin);
    $this->drupalGet($overview->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(4, $results);
    $this->assertSession()->responseContains('Guide page 1');
    $this->drupalLogout();
    // But not for anon.
    $this->drupalGet($overview->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(3, $results);
    $this->assertSession()->responseNotContains('Guide page 1');

    // Delete page.
    $pages[3]->delete();
    $this->drupalGet($overview->toUrl()->toString());
    $xpath = '//ul[@class="progress"]/li';
    $results = $this->xpath($xpath);
    $this->assertCount(2, $results);
    $this->assertSession()->responseNotContains('Guide page 3');
  }

  /**
   * Test unpublished guide page links in contents block.
   */
  public function testUnpublishedGuidePageLinks(): void {
    // Create a guide overview.
    $overview = $this->createNode([
      'title' => 'Guide overview',
      'type' => 'localgov_guides_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Create published guide pages.
    $published_page = $this->createNode([
      'title' => 'Published guide page',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);

    // Create unpublished guide page.
    $unpublished_page = $this->createNode([
      'title' => 'Unpublished guide page',
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::NOT_PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $overview->id()],
    ]);

    // Test as anonymous user - unpublished pages should not appear.
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('Published guide page');
    $this->assertSession()->responseNotContains('Unpublished guide page');

    // Verify no data-drupal-is-unpublished attribute for anonymous users.
    $this->assertSession()->elementNotExists('css', '[data-drupal-is-unpublished]');

    // Test as authenticated user with content access permissions.
    $content_admin = $this->drupalCreateUser([
      'bypass node access',
      'access content',
    ]);
    $this->drupalLogin($content_admin);

    // Check overview page - both published and unpublished should appear.
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('Published guide page');
    $this->assertSession()->responseContains('Unpublished guide page');

    // Verify unpublished page has data-drupal-is-unpublished attribute.
    $this->assertSession()->elementExists('css', '[data-drupal-is-unpublished]');

    // Verify published page does not have the unpublished attribute.
    $published_link_xpath = '//a[contains(text(), "Published guide page")]/ancestor::li';
    $published_elements = $this->xpath($published_link_xpath);
    $this->assertCount(1, $published_elements);
    $this->assertFalse($published_elements[0]->hasAttribute('data-drupal-is-unpublished'));

    // Verify unpublished page has the unpublished attribute.
    $unpublished_link_xpath = '//li[@data-drupal-is-unpublished]';
    $unpublished_elements = $this->xpath($unpublished_link_xpath);
    $this->assertCount(1, $unpublished_elements);
    $this->assertStringContainsString('Unpublished guide page', $unpublished_elements[0]->getText());

    // Test from a guide page view.
    $this->drupalGet($published_page->toUrl()->toString());
    $this->assertSession()->responseContains('Guide overview');
    $this->assertSession()->responseContains('Published guide page');
    $this->assertSession()->responseContains('Unpublished guide page');

    // Verify unpublished attribute is present when viewing from guide page.
    $this->assertSession()->elementExists('css', '[data-drupal-is-unpublished]');

    // Test visibility changes when publishing status changes.
    $this->drupalLogout();

    // Publish the previously unpublished page.
    $unpublished_page->status = NodeInterface::PUBLISHED;
    $unpublished_page->save();

    // Check as anonymous user - should now see the page without
    // unpublished attribute.
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('Published guide page');
    $this->assertSession()->responseContains('Unpublished guide page');
    $this->assertSession()->elementNotExists('css', '[data-drupal-is-unpublished]');

    // Unpublish the previously published page.
    $published_page->status = NodeInterface::NOT_PUBLISHED;
    $published_page->save();

    // Check as anonymous user - should not see the unpublished page.
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseNotContains('Published guide page');
    $this->assertSession()->responseContains('Unpublished guide page');
  }

}
