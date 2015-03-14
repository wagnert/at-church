<?php

/**
 * Net\Faett\AtChurch\Actions\LoginAction
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
 * Default login action implementation.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @Path(name="/login")
 */
class LoginAction extends XhrAbstractAction
{

    /**
     * The session bean that handles the login functionality.
     *
     * @var \Net\Faett\AtChurch\SessionBeans\LoginSessionBean
     * @EnterpriseBean
     */
    protected $loginSessionBean;

    /**
     * Default action to invoke if no action parameter has been found in the request.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/index")
     * @Ensures("is_string(filter_var($this->getAttribute('username'), FILTER_VALIDATE_EMAIL))")
     * @Ensures("is_string(filter_var($this->getAttribute('password'), FILTER_SANITIZE_STRING))")
     */
    public function indexAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
    {

        try {

            // start the session -> we use a SLSB
            $session = $servletRequest->getSession(true);
            $session->start();

            // load username/attribute
            $username = $this->getAttribute('username');
            $password = $this->getAttribute('password');

            // try to login by invoking the SLSB
            $this->loginSessionBean->login($username, $password);

            return array(
                'id' => $session->getId(),
                'username' => $username
            );

        } catch (\Exception $e) {
            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append the exception the response body
            $servletResponse->appendBodyStream($e->__toString());
            $servletResponse->setStatusCode(500);
        }
    }
}
