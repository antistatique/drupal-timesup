# Times'up

Provides cache-tags to deal with time sensitive data.

|       Test-CI        |        Style-CI         |                                                                      Downloads                                                                       |                                                                      Releases                                                                       |
|:----------------------:|:-----------------------:|:----------------------------------------------------------------------------------------------------------------------------------------------------:|:---------------------------------------------------------------------------------------------------------------------------------------------------:|
| [![Build Status](https://github.com/antistatique/drupal-timesup/actions/workflows/ci.yml/badge.svg)](https://github.com/antistatique/drupal-timesup/actions/workflows/ci.yml) | [![Code styles](https://github.com/antistatique/drupal-timesup/actions/workflows/styles.yml/badge.svg)](https://github.com/antistatique/drupal-timesup/actions/workflows/styles.yml) | [![Downloads](https://img.shields.io/badge/downloads-2.0.1-green.svg?style=flat-square)](https://ftp.drupal.org/files/projects/timesup-2.0.1.tar.gz) | [![Latest Stable Version](https://img.shields.io/badge/release-v2.0.1-blue.svg?style=flat-square)](https://www.drupal.org/project/timesup/releases) |

## You need Times'up if

- You want to regenerate caches of specific render-array based on a time
E.g. Refresh the cache of my listing of Events every day.

- You're using a CDN or any Reverse Proxy and you're aware of the Drupal
limitation of `Max-Age` bubbling but still need to invalidate content based on
time dimensions. See [#2352009](https://www.drupal.org/project/drupal/issues/2352009)

- You want to extend the existing feature with your own Periodicity Resolver
E.g. Refresh the cache of my listing of Events every 7th of the month.

## Features

* Cache-tags by minutes, hours, days and months to invalidate render-array.

* Invalidation occurs using the Drupal Cron system.

* Highly extendable code-base system using ChainResolver design pattern.

* Enable or disable resolvers using configuration settings.

## Defaults exposed cache tags

The system will store the last run of every periodicity (daily, weekly, ...)
and use this date as relative to invalidate caches.

It means that `timesup:daily` will re-invalidate content only 1 day after his
last run.

* `timesup:daily`: Will invalidate content every day
* `timesup:weekly`: Will invalidate content every week
* `timesup:hourly`: Will invalidate content every hour
* `timesup:minutely`: Will invalidate content every minute

Timesup also expose a some less-relative cache invalidation.

* `timesup:midnight`: Will invalidate content every day at midnight

## Times'up versions

Times'up is available for Drupal 10.5+, Drupal 11 & Drupal 12 !
The module is ready to be used in Drupal, there are no known issues.

| Drupal Core |    Times'up    |
|:-----------:|:--------------:|
|     8.x     | 8.x-1.0-alpha1 |
|     9.x     |    8.x-1.x     |
|    10.x     |     2.0.x      |
|    11.x     |     2.0.x      |
|    12.x     |     2.0.x      |

## Dependencies

The Drupal version of Times'up requires nothing !
Feel free to use it.

Times'up follows the PHP requirements of Drupal core: PHP 8.1+ for
Drupal 10 and PHP 8.3+ for Drupal 11.

## Supporting organizations

This project is sponsored by Antistatique. We are a Swiss Web Agency,
Visit us at [www.antistatique.net](https://www.antistatique.net) or
[Contact us](mailto:info@antistatique.net).
