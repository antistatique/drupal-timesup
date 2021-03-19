# Times'up

Provides cache-tags to deal with time sensitive data.

|       Test-CI        |        Style-CI         |        Downloads        |         Releases         |
|:----------------------:|:-----------------------:|:-----------------------:|:------------------------:|
| [![Build Status](https://github.com/antistatique/drupal-timesup/actions/workflows/ci.yml/badge.svg)](https://github.com/antistatique/drupal-timesup/actions/workflows/ci.yml) | [![Code styles](https://github.com/antistatique/drupal-timesup/actions/workflows/styles.yml/badge.svg)](https://github.com/antistatique/drupal-timesup/actions/workflows/styles.yml) | [![Downloads](https://img.shields.io/badge/downloads-8.x--1.0-green.svg?style=flat-square)](https://ftp.drupal.org/files/projects/timesup-8.x-1.0.tar.gz) | [![Latest Stable Version](https://img.shields.io/badge/release-v1.0-blue.svg?style=flat-square)](https://www.drupal.org/project/timesup/releases) |

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

Times'up is only available for Drupal 8 !
The module is ready to be used in Drupal 8, there are no known issues.

This version should work with all Drupal 8 releases, and it is always
recommended to keep Drupal core installations up to date.

## Dependencies

The Drupal 8 version of Times'up requires nothing !
Feel free to use it.

Times'up requires PHP 7.0+ to works properly. We recommend updating to at least
PHP 7.2 if possible, and ideally PHP 7.4, which is supported as of Drupal 8.8.0.

## Supporting organizations

This project is sponsored by Antistatique. We are a Swiss Web Agency,
Visit us at [www.antistatique.net](https://www.antistatique.net) or
[Contact us](mailto:info@antistatique.net).
