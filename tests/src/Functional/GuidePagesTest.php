<?php

namespace Drupal\Tests\localgov_guides\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;
use Drupal\node\NodeInterface;

/**
 * Tests localgov guide pages working together.
 *
 * @group localgov_guides
 */
class GuidePagesTest extends BrowserTestBase {

  use NodeCreationTrait;
  use AssertBreadcrumbTrait;

  /**
   * Test breadcrumbs in the Standard profile.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'localgov_core',
    'localgov_guides',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node fields',
    ]);
    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    // Place the guide navigation block.
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_guides_contents');
    $this->drupalLogout();
  }

  /**
   * Verifies basic functionality with all modules.
   */
  public function testConfigForm() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/structure/types/manage/localgov_guides_overview/fields');
    $this->assertSession()->pageTextContains('Guide pages');
    $this->assertSession()->pageTextContains('Guide section title');
    $this->assertSession()->pageTextContains('List format');
    $this->drupalGet('/admin/structure/types/manage/localgov_guides_page/fields');
    $this->assertSession()->pageTextContains('Parent page');
    $this->assertSession()->pageTextContains('Guide section title');
  }

  /**
   * Test adding unpublished guide pages via /node/add form.
   */
  public function testUnpublishedGuidePages() {
    $guide_overview_title = 'Guide overview - ' . $this->randomMachineName(8);
    $guide_summary_text = 'Aenean semper sodales augue. In volutpat quam id nisi accumsan scelerisque. Phasellus et dignissim arcu. Quisque vulputate ligula ac mauris consectetur bibendum. Phasellus ultrices velit ultrices efficitur sodales.';
    $guide_body_text = 'Vestibulum scelerisque viverra diam in cursus. Donec interdum eget tellus sed volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec tempus at neque vitae tempor. Aenean tristique elit id ultrices ornare. Morbi a mauris magna. Ut diam dui, venenatis non purus in, tincidunt aliquet diam. Maecenas a mattis sapien. Duis ultricies lacinia tortor, et interdum ante rhoncus id. Ut ultrices leo et dui aliquam placerat. Nullam egestas eros a lectus venenatis, vel mattis dolor consectetur. Sed ac mattis purus. Duis vulputate nisi nisl, a varius ligula accumsan non. Praesent sed ipsum nunc. Cras tincidunt, metus in commodo pulvinar, tortor nisi consequat est, ac porttitor orci eros id sem. Suspendisse rutrum risus arcu, quis placerat dolor pulvinar quis.';
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/node/add/localgov_guides_overview');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getSession()->getPage();
    $page->fillField('edit-title-0-value', $guide_overview_title);
    $page->fillField('edit-localgov-guides-section-title-0-value', $guide_overview_title);
    $page->fillField('edit-body-0-summary', $guide_summary_text);
    $page->fillField('edit-body-0-value', $guide_body_text);
    // Set node to be unpublished.
    $page->uncheckField('status[value]');
    $page->pressButton('Save');
    // Check we have a 200 and not a fatal error.
    // See https://github.com/localgovdrupal/localgov_guides/issues/159
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Guide overview ' . $guide_overview_title . ' has been created.');
    $this->drupalLogout();
    $this->drupalGet('/node/1');
    $this->assertSession()->statusCodeEquals(403);

    // Create a guide overview and couple of unpublished guide pages.
    $guide_page_title_1 = 'Guide page - ' . $this->randomMachineName(8);
    $guide_page_title_2 = 'Guide page - ' . $this->randomMachineName(8);
    $guide_overview_page = $this->createNode([
      'title' => $guide_overview_title,
      'localgov_guides_section_title' => $guide_overview_title,
      'type' => 'localgov_guides_overview',
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);
    $guide_page_1 = $this->createNode([
      'title' => $guide_page_title_1,
      'localgov_guides_section_title' => $guide_page_title_1,
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::NOT_PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $guide_overview_page->id()],
    ]);
    $guide_page_2 = $this->createNode([
      'title' => $guide_page_title_2,
      'localgov_guides_section_title' => $guide_page_title_2,
      'type' => 'localgov_guides_page',
      'status' => NodeInterface::NOT_PUBLISHED,
      'localgov_guides_parent' => ['target_id' => $guide_overview_page->id()],
    ]);
    $this->drupalGet($guide_overview_page->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($guide_page_1->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($guide_page_2->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(403);

    // Log in and publish the nodes.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($guide_overview_page->toUrl()->toString());
    $this->clickLink('Edit');
    $page = $this->getSession()->getPage();
    $page->checkField('status[value]');
    $page->pressButton('Save');

    $this->drupalGet($guide_page_1->toUrl()->toString());
    $this->clickLink('Edit');
    $page = $this->getSession()->getPage();
    $page->checkField('status[value]');
    $page->pressButton('Save');

    $this->drupalGet($guide_page_2->toUrl()->toString());
    $this->clickLink('Edit');
    $page = $this->getSession()->getPage();
    $page->checkField('status[value]');
    $page->pressButton('Save');

    $this->drupalLogout();

    // Confirm we can view the guide pages, now that they are published.
    $this->drupalGet($guide_overview_page->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($guide_overview_title);
    $this->drupalGet($guide_page_1->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($guide_page_title_1);
    $this->drupalGet($guide_page_2->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($guide_page_title_2);
  }

}
