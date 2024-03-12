<?php

namespace Drupal\timesup\Periodicity;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * The midnight resolver which only apply once by day at midnight.
 *
 * @internal
 */
class MidnightResolver extends PeriodicityBaseResolver {

  /**
   * The Cache tag name to invalidate.
   *
   * @string
   */
  const CACHE_TAG = 'midnight';

  /**
   * {@inheritdoc}
   */
  public function shouldApply(): bool {
    $settings = $this->configFactory->get('timesup.settings');
    $resolvers = $settings->get('resolvers');

    if (!isset($resolvers['midnight']) || !$resolvers['midnight']) {
      return FALSE;
    }

    $today_midnight = $this->getTodayMidnight();
    $last_run = $this->state->get($this->getLastRunKey());
    return $last_run <= $today_midnight->getTimestamp();
  }

  /**
   * Get the DrupalDateTime object of Today at Midnight as UTC as Timezone.
   *
   * @return \Drupal\Component\Datetime\DateTimePlus
   *   The Today at Midnight DrupalDateTime object.
   */
  protected function getTodayMidnight(): DateTimePlus {
    $today_midnight = new DrupalDateTime(NULL, new \DateTimeZone('UTC'));
    $today_midnight->setTime(0, 0, 0);
    return $today_midnight;
  }

}
