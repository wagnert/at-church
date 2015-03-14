<?php

/**
 * Net\Faett\AtChurch\Interceptors\XhrRequestInterceptor
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

use AppserverIo\Psr\MetaobjectProtocol\Aop\MethodInvocationInterface;
use AppserverIo\Psr\MetaobjectProtocol\Dbc\ContractExceptionInterface;

/**
 * Interceptor to catch action invocations.
 *
 * @author Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/faett-net/at-church
 *
 * @Aspect
 */
class XhrRequestInterceptor
{

    /**
     * This is a dummy method needed to specify a pointcut.
     *
     * @return void
     *
     * @Pointcut("call(\Net\Faett\AtChurch\Actions\XhrAbstractAction->perform())")
     */
    public function thePerformMethod() {}

    /**
     * This is a dummy method needed to specify a pointcut.
     *
     * @return void
     *
     * @Pointcut("call(\Net\Faett\AtChurch\Actions\LoginAction->*Action())")
     */
    public function allActionMethods() {}

    /**
     * @Around("pointcut(thePerformMethod())")
     */
    public function handlePerformMethod(MethodInvocationInterface $methodInvocation)
    {

        try {
            // get servlet method params to local refs
            $parameters = $methodInvocation->getParameters();
            $servletRequest = $parameters['servletRequest'];
            $servletResponse = $parameters['servletResponse'];

            // proceed invocation chain
            $responseJsonObject = $methodInvocation->proceed();

        } catch (ContractExceptionInterface $cei) {

            // set the status code
            $servletResponse->setStatusCode(
                $cei->getCode() ? $cei->getCode() : 400
            );

            // create error JSON response object
            $responseJsonObject = new \stdClass();
            $responseJsonObject->error = new \stdClass();
            $responseJsonObject->error->message = nl2br($cei->getMessage());
        }

        // add json encoded string to response body stream
        $servletResponse->appendBodyStream(json_encode($responseJsonObject));
    }

    /**
     * Advice that handles a XHR Request.
     *
     * @param AppserverIo\Psr\MetaobjectProtocol\Aop\MethodInvocationInterface $methodInvocation Initially invoked method
     *
     * @return void
     *
     * @Around("pointcut(allActionMethods())")
     */
    public function handleRequest(MethodInvocationInterface $methodInvocation)
    {

        try {
            // get servlet method params to local refs
            $parameters = $methodInvocation->getParameters();
            $servletRequest = $parameters['servletRequest'];
            $servletResponse = $parameters['servletResponse'];

            // only if request has valid json
            if (is_object(json_decode($servletRequest->getBodyContent())) === false) {
                throw new \Exception('Invalid request format', 400);
            }

            // set json parsed object data in the action context
            foreach (json_decode($servletRequest->getBodyContent()) as $key => $value) {
                $methodInvocation->getContext()->setAttribute($key, $value);
            }

            // proceed invocation chain
            $responseJsonObject = $methodInvocation->proceed();

        } catch (\Exception $e) {
            // set the status code
            $servletResponse->setStatusCode(
                $e->getCode() ? $e->getCode() : 400
            );

            // create error JSON response object
            $responseJsonObject = new \stdClass();
            $responseJsonObject->error = new \stdClass();
            $responseJsonObject->error->message = nl2br($e->getMessage());
        }

        // add json encoded string to response body stream
        $servletResponse->appendBodyStream(json_encode($responseJsonObject));
    }
}
