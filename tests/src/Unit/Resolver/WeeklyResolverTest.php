<?php

namespace Drupal\Tests\timesup\Unit\Resolver;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\timesup\Traits\InvokeMethodTrait;
use Drupal\Tests\UnitTestCase;
use Drupal\timesup\Periodicity\WeeklyResolver;

/**
 * @coversDefaultClass \Drupal\timesup\Periodicity\WeeklyResolver
 *
 * @group timesup
 */
class WeeklyResolverTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * The resolver.
   *
   * @var \Drupal\timesup\Periodicity\WeeklyResolver
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
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The mocked configuration object timesup.settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

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

    // Create a mock config factory and config object.
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->settings = $this->createMock(ImmutableConfig::class);
    $this->configFactory->method('get')->with('timesup.settings')->willReturn($this->settings);

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
    $resolver = new WeeklyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $tags = $this->invokeMethod($resolver, 'getCacheTags');
    $this->assertSame(['timesup', 'timesup:weekly'], $tags);
  }

  /**
   * @covers ::getLastRunKey
   */
  public function testGetLastRunKey() {
    $resolver = new WeeklyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $key = $this->invokeMethod($resolver, 'getLastRunKey');
    $this->assertEquals('timesup.last_run.WeeklyResolver', $key);
  }

  /**
   * @covers ::shouldApply
   */
  public function testShouldApplySettingsDisabled() {
    $this->settings->expects($this->once())
      ->method('get')->with('resolvers')->willReturn(['weekly' => FALSE]);

    $this->state->expects($this->never())
      ->method('get')->with('timesup.last_run.WeeklyResolver');
    $this->time->expects($this->never())
      ->method('getRequestTime');

    $resolver = new WeeklyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $this->assertFalse($resolver->shouldApply());
  }

  /**
   * @covers ::shouldApply
   *
   * @dataProvider shouldApplyProvider
   */
  public function testShouldApply($request_time, $last_run, $expected) {
    $this->settings->expects($this->once())
      ->method('get')->with('resolvers')->willReturn(['weekly' => TRUE]);

    $this->state->expects($this->once())
      ->method('get')->with('timesup.last_run.WeeklyResolver')->willReturn($last_run);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn($request_time);

    $resolver = new WeeklyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
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
      'Run exactly 1min before' => [1583280000, 1583279940, FALSE],
      'Run exactly 30sec before' => [1583280000, 1583279970, FALSE],
      'Run exactly 1h before' => [1583280000, 1583276400, FALSE],
      'Run exactly 30min before' => [1583280000, 1583278200, FALSE],
      'Run more than 24h before' => [1583280000, 1583107200, FALSE],
      'Run exactly 1 week before' => [1583280000, 1582675200, TRUE],
      'Run more than 1 week before' => [1583280000, 1577836800, TRUE],
      'Run exactly 1 year before' => [1583280000, 1551657600, TRUE],
    ];
  }

  /**
   * @covers ::purge
   */
  public function testPurge() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')->with(['timesup', 'timesup:weekly']);
    $this->state->expects($this->once())
      ->method('set')->with('timesup.last_run.WeeklyResolver', 1583309350);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn(1583309350);
    $this->logger->expects($this->once())
      ->method('notice')->with("Purging Time's up time-sensitive cache-tags: timesup, timesup:weekly.");

    $resolver = new WeeklyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $resolver->purge();
  }

}
