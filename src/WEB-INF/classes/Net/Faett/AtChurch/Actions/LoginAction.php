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
 *     @Result(name="success", result="/phtml/my_template.phtml", type="AppserverIo\Routlt\Results\ServletDispatcherResult"),
 *     @Result(name="failure", result="/phtml/my_template.phtml", type="AppserverIo\Routlt\Results\ServletDispatcherResult")
 * })
 */
class LoginAction extends AbstractAction implements ValidationAware
{

    const SUCCESS = 'success';

    const FAILURE = 'failure';

    /**
     * The session bean that handles the login functionality.
     *
     * @var \Net\Faett\AtChurch\SessionBeans\LoginSessionBean
     * @EnterpriseBean
     */
    protected $loginSessionBean;

    protected $errors = array();

    protected $results = array();

    protected $username = '';

    protected $password = '';

    /**
     * @Requires(type="RespectValidation", constraint="v::email()->setName('Username')->check($username)")
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @Requires(type="RespectValidation", constraint="v::notEmpty()->setName('Password')->check($password)")
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function addResult($result)
    {
        $this->results[$result->getName()] = $result;
    }

    public function findResult($name)
    {
        if (isset($this->results[$name])) {
            return $this->results[$name];
        }
    }

    public function addFieldError($fieldName, $e)
    {
        $this->errors[$fieldName] = $e->getMessage();
    }

    public function hasErrors()
    {
        return sizeof($this->errors) > 0;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /*
    public function validate()
    {

        $errorMessages = array();

        if (filter_var($this->getUsername(), FILTER_VALIDATE_EMAIL) === false) {
            $errorMessages['username'] = 'Username is not a valid mail address';
        }
    }
    */

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
            $servletRequest->setAttribute('responseData', array('id' => $session->getId(), 'username' => $this->getUsername()));

            // return the path to the PHTML template
            return LoginAction::SUCCESS;

        } catch (\Exception $e) {
            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append the exception the response body
            $servletResponse->appendBodyStream($e->__toString());
            $servletResponse->setStatusCode(500);
        }
    }
}
