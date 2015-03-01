<?php

/**
 * Net\Faett\AtChurch\Actions\TestAction
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

namespace Net\Faett\AtChurch\Actions;

use AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface;
use AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface;

/**
 * Default test action implementation.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @Path(name="/test")
 */
class TestAction extends AbstractAction
{

    /**
     * Default action to invoke if no action parameter has been found in the request.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/gaga")
     */
    public function indexAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
    {

        try {

            // add the famous 'Hello World' to the response
            $servletResponse->appendBodyStream(
                sprintf('Hello World! <a href="%s/%s">Reload self</a>', $this->getBaseUrl(), 'index.do/test')
            );

        } catch (\Exception $e) {
            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append a re-login messagge
            $servletResponse->appendBodyStream('<a href="index.do/index/login">Login with Github!</a>');
            $servletResponse->setStatusCode(500);
        }
    }
}
