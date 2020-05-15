<?php

namespace Drupal\timesup\Periodicity;

use Drupal\Core\Datetime\DrupalDateTime;
use DateTimeZone;

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
    $today_midnight = $this->getTodayMidnight();
    $last_run = $this->state->get($this->getLastRunKey());
    return $last_run <= $today_midnight->getTimestamp();
  }

  /**
   * Get the DrupalDateTime object of Today at Midnight as UTC as Timezone.
   *
   * @return \DateTime
   *   The Today at Midnight DrupalDateTime object.
   */
  protected function getTodayMidnight(): \DateTime {
    $today_midnight = new DrupalDateTime(NULL, new DateTimeZone('UTC'));
    $today_midnight->setTime(0, 0, 0);
    return $today_midnight;
  }

}
