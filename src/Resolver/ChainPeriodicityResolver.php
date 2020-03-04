<?php

namespace Drupal\timesup\Resolver;

/**
 * Chain resolver to be used to process every periodicity one by one.
 */
class ChainPeriodicityResolver {

  /**
   * The resolvers.
   *
   * @var \Drupal\timesup\Resolver\PeriodicityResolverInterface[]
   */
  protected $resolvers = [];

  /**
   * Constructs a new ChainPeriodicityResolver object.
   *
   * @param \Drupal\timesup\Resolver\PeriodicityResolverInterface[] $resolvers
   *   The resolvers.
   */
  public function __construct(array $resolvers = []) {
    $this->resolvers = $resolvers;
  }

  /**
   * Adds a periodicity resolver.
   *
   * @param \Drupal\timesup\Resolver\PeriodicityResolverInterface $resolver
   *   The resolver.
   */
  public function addResolver(PeriodicityResolverInterface $resolver): void {
    $this->resolvers[] = $resolver;
  }

  /**
   * Gets all added resolvers.
   *
   * @return \Drupal\timesup\Resolver\PeriodicityResolverInterface[]
   *   The resolvers.
   */
  public function getResolvers(): array {
    return $this->resolvers;
  }

  /**
   * Process every collected resolver one by one.
   */
  public function process(): void {
    foreach ($this->resolvers as $resolver) {

      if (!$resolver->shouldApply()) {
        continue;
      }

      $resolver->purge();
    }
  }

}
