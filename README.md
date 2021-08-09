 # Sage Business Cloud Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/olsgreen/oauth2-adobe-sign.svg?style=flat-square)](https://github.com/olsgreen/oauth2-adobe-sign/releases)
[![Tests](https://github.com/olsgreen/oauth2-adobe-sign/workflows/Tests/badge.svg)](https://github.com/olsgreen/oauth2-adobe-sign/actions/runs)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package provides Sage Business Cloud OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

This package requires PHP >= 7.4.

## Installation

To install, use composer:

```
composer require olsgreen/oauth2-sage-business-cloud
```

## Usage

Usage is the same as The League's OAuth client, using `\Olsgreen\OAuth2\Client\Provider\AdobeSign` as the provider.

### Authorization Code Flow

```php
$provider = new Olsgreen\OAuth2\Client\Provider\SageBusinessCloud([
    'clientId'          => '{sage-client-id}',
    'clientSecret'      => '{sage-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'locale'            => 'en-GB',
    'country'           => 'gb',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Countries & Locales

See https://developer.sage.com/accounting/guides/authenticating/authentication/ for a current list of supported countries and locales.


## Provider Quirks

Adobe do not provide an endpoint to retrieve the current user, so `getResourceOwnerDetailsUrl()`, `createResourceOwner()` & `getResourceOwner()` will throw `NotImplmenetedException`.


## Testing

``` bash
$ ./vendor/bin/phpunit
```


## Credits

Originally forked from [kevinm/oauth2-adobe-sign](https://github.com/kevinem/oauth2-adobe-sign).

- [Oliver Green](https://github.com/olsgreen)
- [All Contributors](https://github.com/thephpleague/oauth2-github/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/oldgreen/oauth2-adobe-sign/blob/master/LICENSE) for more information.
