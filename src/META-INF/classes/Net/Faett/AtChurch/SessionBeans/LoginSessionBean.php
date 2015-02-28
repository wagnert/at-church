<?php

/**
 * Net\Faett\AtChurch\SessionBeans\LoginSessionBean
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

/**
 * Session bean handles login functionality.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @Stateful
 */
class LoginSessionBean
{

    /**
     * The application instance.
     *
     * @var \AppserverIo\Psr\Application\ApplicationInterface
     * @Resource(name="ApplicationInterface")
     */
    protected $application;

    /**
     * The username of the user, logged into the system.
     *
     * @var string
     */
    protected $username;

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
     * Logs the user with the passed credentials into the system.
     *
     * @param string $username The username to login with
     * @param string $password The password to login with
     *
     * @return void
     * @todo Login functionality still needs to be implemented
     */
    public function login($username, $password)
    {

        // log the methog invocation
        $this->getInitialContext()->getSystemLogger()->info(
            sprintf('Now try to login with credentials %s/%s', $username, $password)
        );

        // set the username in the SLSB
        $this->username = $username;
    }
}
