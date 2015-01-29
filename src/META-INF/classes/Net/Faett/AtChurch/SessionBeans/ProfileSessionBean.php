<?php

/**
 * Net\Faett\AtChurch\SessionBeans\ProfileSessionBean
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

namespace Net\Faett\AtChurch\SessionBeans;

use Net\Faett\AtChurch\Interceptors\AuthorizationInterceptor;

/**
 * Session bean that handles the user profile specific functionality.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @Stateful
 */
class ProfileSessionBean
{

    /**
     * The application instance.
     *
     * @var \AppserverIo\Psr\Application\ApplicationInterface
     * @Resource(name="ApplicationInterface")
     */
    protected $application;

    /**
     * The user, logged into the system.
     *
     * @var \stdClass
     */
    protected $user;

    /**
     * The application instance.
     *
     * @return \AppserverIo\Psr\Application\ApplicationInterface The application instance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns the initial context instance.
     *
     * @return \AppserverIo\Appserver\Core\InitialContext The initial context instance
     */
    public function getInitialContext()
    {
        return $this->getApplication()->getInitialContext();
    }

    /**
     * Logs the user into the system.
     *
     * @param \stdClass $user The user to login
     *
     * @return void
     * @todo Login functionality still needs to be implemented
     */
    public function login(\stdClass $user)
    {
        $this->user = $user;
    }

    /**
     * Checks if a user has been logged into the system, if not an exception
     * will be thrown.
     *
     * @return void
     * @throws \Exception Is thrown if no user is logged into the system
     */
    public function isLoggedIn()
    {
        if (isset($this->user) === false) {
            throw new \Exception('Please log-in first!');
        }
    }

    /**
     * Returns the user logged into the system.
     *
     * @return \string The user logged into the system
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * A business method protected by an around advice that will query authorization for
     * the users method call by invoking the authorize() method of our interceptor.
     *
     * @return void
     * @Around("advise(AuthorizationInterceptor->authorize())")
     */
    public function protectedMethod()
    {
        $this->getInitialContext()->getSystemLogger()->info(
            sprintf('%s has successfully been invoked', __METHOD__)
        );
    }
}
