# Upgrade guide

## Upgrade from 2.0.1 to 2.1.x

This guide is intended to describe the process of upgrading
Times'up from version 2.0.1 to version 2.1.x.

### Changes in configuration

Running `drush updb` and exporting the changed configuration should be sufficient
to convert to the new configuration structure.

The `timesup.settings` configuration object has been introduced. This allows you to enable only the resolvers you actively use, reducing stress on cache and database invalidation.

```yaml
resolvers:
  minutely: false
  hourly: true
  daily: true
  midnight: true
  weekly: true
```



