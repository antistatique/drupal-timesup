<?php

namespace Drupal\timesup\Resolver;

/**
 * Runs the added periodicity-invalidator one by one, if they can be applied.
 */
interface PeriodicityResolverInterface {

  /**
   * The Cache tag name to invalidate.
   *
   * @string
   */
  const CACHE_TAG = '';

  /**
   * Does the periodicity should run ?
   *
   * Based on the State config, does this periodicity should run or wait.
   *
   * @return bool
   *   Return True when the periodicity has expired and should run again.
   */
  public function shouldApply(): bool;

  /**
   * Purge the periodicity cache tag.
   */
  public function purge(): void;

}
