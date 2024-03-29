<?php

namespace Drupal\Tests\timesup\Unit\Resolver;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\timesup\Traits\InvokeMethodTrait;
use Drupal\Tests\UnitTestCase;
use Drupal\timesup\Periodicity\MidnightResolver;

/**
 * @coversDefaultClass \Drupal\timesup\Periodicity\MidnightResolver
 *
 * @group timesup
 */
final class MidnightResolverTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * The resolver.
   *
   * @var \Drupal\timesup\Periodicity\MidnightResolver
   */
  protected $resolver;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The logger channel factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->cacheTagsInvalidator = $this->createMock(CacheTagsInvalidatorInterface::class);

    $this->state = $this->createMock(StateInterface::class);

    $this->time = $this->createMock(TimeInterface::class);

    $this->logger = $this->createMock(LoggerChannelInterface::class);

    $this->loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->loggerFactory->expects($this->once())
      ->method('get')->with('timesup')->willReturn($this->logger);
  }

  /**
   * @covers ::getCacheTags
   */
  public function testGetCacheTags() {
    $resolver = new MidnightResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $tags = $this->invokeMethod($resolver, 'getCacheTags');
    $this->assertSame(['timesup', 'timesup:midnight'], $tags);
  }

  /**
   * @covers ::getLastRunKey
   */
  public function testGetLastRunKey() {
    $resolver = new MidnightResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $key = $this->invokeMethod($resolver, 'getLastRunKey');
    $this->assertEquals('timesup.last_run.MidnightResolver', $key);
  }

  /**
   * @covers ::shouldApply
   *
   * @dataProvider shouldApplyProvider
   */
  public function testShouldApply($request_time, $last_run, $expected) {
    $this->state->expects($this->any())
      ->method('get')->willReturn($last_run);
    $this->time->expects($this->any())
      ->method('getRequestTime')->willReturn($request_time);

    $today_midnight = new DateTimePlus('now', new \DateTimeZone('UTC'));
    $today_midnight->setTimestamp($request_time);
    $today_midnight->setTime(0, 0);

    $midnightResolverMock = $this->getMockBuilder(MidnightResolver::class)
      ->setConstructorArgs([
        $this->cacheTagsInvalidator,
        $this->state,
        $this->time,
        $this->loggerFactory,
      ])
      ->onlyMethods(['getTodayMidnight'])
      ->getMock();
    $midnightResolverMock->method('getTodayMidnight')
      ->willReturn($today_midnight);

    $this->assertEquals($expected, $midnightResolverMock->shouldApply());
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
      'Run exactly 1min before midnight' => [1583280000, 1583279940, TRUE],
      'Run the same day' => [1583326800, 1583362800, FALSE],
      'Run exactly 30sec before midnight' => [1583280000, 1583279970, TRUE],
      'Run exactly 1h before' => [1583280000, 1583276400, TRUE],
      'Run exactly 30min before midnight' => [1583280000, 1583278200, TRUE],
      'Run more than 24h before' => [1583280000, 1583107200, TRUE],
      'Run exactly 23h59 before' => [1583280000, 1583366399, FALSE],
      'Run more than 48h before' => [1583452800, 1583280000, TRUE],
      'Run exactly 1 year before' => [1583280000, 1551657600, TRUE],
    ];
  }

  /**
   * @covers ::purge
   */
  public function testPurge() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')->with(['timesup', 'timesup:midnight']);
    $this->state->expects($this->once())
      ->method('set')->with('timesup.last_run.MidnightResolver', 1583309350);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn(1583309350);
    $this->logger->expects($this->once())
      ->method('notice')->with("Purging Time's up time-sensitive cache-tags: timesup, timesup:midnight.");

    $resolver = new MidnightResolver($this->cacheTagsInvalidator, $this->state, $this->time, $this->loggerFactory);
    $resolver->purge();
  }

}
