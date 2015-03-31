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

use Respect\Validation\Validator as v;
use AppserverIo\Routlt\ActionInterface;
use AppserverIo\Routlt\Results\JsonResult;
use AppserverIo\Routlt\Util\ValidationAware;
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
 * @Results({
 *     @Result(name="success", result="/phtml/my_template.phtml", type="AppserverIo\Routlt\Results\JsonResult"),
 *     @Result(name="failure", result="/phtml/my_template.phtml", type="AppserverIo\Routlt\Results\JsonResult")
 * })
 */
class LoginAction extends AbstractAction
{

    /**
     * The session bean that handles the login functionality.
     *
     * @var \Net\Faett\AtChurch\SessionBeans\LoginSessionBean
     * @EnterpriseBean
     */
    protected $loginSessionBean;

    /**
     * The username found in the request.
     *
     * @var string
     */
    protected $username = '';

    /**
     * The password found in the request.
     *
     * @var string
     */
    protected $password = '';

    /**
     * Sets the username found in the request.
     *
     * @param string $username The username
     *
     * @return void
     * @Requires(type="RespectValidation", constraint="v::email()->setName('Username')->check($username)")
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Returns the username found in the request.
     *
     * @return string|null The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the password found in the request.
     *
     * @param string $password The password
     *
     * @return void
     * @Requires(type="RespectValidation", constraint="v::notEmpty()->setName('Password')->check($password)")
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * The password found in the request.
     *
     * @return string|null The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Default action to invoke if no action parameter has been found in the request.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return string|null The action result
     *
     * @Action(name="/index")
     */
    public function indexAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
    {

        try {

            // start the session -> we use a SLSB
            $session = $servletRequest->getSession(true);
            $session->start();

            // load username/attribute
            $username = $this->getUsername();
            $password = $this->getPassword();

            // try to login by invoking the SLSB
            $this->loginSessionBean->login($username, $password);

            // load username/attribute
            $servletRequest->setAttribute(JsonResult::DATA, array('id' => $session->getId(), 'username' => $this->getUsername()));

            // action invocation has been successfull
            return ActionInterface::SUCCESS;

        } catch (\Exception $e) {
            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append the exception the response body
            $this->addFieldError('unknown', $e);

            // action invocation has been successfull
            return ActionInterface::FAILURE;
        }
    }
}
