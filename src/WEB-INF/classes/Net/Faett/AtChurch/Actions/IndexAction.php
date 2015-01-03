<?php

/**
 * Net\Faett\AtChurch\Actions\IndexAction
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

namespace Net\Faett\AtChurch\Actions;

use AppserverIo\Psr\Servlet\Http\HttpServletRequest;
use AppserverIo\Psr\Servlet\Http\HttpServletResponse;
use AppserverIo\Server\Dictionaries\ServerVars;
use AppserverIo\Http\HttpProtocol;
use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use Net\Faett\AtChurch\OAuth\Common\Storage\Session;

/**
 * Default action implementation.
 *
 * @category   Net
 * @package    Faett
 * @subpackage AtChurch
 * @author     Tim Wagner <wagner_tim78@hotmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/faett-net/at-church
 *
 * @Path(name="/index")
 */
class IndexAction extends AbstractAction
{

    /**
     * Default action to invoke if no action parameter has been found in the request.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequest  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponse $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/index")
     */
    public function indexAction(HttpServletRequest $servletRequest, HttpServletResponse $servletResponse)
    {

        try {

            // load the github service
            $gitHub = $this->getGithubService();

            // load the first mail address from the github account
            $result = json_decode($gitHub->request('user/emails'), true);
            $servletResponse->appendBodyStream('The first email on your github account is ' . $result[0]);

        } catch (\Exception $e) {

            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append a re-login messagge
            $servletResponse->appendBodyStream('<a href="index.do/index/login">Login with Github!</a>');
            $servletResponse->setStatusCode(500);
        }
    }

    /**
     * This is the action invokes the Github login.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequest  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponse $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/login")
     */
    public function loginAction(HttpServletRequest $servletRequest, HttpServletResponse $servletResponse)
    {
        try {

            // redirect the the Github authorization URL
            $servletResponse->setStatusCode(301);
            $servletResponse->addHeader(HttpProtocol::HEADER_LOCATION, $this->getGithubService()->getAuthorizationUri()->__toString());

        }  catch (\Exception $e) { // if we've a problem, try to re-login

            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append a re-login messagge
            $servletResponse->appendBodyStream('<a href="index.do/index/login">Login with Github!</a>');
            $servletResponse->setStatusCode(500);
        }
    }

    /**
     * This is a callback action invoked by Github after successfull login.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequest  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponse $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/callback")
     */
    public function callbackAction(HttpServletRequest $servletRequest, HttpServletResponse $servletResponse)
    {

        try {

            // query if we've a Github callback code
            if (($code = $servletRequest->getParameter('code')) == null) {
                throw new \Exception('Missing "code" parameter in Github callback');
            }

            // if yes, add it to the session
            $this->getGithubService()->requestAccessToken($code);
            $this->indexAction($servletRequest, $servletResponse);

        }  catch (\Exception $e) { // if we've a problem, try to re-login

            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append a re-login messagge
            $servletResponse->appendBodyStream('<a href="index.do/index/login">Login with Github!</a>');
            $servletResponse->setStatusCode(500);
        }
    }

    /**
     * Returns the URL we want to redirect after the Github login.
     *
     * @return string The Github redirect URL
     */
    public function getCallbackUrl()
    {

        // load servlet request
        $servletRequest = $this->getServletRequest();

        // prepare the URL
        return sprintf(
            '%s://%s%s/index/callback',
            $servletRequest->getServerVar(ServerVars::REQUEST_SCHEME),
            $servletRequest->getServerVar(ServerVars::SERVER_NAME),
            $servletRequest->getServerVar(ServerVars::SCRIPT_NAME)
        );
    }

    /**
     * Returns an instance of the Github service we used to load our data.
     *
     * @return \OAuth\Common\Service\ServiceInterface The Github service
     */
    protected function getGithubService()
    {

        // se need the actual request instance
        $servletRequest = $this->getServletRequest();

        // prepare the Github credentials
        $servicesCredentials = array(
            'github' => array(
                'key'       => 'enter-the-client-id-here',
                'secret'    => 'enter-the-client-secret-here'
            )
        );

        // initialize the service factory
        $serviceFactory = new ServiceFactory();

        // session storage
        $storage = new Session($servletRequest);

        // Setup the credentials for the requests
        $credentials = new Credentials(
            $servicesCredentials['github']['key'],
            $servicesCredentials['github']['secret'],
            $callbackUrl = $this->getCallbackUrl()
        );

        // instantiate the GitHub service using the credentials, http client and storage mechanism for the token
        return $serviceFactory->createService('GitHub', $credentials, $storage, array('user'));
    }
}
