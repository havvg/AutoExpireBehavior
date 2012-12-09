# AutoExpireBehavior

[![Build Status](https://secure.travis-ci.org/havvg/AutoExpireBehavior.png?branch=master)](http://travis-ci.org/havvg/AutoExpireBehavior)

See the Propel documentation on how to [install a third party behavior](http://propelorm.org/documentation/07-behaviors.html#using_thirdparty_behaviors)

## Usage

Just add the following XML tag in your `schema.xml` file:

```xml
<behavior name="auto_expire" />
```

The behavior will add a column storing the expiration date and those methods: `preExpire`, `expire`, `doExpire`, `postExpire` and `isExpired`.

### Configuration

The following options are provided to customize the behavior.

The `column` option defines the name of the column to store the expiration date into.
If the column is not given, it will be added. Defaults to `expires_at`.

The `required` flag indicates whether the expiration date is required.
If `true` the column will be `NOT NULL`.

An `auto_delete` option can be set to automatically delete the expired model.
When set, this option will apply the "Expiration" behavior to the `postHydrate` hook of the model.

## Expiration

Whenever a model expires by calling `expire`, the following behavior is applied - similar to `save` or `delete`.

1. The `preExpire` method is called. The process will be aborted, if this methods returns `false`.
2. The `doExpire` method will be called.
3. The `postExpire` method is called afterwards.

In addition, the `isExpired` method will be added to check whether the model is expired.
