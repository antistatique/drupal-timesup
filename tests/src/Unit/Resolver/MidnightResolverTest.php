<?php

namespace Drupal\Tests\timesup\Unit\Resolver;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\timesup\Traits\InvokeMethodTrait;
use Drupal\timesup\Periodicity\MidnightResolver;
use Drupal\Tests\UnitTestCase;

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

    $today_midnight = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $today_midnight->setTimestamp($request_time);
    $today_midnight->setTime(0, 0);

    $midnightResolverMock = $this->getMockBuilder(MidnightResolver::class)
      ->setConstructorArgs([
        $this->cacheTagsInvalidator,
        $this->state,
        $this->time,
        $this->loggerFactory,
      ])
      ->setMethods(['getTodayMidnight'])
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
