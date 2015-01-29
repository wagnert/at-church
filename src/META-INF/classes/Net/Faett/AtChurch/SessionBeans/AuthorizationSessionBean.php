<?php

/**
 * Net\Faett\AtChurch\SessionBeans\AuthorizationSessionBean
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

use AppserverIo\Doppelgaenger\Entities\MethodInvocation;

/**
 * Session bean that implements ACLs to secure method calls.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @Stateless
 */
class AuthorizationSessionBean
{

    /**
     * The application instance.
     *
     * @var \AppserverIo\Psr\Application\ApplicationInterface
     * @Resource(name="ApplicationInterface")
     */
    protected $application;

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
     * Queries against the ACLs whether the user is allowed to invoke the method specified
     * by the passed method invocation instance.
     *
     * @param \AppserverIo\Doppelgaenger\Entities\MethodInvocation $methodInvocation Initially invoked method
     * @param \stdClass                                            $user             The user to login
     *
     * @return void
     * @throws \Exception Is thrown if the user is not allowed to invoke the passed method
     */
    public function allowed(MethodInvocation $methodInvocation, \stdClass $user)
    {
        $this->getInitialContext()->getSystemLogger()->info(
            sprintf('%s has successfully been invoked', __METHOD__)
        );
    }
}
