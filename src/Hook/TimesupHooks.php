<?php

namespace Drupal\timesup\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\timesup\Resolver\ChainPeriodicityResolver;

/**
 * Hook implementations for timesup.
 */
class TimesupHooks {

  /**
   * Constructs a new TimesupHooks object.
   *
   * @param \Drupal\timesup\Resolver\ChainPeriodicityResolver $chainPeriodicityResolver
   *   The chain periodicity resolver.
   */
  public function __construct(
    protected ChainPeriodicityResolver $chainPeriodicityResolver,
  ) {}

  /**
   * Implements hook_cron().
   *
   * Adds clean up job to drop expired cache tags.
   */
  #[Hook('cron')]
  public function cron() {
    $this->chainPeriodicityResolver->process();
  }

}
