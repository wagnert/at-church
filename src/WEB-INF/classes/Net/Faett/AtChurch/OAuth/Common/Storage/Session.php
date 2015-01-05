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
 * @category   Net
 * @package    Faett
 * @subpackage AtChurch
 * @author     Tim Wagner <wagner_tim78@hotmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/faett-net/at-church
 */

namespace Net\Faett\AtChurch\OAuth\Common\Storage;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

/**
 * Stores a token in the application servers session.
 *
 * @category   Net
 * @package    Faett
 * @subpackage AtChurch
 * @author     Tim Wagner <wagner_tim78@hotmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/faett-net/at-church
 */
class Session implements TokenStorageInterface
{
    /**
     * @var bool
     */
    protected $startSession;

    /**
     * @var string
     */
    protected $sessionVariableName;

    /**
     * @var string
     */
    protected $stateVariableName;

    /**
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequest $serlvetRequest The servlet request
     * @param bool $startSession Whether or not to start the session upon construction.
     * @param string $sessionVariableName the variable name to use within the _SESSION superglobal
     * @param string $stateVariableName
     */
    public function __construct(
        $servletRequest,
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function hasAccessToken($service)
    {
        if (is_array($data = $this->session->getData($this->sessionVariableName))) {
            return isset($data[$service]);
        }
        return false;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function clearAllTokens()
    {
        $this->session->putData($this->sessionVariableName, null);
        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function hasAuthorizationState($service)
    {
        if (is_array($data = $this->session->getData($this->stateVariableName))) {
            return isset($data[$service]);
        }
        return false;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function clearAllAuthorizationStates()
    {
        $this->session->putData($this->stateVariableName, null);
        return $this;
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
}
