<?php

namespace Drupal\timesup\Periodicity;

/**
 * The minutes by minutes resolver which will apply every minutes.
 *
 * @internal
 */
final class MinutelyResolver extends PeriodicityBaseResolver {

  /**
   * The Cache tag name to invalidate.
   *
   * @string
   */
  const CACHE_TAG = 'minutely';

  /**
   * {@inheritdoc}
   */
  public function shouldApply(): bool {
    $settings = $this->configFactory->get('timesup.settings');
    $resolvers = $settings->get('resolvers');

    if (!isset($resolvers['minutely']) || !$resolvers['minutely']) {
      return FALSE;
    }

    $last_run_per_minute = $this->state->get($this->getLastRunKey());
    return !($this->time->getRequestTime() - $last_run_per_minute < 60);
  }

}
