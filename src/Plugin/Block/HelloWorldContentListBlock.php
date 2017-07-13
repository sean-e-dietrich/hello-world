<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Provides block with links to hello_world content.
 *
 * @Block(
 *   id = "hello_world_content_list",
 *   admin_label = @Translation("Hello World!")
 * )
 */
class HelloWorldContentListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Used to load entities.
   *
   * @var $entityManager \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Used to query entities.
   *
   * @var $entity_query \Drupal\Core\Entity\Query\QueryFactory;
   */
  protected $entityQuery;

  /**
   * HelloWorldContentListBlock constructor.
   *
   * @param array $configuration
   *    Configuration Factory.
   * @param string $plugin_id
   *    Plugin ID.
   * @param mixed $plugin_definition
   *    Plugin Definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *    Load entities.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
   *    Query entities.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entityManager, QueryFactory $entityQuery) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entityManager;
    $this->entityQuery = $entityQuery;
  }

  /**
   * Used to create HelloWorldContentListBlock block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *    Container of Services.
   * @param array $configuration
   *    Configuration injected for block.
   * @param string $plugin_id
   *    Block ID.
   * @param mixed $plugin_definition
   *    Plugin Definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#theme' => 'item_list',
      '#items' => $this->getContentList(),
      '#type' => 'ul',
    );
  }

  /**
   * Provides array of of nodes with Enabled terms.
   *
   * @return array
   *   Array of available nodes.
   */
  private function getContentList() {
    $query = $this->entityQuery->get('node')
      ->condition('type', 'hello_world_article')
      ->condition('status', 1)
      ->condition('field_sections.entity.field_enabled', TRUE)
      ->sort('title', 'ASC');

    $nids = $query->execute();

    // We get the node storage object.
    $node_storage = $this->entityManager->getStorage('node');

    $nodes = $node_storage->loadMultiple($nids);

    $list = array();
    foreach ($nodes as $nid => $node) {
      $list[] = Link::createFromRoute($node->getTitle(),
        'entity.node.canonical',
        ['node' => $nid]
      );
    }

    return $list;
  }

}
