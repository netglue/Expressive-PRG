# Post/Redirect/Get Middleware for Zend Expressive

## Intro

This is simple middleware that'll help you implement the PRG pattern in your Zend Expressive app.

Based on the original [Zend Framework PRG controller plugin](https://zendframework.github.io/zend-mvc-plugin-prg/).

Uses `Zend\Session` but performs no configuration of the session itself. I figured this was a sane choice for session management. If you know of a standard session library to typehint against, let me know, but afaik there isn't one…

## Installation

```bash
$ composer require netglue/expressive-prg
```

## Usage

First you'll need to register the middleware with your DI container as an invokable with something like:

```php
// use NetglueExpressive\Middleware\PostRedirectGet;

'dependencies' => [
    'invokables' => [
        PostRedirectGet::class => PostRedirectGet::class
    ],
],
```

Add the middleware to your routes whenever you want to perform a PRG something like this:

```php    
'routes' => [
    'some-form' => [
        'name' => 'some-form',
        'path' => '/somewhere',
        'allowed_methods' => ['GET', 'POST'],
        'middleware' => [
            PostRedirectGet::class
            // Your middleware to post process, render templates etc…
        ],
    ],
],
```

Inside your action, the request will have an attribute `prg` set to either false or the posted data _(If any)_, for example:

```php
$post = $request->getAttribute('prg');
if (false === $post) {
    // No POST has occurred, probably render the form template
}

// Otherwise, process POST data, validate, store, whatever…
```

The request attribute name is defined as a constant in `PostRedirectGet::KEY`

It is possible for the attribute to be null if the request is neither a GET, nor a POST request and your route allows other methods such as PUT, DELETE etc…

## Tests

```bash
$ cd vendor/netglue/expressive-prg
$ composer install
$ phpunit
```

## About

[Netglue makes web based stuff in Devon, England](https://netglue.uk). We hope this is useful to you and we’d appreciate feedback either way :)

