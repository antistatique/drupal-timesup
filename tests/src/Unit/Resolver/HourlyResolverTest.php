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
use Drupal\timesup\Periodicity\HourlyResolver;

/**
 * @coversDefaultClass \Drupal\timesup\Periodicity\HourlyResolver
 *
 * @group timesup
 */
class HourlyResolverTest extends UnitTestCase {
  use InvokeMethodTrait;

  /**
   * The resolver.
   *
   * @var \Drupal\timesup\Periodicity\HourlyResolver
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
    $resolver = new HourlyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $tags = $this->invokeMethod($resolver, 'getCacheTags');
    $this->assertSame(['timesup', 'timesup:hourly'], $tags);
  }

  /**
   * @covers ::getLastRunKey
   */
  public function testGetLastRunKey() {
    $resolver = new HourlyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $key = $this->invokeMethod($resolver, 'getLastRunKey');
    $this->assertEquals('timesup.last_run.HourlyResolver', $key);
  }

  /**
   * @covers ::shouldApply
   */
  public function testShouldApplySettingsDisabled() {
    $this->settings->expects($this->once())
      ->method('get')->with('resolvers')->willReturn(['hourly' => FALSE]);

    $this->state->expects($this->never())
      ->method('get')->with('timesup.last_run.HourlyResolver');
    $this->time->expects($this->never())
      ->method('getRequestTime');

    $resolver = new HourlyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $this->assertFalse($resolver->shouldApply());
  }

  /**
   * @covers ::shouldApply
   *
   * @dataProvider shouldApplyProvider
   */
  public function testShouldApply($request_time, $last_run, $expected) {
    $this->settings->expects($this->once())
      ->method('get')->with('resolvers')->willReturn(['hourly' => TRUE]);

    $this->state->expects($this->once())
      ->method('get')->with('timesup.last_run.HourlyResolver')->willReturn($last_run);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn($request_time);

    $resolver = new HourlyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
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
      'Run exactly 1h before' => [1583280000, 1583276400, TRUE],
      'Run exactly 30min before' => [1583280000, 1583278200, FALSE],
      'Run more than 24h before' => [1583280000, 1583107200, TRUE],
      'Run exactly 1 year before' => [1583280000, 1551657600, TRUE],
    ];
  }

  /**
   * @covers ::purge
   */
  public function testPurge() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')->with(['timesup', 'timesup:hourly']);
    $this->state->expects($this->once())
      ->method('set')->with('timesup.last_run.HourlyResolver', 1583309350);
    $this->time->expects($this->once())
      ->method('getRequestTime')->willReturn(1583309350);
    $this->logger->expects($this->once())
      ->method('notice')->with("Purging Time's up time-sensitive cache-tags: timesup, timesup:hourly.");

    $resolver = new HourlyResolver($this->cacheTagsInvalidator, $this->configFactory, $this->state, $this->time, $this->loggerFactory);
    $resolver->purge();
  }

}
