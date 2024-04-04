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
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The registered logger for this channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a PeriodicityBaseResolver object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory service.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator, ConfigFactoryInterface $config_factory, StateInterface $state, TimeInterface $time, LoggerChannelFactoryInterface $logger_factory) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->configFactory = $config_factory;
    $this->state = $state;
    $this->time = $time;
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
