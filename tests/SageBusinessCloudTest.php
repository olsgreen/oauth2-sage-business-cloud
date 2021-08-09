<?php


namespace Tests;

use GuzzleHttp\Psr7\Request;
use Mockery;
use Olsgreen\OAuth2\Client\Provider\NotImplementedException;
use Olsgreen\OAuth2\Client\Provider\SageBusinessCloud;

/**
 * Class AdobeSignTest
 * @package Tests
 */
class SageBusinessCloudTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SageBusinessCloud
     */
    protected $provider;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->provider = new SageBusinessCloud([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'none',
            'scope' => 'full_access',
            'country' => 'gb',
            'locale' => 'en-GB',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /*public function testScopes()
    {
        $options = [
            'scope' => [
                'user_login:account',
                'agreement_send:account'
            ]
        ];

        $url = $this->provider->getAuthorizationUrl($options);
        $this->assertStringContainsString(implode('+', $options['scope']), $url);
    }*/

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('www.sageone.com', $uri['host']);
        $this->assertEquals('/oauth2/auth/central', $uri['path']);
        $this->assertEquals('mock_client_id', $query['client_id']);
        $this->assertEquals('en-GB', $query['locale']);
        $this->assertEquals('gb', $query['country']);
    }

    public function testGetAccessToken()
    {
        $accessToken = [
            'access_token' => 'mock_access_token'
        ];

        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessToken));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->withArgs(function($request) {
            $uri = $request->getUri();
            parse_str((string) $request->getBody(), $body);

            $this->assertEquals('oauth.accounting.sage.com', $uri->getHost());
            $this->assertEquals('/token', $uri->getPath());

            $this->assertEquals('mock_client_id', $body['client_id']);
            $this->assertEquals('mock_client_secret', $body['client_secret']);
            $this->assertEquals('mock_authorization_code', $body['code']);
            $this->assertEquals('authorization_code', $body['grant_type']);

            return true;
        })->times(1)->andReturn($response);

        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals($token->getToken(), 'mock_access_token');
    }

    public function testExchangerRefreshTokenForAccessToken()
    {
        $accessToken = [
            'access_token' => 'mock_access_token'
        ];

        $response = Mockery::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($accessToken));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = Mockery::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->withArgs(function($request) {
            $uri = $request->getUri();
            parse_str((string) $request->getBody(), $body);

            $this->assertEquals('oauth.accounting.sage.com', $uri->getHost());
            $this->assertEquals('/token', $uri->getPath());

            $this->assertEquals('mock_client_id', $body['client_id']);
            $this->assertEquals('mock_client_secret', $body['client_secret']);
            $this->assertEquals('mock_refresh_token', $body['refresh_token']);
            $this->assertEquals('refresh_token', $body['grant_type']);

            return true;
        })->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => 'mock_refresh_token']);

        $this->assertEquals($token->getToken(), 'mock_access_token');
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $this->expectException(NotImplementedException::class);

        $accessToken = Mockery::mock('League\OAuth2\Client\Token\AccessToken');
        $res = $this->provider->getResourceOwnerDetailsUrl($accessToken);
    }

    public function testCreateResourceOwner()
    {
        $this->expectException(NotImplementedException::class);

        $accessToken = Mockery::mock('League\OAuth2\Client\Token\AccessToken');
        $res = $this->provider->getResourceOwner($accessToken);
    }
}