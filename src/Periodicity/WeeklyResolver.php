<?php

namespace Drupal\timesup\Periodicity;

/**
 * The weekly resolver which will only apply once every week.
 *
 * @internal
 */
final class WeeklyResolver extends PeriodicityBaseResolver {

  /**
   * The Cache tag name to invalidate.
   *
   * @string
   */
  const CACHE_TAG = 'weekly';

  /**
   * {@inheritdoc}
   */
  public function shouldApply(): bool {
    $settings = $this->configFactory->get('timesup.settings');
    $resolvers = $settings->get('resolvers');

    if (!isset($resolvers['weekly']) || !$resolvers['weekly']) {
      return FALSE;
    }

    $last_run_per_week = $this->state->get($this->getLastRunKey());
    return !($this->time->getRequestTime() - $last_run_per_week < 604800);
  }

}
