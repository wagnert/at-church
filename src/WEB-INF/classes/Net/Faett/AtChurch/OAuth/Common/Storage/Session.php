<?php

/**
 * Net\Faett\AtChurch\OAuth\Common\Storage\Session
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 */

namespace Net\Faett\AtChurch\OAuth\Common\Storage;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;
use AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface;

/**
 * Stores a token in the application servers session.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 */
class Session implements TokenStorageInterface
{
    /**
     * Flag that the session has been started.
     *
     * @var boolean
     */
    protected $startSession;

    /**
     * Session variable name.
     *
     * @var string
     */
    protected $sessionVariableName;

    /**
     * Session state varialbe name.
     *
     * @var string
     */
    protected $stateVariableName;

    /**
     * Initializes the session wrapper.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface $servletRequest      The servlet request
     * @param boolean                                                   $startSession        Whether or not to start the session upon construction.
     * @param string                                                    $sessionVariableName The variable name to use within the session
     * @param string                                                    $stateVariableName   The state variable name to use within the session
     */
    public function __construct(
        HttpServletRequestInterface $servletRequest,
        $startSession = true,
        $sessionVariableName = 'lusitanian_oauth_token',
        $stateVariableName = 'lusitanian_oauth_state'
    ) {

        // set the session
        $this->session = $servletRequest->getSession(true);

        // start the session if not already dony
        if ($startSession && $this->session->isStarted() === false) {
            $this->session->start();
        }

        // initialize the members
        $this->startSession = $startSession;
        $this->sessionVariableName = $sessionVariableName;
        $this->stateVariableName = $stateVariableName;

        // add inital data
        if ($this->session->getData($sessionVariableName) == null) {
            $this->session->putData($sessionVariableName, array());
        }
        if ($this->session->getData($stateVariableName) == null) {
            $this->session->putData($stateVariableName, array());
        }
    }

    /**
     * Default destrcutor implementation.
     */
    public function __destruct()
    {
        if ($this->startSession) {
            // save session here
        }
    }

    /**
     * Returns the OAuth access token for the passed service.
     *
     * @param string $service The requested service
     *
     * @return string Thes OAuth access token stored in the session
     * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException Is thrown if the requested token is not available
     * @see \OAuth\Common\Storage\TokenStorageInterface::retrieveAccessToken()
     */
    public function retrieveAccessToken($service)
    {
        if ($this->hasAccessToken($service)) {
            $data = $this->session->getData($this->sessionVariableName);
            return unserialize($data[$service]);
        }
        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }

    /**
     * Stores the OAuth access token in the session.
     *
     * @param string                            $service The requested service
     * @param OAuth\Common\Token\TokenInterface $token   The OAuth access token to store
     *
     * @return Net\Faett\AtChurch\OAuth\Common\Storage\Session The instance itself
     * @see \OAuth\Common\Storage\TokenStorageInterface::storeAccessToken()
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $serializedToken = serialize($token);
        if (is_array($data = $this->session->getData($this->sessionVariableName))) {
            $data[$service] = $serializedToken;
            $this->session->putData($this->sessionVariableName, $data);
        } else {
            $this->session->putData($this->sessionVariableName, array($service => $serializedToken));
        }
        return $this;
    }

    /**
     * Queries whether an access token for the service is available.
     *
     * @param string $service The requested service
     *
     * @return boolean TRUE if the access token is available, else FALSE
     * @see \OAuth\Common\Storage\TokenStorageInterface::hasAccessToken()
     */
    public function hasAccessToken($service)
    {
        if (is_array($data = $this->session->getData($this->sessionVariableName))) {
            return isset($data[$service]);
        }
        return false;
    }

    /**
     * Deletes the OAuth access token for the passed service from the session.
     *
     * @param string $service The requested service
     *
     * @return Net\Faett\AtChurch\OAuth\Common\Storage\Session The instance itself
     * @see \OAuth\Common\Storage\TokenStorageInterface::clearToken()
     */
    public function clearToken($service)
    {
        if (is_array($data = $this->session->getData($this->sessionVariableName)) && isset($data[$service])) {
            unset($data[$service]);
            $this->session->putData($this->sessionVariableName, $data);
        }
        return $this;
    }

    /**
     * Deletes all OAuth access tokens from the session.
     *
     * @return Net\Faett\AtChurch\OAuth\Common\Storage\Session The instance itself
     * @see \OAuth\Common\Storage\TokenStorageInterface::clearAllTokens()
     */
    public function clearAllTokens()
    {
        $this->session->putData($this->sessionVariableName, null);
        return $this;
    }

    /**
     * Stores the state for the passed service in the session.
     *
     * @param string $service The requested service
     * @param string $state   The state to be stored
     *
     * @return Net\Faett\AtChurch\OAuth\Common\Storage\Session The instance itself
     * @see \OAuth\Common\Storage\TokenStorageInterface::storeAuthorizationState()
     */
    public function storeAuthorizationState($service, $state)
    {
        if (is_array($data = $this->session->getData($this->stateVariableName))) {
            $data[$service] = $state;
            $this->session->putData($this->stateVariableName, $data);
        } else {
            $this->session->putData($this->stateVariableName, array($service => $state));
        }
        return $this;
    }

    /**
     * Queries whether the passed service has an authorization state in the session.
     *
     * @param string $service The requested service
     *
     * @return boolean TRUE if the authorization state is available, else FALSE
     * @see \OAuth\Common\Storage\TokenStorageInterface::hasAuthorizationState()
     */
    public function hasAuthorizationState($service)
    {
        if (is_array($data = $this->session->getData($this->stateVariableName))) {
            return isset($data[$service]);
        }
        return false;
    }

    /**
     * Returns the authorization state of the passed service from the session.
     *
     * @param string $service The requested service
     *
     * @return string The authorization state for the passed service
     * @throws \OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException Is thrown if the state is not available in the session
     * @see \OAuth\Common\Storage\TokenStorageInterface::retrieveAuthorizationState()
     */
    public function retrieveAuthorizationState($service)
    {
        if ($this->hasAuthorizationState($service)) {
            $data = $this->session->getData($this->stateVariableName);
            return $data[$service];
        }
        throw new AuthorizationStateNotFoundException('State not found in session, are you sure you stored it?');
    }

    /**
     * Removes the authorization state for the passed service from the session.
     *
     * @param string $service The service to remove the authorization state for
     *
     * @return Net\Faett\AtChurch\OAuth\Common\Storage\Session The instance itself
     * @see \OAuth\Common\Storage\TokenStorageInterface::clearAuthorizationState()
     */
    public function clearAuthorizationState($service)
    {
        if (is_array($data = $this->session->getData($this->stateVariableName)) && isset($data[$service])) {
            unset($data[$service]);
            $this->session->putData($this->stateVariableName, $data);
        }
        return $this;
    }

    /**
     * Removes all authorization states from the session.
     *
     * @return Net\Faett\AtChurch\OAuth\Common\Storage\Session The instance itself
     * @see \OAuth\Common\Storage\TokenStorageInterface::clearAllAuthorizationStates()
     */
    public function clearAllAuthorizationStates()
    {
        $this->session->putData($this->stateVariableName, null);
        return $this;
    }
}
