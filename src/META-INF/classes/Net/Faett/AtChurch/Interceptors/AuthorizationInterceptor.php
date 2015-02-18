<?php

/**
 * Net\Faett\AtChurch\Interceptors\AuthorizationInterceptor
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

namespace Net\Faett\AtChurch\Interceptors;

use AppserverIo\Doppelgaenger\Interfaces\MethodInvocationInterface;

/**
 * Interceptor to catch method invocations on proctected methods.
 *
 * @author Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/faett-net/at-church
 *
 * @Aspect
 */
class AuthorizationInterceptor
{

    /**
     * Advice used to check user authorization on method call.
     *
     * @param \AppserverIo\Doppelgaenger\Interfaces\MethodInvocationInterface $methodInvocation Initially invoked method
     *
     * @return void
     */
    public function authorize(MethodInvocation $methodInvocation)
    {

        // load class and method name
        $className = $methodInvocation->getStructureName();
        $methodName = $methodInvocation->getName();

        // load context, a instance of AStatefulSessionBean
        $context = $methodInvocation->getContext();

        // load the application context
        $application = $context->getApplication();

        // load the user logged into the system
        $user = $context->getUser();

        // query whether the user is allowed to invoke the method
        $application->search('AuthorizationSessionBean')->allowed($methodInvocation, $user);

        // log the method invocation
        $methodInvocation->getContext()
            ->getApplication()
            ->getInitialContext()
            ->getSystemLogger()
            ->info(sprintf('The method %s::%s is about to be called by user %s', $className, $methodName, $user->login));
    }
}
