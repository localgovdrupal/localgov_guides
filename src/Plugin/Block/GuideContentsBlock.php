<?php

namespace Drupal\bhcc_guide\Plugin\Block;

use Drupal\bhcc_helper\CurrentPage;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GuideContentsBlock
 *
 * @package Drupal\bhcc_guide\Plugin\Block
 *
 * @Block(
 *   id = "bhcc_guide_contents",
 *   admin_label = "Guide contents"
 * )
 */
class GuideContentsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var bool|\Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return \Drupal\bhcc_guide\Plugin\Block\GuideContentsBlock|\Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('bhcc_helper.current_page')
    );
  }

  /**
   * GuideContentsBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\bhcc_helper\CurrentPage $currentPage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentPage $currentPage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->node = $currentPage->getNode();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];

    // Get current node and store nid as variable currentNid
    $currentNode = \Drupal::routeMatch()->getParameter('node');
    if ($currentNode instanceof \Drupal\node\NodeInterface) {
      $currentNid = $currentNode->id();
    }

    $links = [];

    foreach ($this->node->listGuidePages() as $guide_node) {
      
      // Get nid of each node in listGuidePages object
      $guideNid = $guide_node->id();

      // If the nid of the guide page matches nid of current page, add 'active' class
      if ($guideNid == $currentNid) {
        $links[] = \Drupal\Core\Link::fromTextAndUrl(
          $guide_node->getGuideSectionTitle(), 
          \Drupal\Core\Url::fromRoute(
            'entity.node.canonical', 
            ['node' => $guideNid],
            ['attributes' => ['class' => 'active']]
          )
        );
      } 

      // Otherwise add without classes
      else {
        $links[] = \Drupal\Core\Link::fromTextAndUrl(
          $guide_node->getGuideSectionTitle(), 
          \Drupal\Core\Url::fromRoute(
            'entity.node.canonical', 
            ['node' => $guideNid]
          )
        );
      }

    }

    $format = $this->node->getListFormat();

    $build[] = [
      '#theme' => 'guide_contents',
      '#links' => $links,
      '#format' => $format
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {

    $guide_pages_cache_tags = $this->prepareCacheTags($this->node->listGuidePages());

    return Cache::mergeTags(parent::getCacheTags(), $guide_pages_cache_tags);
  }

  /**
   * Prepare cache tags for the given items.
   *
   * @param array $cacheable_items
   *   Array of Drupal\Core\Cache\CacheableDependencyInterface objects.
   */
  protected function prepareCacheTags(array $cacheable_items): array {

    $list_of_tag_collections = array_map(function (CacheableDependencyInterface $cacheable_item): array {
      return $cacheable_item->getCacheTags();
    }, $cacheable_items);

    $merged_tags = array_reduce($list_of_tag_collections, [Cache::class, 'mergeTags'], $initial = []);
    return $merged_tags;
  }

}
