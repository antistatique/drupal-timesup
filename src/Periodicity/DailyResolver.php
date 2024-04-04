<?php

namespace Drupal\timesup\Periodicity;

/**
 * The daily resolver which only apply once by day.
 *
 * @internal
 */
final class DailyResolver extends PeriodicityBaseResolver {

  /**
   * The Cache tag name to invalidate.
   *
   * @string
   */
  const CACHE_TAG = 'daily';

  /**
   * {@inheritdoc}
   */
  public function shouldApply(): bool {
    $settings = $this->configFactory->get('timesup.settings');
    $resolvers = $settings->get('resolvers');

    if (!isset($resolvers['daily']) || !$resolvers['daily']) {
      return FALSE;
    }

    $last_run_per_day = $this->state->get($this->getLastRunKey());
    return !($this->time->getRequestTime() - $last_run_per_day < 86400);
  }

}
