<?php

namespace Drupal\Tests\timesup\Unit\Resolver;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\timesup\Resolver\ChainPeriodicityResolver;

/**
 * @coversDefaultClass \Drupal\timesup\Resolver\ChainPeriodicityResolver
 *
 * @group timesup
 */
class ChainPeriodicityResolverTest extends UnitTestCase {

  /**
   * The resolver.
   *
   * @var \Drupal\timesup\Resolver\ChainPeriodicityResolver
   */
  protected $resolver;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->resolver = new ChainPeriodicityResolver();
  }

  /**
   * Tests the resolver and priority.
   *
   * ::covers addResolver
   * ::covers getResolvers
   * ::covers process.
   */
  public function testResolver() {
    $container = new ContainerBuilder();

    $mock_builder = $this->getMockBuilder('Drupal\timesup\Resolver\PeriodicityResolverInterface')
      ->disableOriginalConstructor();

    $first_resolver = $mock_builder->getMock();
    $first_resolver->expects($this->once())
      ->method('shouldApply');
    $first_resolver->expects($this->never())
      ->method('purge');
    $container->set('timesup.first_resolver', $first_resolver);

    $second_resolver = $mock_builder->getMock();
    $second_resolver->expects($this->once())
      ->method('shouldApply')
      ->willReturn(TRUE);
    $second_resolver->expects($this->once())
      ->method('purge');
    $container->set('timesup.second_resolver', $second_resolver);

    $third_resolver = $mock_builder->getMock();
    $third_resolver->expects($this->once())
      ->method('shouldApply');
    $third_resolver->expects($this->never())
      ->method('purge');
    $container->set('timesup.third_resolver', $third_resolver);

    // Mimic how the container would add the services.
    // @see \Drupal\Core\DependencyInjection\Compiler\TaggedHandlersPass::process
    $resolvers = [
      'timesup.first_resolver' => 900,
      'timesup.second_resolver' => 400,
      'timesup.third_resolver' => -100,
    ];
    arsort($resolvers, SORT_NUMERIC);
    foreach ($resolvers as $id => $priority) {
      $this->resolver->addResolver($container->get($id));
    }

    $this->resolver->process();
  }

}
