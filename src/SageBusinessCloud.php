<?php

namespace Olsgreen\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SageBusinessCloud.
 */
class SageBusinessCloud extends AbstractProvider
{
    /**
     * @var array
     */
    protected $scope;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $locale;

    /**
     * AdobeSign constructor.
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options, array $collaborators = [])
    {
        if (isset($options['scope'])) {
            $this->scope = $options['scope'];
        }

        if (isset($options['country'])) {
            $this->country = $options['country'];
        }

        if (isset($options['locale'])) {
            $this->locale = $options['locale'];
        }

        parent::__construct($options, $collaborators);
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://www.sageone.com/oauth2/auth/central';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth.accounting.sage.com/token';
    }

    /**
     * Returns the base URL for requesting an refresh token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @return string
     */
    public function getBaseRefreshTokenUrl()
    {
        return $this->getBaseAccessTokenUrl([]);
    }

    /**
     * Returns the base URL for revoking a access or refresh token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @return string
     */
    public function getBaseRevokeTokenUrl()
    {
        return 'https://oauth.accounting.sage.com/revoke';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.accounting.sage.com/v3.1/user';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return isset($this->scope) ? $this->scope : [];
    }

    /**
     * Builds the authorization URL's query string.
     * Override parent getAuthorizationQuery to add additional parameters.
     *
     * @param array $params Query parameters
     *
     * @return string Query string
     */
    protected function getAuthorizationQuery(array $params)
    {
        $additionalParams = array_filter([
            'locale'  => $this->locale,
            'country' => $this->country,
        ]);

        $params = array_merge($params, $additionalParams);

        return parent::getAuthorizationQuery($params);
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string      $data     Parsed response data
     *
     * @throws IdentityProviderException
     *
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $message = $data['error'];

            throw new IdentityProviderException($message, 0, $data);
        }

        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 400) {
            throw new IdentityProviderException($data[0]['$message'], 0, $data);
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new User($response);
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param mixed|null $token Either a string or an access token instance
     *
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return [
            'Authorization' => 'Bearer '.($token instanceof AccessToken ? $token->getToken() : $token),
        ];
    }

    /**
     * Returns the full URL to use when requesting an access token.
     *
     * @param array $params Query parameters
     *
     * @return string
     */
    protected function getAccessTokenUrl(array $params)
    {
        if (isset($params['refresh_token'])) {
            return $this->getBaseRefreshTokenUrl();
        } else {
            return $this->getBaseAccessTokenUrl($params);
        }
    }
}
