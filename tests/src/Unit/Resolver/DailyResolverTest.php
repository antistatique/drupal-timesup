<?php

namespace Drupal\Tests\timesup\Unit\Resolver;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\timesup\Traits\InvokeMethodTrait;
use Drupal\timesup\Periodicity\DailyResolver;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\timesup\Periodicity\DailyResolver
 *
 * @group timesup
 */
class DailyResolverTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * The resolver.
   *
   * @var \Drupal\timesup\Periodicity\DailyResolver
   */
  protected $resolver;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->cacheTagsInvalidator = $this->getMockBuilder(CacheTagsInvalidatorInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->state = $this->getMockBuilder(StateInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->time = $this->getMockBuilder(TimeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->loggerFactory = $this->getMockBuilder(LoggerChannelFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->loggerFactory->expects($this->once())
      ->method('get')->with('timesup')->willReturn($this->logger);
  }

  /**
   * @covers ::getCacheTags
   */
  public function testGetCacheTags() {
    $resolver = new DailyResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $tags = $this->invokeMethod($resolver, 'getCacheTags');
    $this->assertSame(['timesup', 'timesup:daily'], $tags);
  }

  /**
   * @covers ::getLastRunKey
   */
  public function testGetLastRunKey() {
    $resolver = new DailyResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $key = $this->invokeMethod($resolver, 'getLastRunKey');
    $this->assertEquals('timesup.last_run.DailyResolver', $key);
  }

  /**
   * @covers ::shouldApply
   *
   * @dataProvider shouldApplyProvider
   */
  public function testShouldApply($request_time, $last_run, $expected) {
    $this->state->expects($this->once())
      ->method('get')->with('timesup.last_run.DailyResolver')->willReturn($last_run);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn($request_time);

    $resolver = new DailyResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $this->assertEquals($expected, $resolver->shouldApply());
  }

  /**
   * Request time, last run time and the expected results "Should Apply".
   *
   * @return array
   *   The menu level scenario.
   */
  public function shouldApplyProvider() {
    return [
      'Never run' => [1583280000, NULL, TRUE],
      'Run just now' => [1583280000, 1583280001, FALSE],
      'Run exactly 24h before' => [1583280000, 1583193600, TRUE],
      'Run exactly 18h before' => [1583280000, 1583215200, FALSE],
      'Run more than 24h before' => [1583280000, 1583107200, TRUE],
      'Run exactly 1 year before' => [1583280000, 1551657600, TRUE],
    ];
  }

  /**
   * @covers ::purge
   */
  public function testPurge() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')->with(['timesup', 'timesup:daily']);
    $this->state->expects($this->once())
      ->method('set')->with('timesup.last_run.DailyResolver', 1583309350);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn(1583309350);
    $this->logger->expects($this->once())
      ->method('notice')->with("Purging Time's up time-sensitive cache-tags: timesup, timesup:daily.");

    $resolver = new DailyResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $resolver->purge();
  }

}
