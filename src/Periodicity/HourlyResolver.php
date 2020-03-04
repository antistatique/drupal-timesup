<?php

namespace Drupal\timesup\Periodicity;

/**
 * The hourly resolver which will apply once every hour.
 *
 * @internal
 */
final class HourlyResolver extends PeriodicityBaseResolver {

  /**
   * The Cache tag name to invalidate.
   *
   * @string
   */
  const CACHE_TAG = 'hourly';

  /**
   * {@inheritdoc}
   */
  public function shouldApply(): bool {
    $last_run_per_week = $this->state->get($this->getLastRunKey());
    return !($this->time->getRequestTime() - $last_run_per_week < 3600);
  }

}
