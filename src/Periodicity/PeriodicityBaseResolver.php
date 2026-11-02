<?php

namespace Drupal\timesup\Periodicity;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\timesup\Resolver\PeriodicityResolverInterface;

/**
 * Base class to run periodicity cache-tags invalidations.
 */
abstract class PeriodicityBaseResolver implements PeriodicityResolverInterface {
  use StringTranslationTrait;

  /**
   * The registered logger for this channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a PeriodicityBaseResolver object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tags invalidator.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory service.
   */
  public function __construct(
    protected CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    protected ConfigFactoryInterface $configFactory,
    protected StateInterface $state,
    protected TimeInterface $time,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->logger = $logger_factory->get('timesup');
  }

  /**
   * {@inheritdoc}
   */
  public function shouldApply(): bool {
    return FALSE;
  }

  /**
   * Get a normalized cache tag name.
   *
   * @return string[]
   *   The cache tag.
   */
  protected function getCacheTags(): array {
    return [
      'timesup',
      'timesup:' . $this::CACHE_TAG,
    ];
  }

  /**
   * Get a normalized State key.
   *
   * @return string
   *   The state key.
   *
   * @throws \ReflectionException
   */
  protected function getLastRunKey(): string {
    $short_name = (new \ReflectionClass($this))->getShortName();
    return 'timesup.last_run.' . $short_name;
  }

  /**
   * {@inheritdoc}
   */
  public function purge(): void {
    $this->logger->notice(sprintf("Purging Time's up time-sensitive cache-tags: %s.", implode(', ', $this->getCacheTags())));
    $this->cacheTagsInvalidator->invalidateTags($this->getCacheTags());
    $this->state->set($this->getLastRunKey(), $this->time->getRequestTime());
  }

}
