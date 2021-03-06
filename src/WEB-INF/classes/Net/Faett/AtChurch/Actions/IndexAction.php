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
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 */

namespace Net\Faett\AtChurch\Actions;

use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use AppserverIo\Http\HttpProtocol;
use AppserverIo\Routlt\Util\ValidationAware;
use Net\Faett\AtChurch\Util\RequestKeys;
use Net\Faett\AtChurch\OAuth\Common\Storage\Session;
use AppserverIo\Server\Dictionaries\ServerVars;
use AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface;
use AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface;

/**
 * Default action implementation.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @Path(name="/index")
 */
class IndexAction extends AbstractAction
{

    /**
     * The key for the property containing the Github client ID.
     *
     * @var string
     */
    const GITHUB_CLIENT_ID = 'github.client.id';

    /**
     * The key for the property containing the Github client secret.
     *
     * @var string
     */
    const GITHUB_CLIENT_SECRET = 'github.client.secret';

    /**
     * The SFSB to implement authorized access to resources.
     *
     * @var Net\Faett\AtChurch\SessionBeans\ProfileSessionBean
     * @EnterpriseBean
     */
    protected $profileSessionBean;

    /**
     * Default action to invoke if no action parameter has been found in the request.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/index")
     */
    public function indexAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
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
     * This is the action that invokes the Github login.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/login")
     */
    public function loginAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
    {
        try {
            // load the github service
            $gitHub = $this->getGithubService();

            // redirect the the Github authorization URL
            $servletResponse->redirect($gitHub->getAuthorizationUri()->__toString());

            // login use the SFSB
            // $this->profileSessionBean->login(json_decode($gitHub->request('user')));
            // $this->profileSessionBean->protectedMethod();

        // if we've a problem, try to re-login
        } catch (\Exception $e) {
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
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/callback")
     */
    public function callbackAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
    {

        try {
            // query if we've a Github callback code
            if ($servletRequest->hasParameter(RequestKeys::CODE) === false) {
                throw new \Exception(sprintf('Missing "%s" parameter in Github callback', RequestKeys::CODE));
            }

            // if yes, add it to the session
            $this->getGithubService()->requestAccessToken($servletRequest->getParameter(RequestKeys::CODE));
            $this->indexAction($servletRequest, $servletResponse);

        // if we've a problem, try to re-login
        } catch (\Exception $e) {
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
            $servletRequest->getServerVar(ServerVars::HTTP_HOST),
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

        // initialize the service factory
        $serviceFactory = new ServiceFactory();

        // session storage
        $storage = new Session($servletRequest);

        // Setup the credentials for the requests
        $credentials = new Credentials(
            $this->getServletContext()->getInitParameter(IndexAction::GITHUB_CLIENT_ID),
            $this->getServletContext()->getInitParameter(IndexAction::GITHUB_CLIENT_SECRET),
            $this->getCallbackUrl()
        );

        // instantiate the GitHub service using the credentials, http client and storage mechanism for the token
        return $serviceFactory->createService('GitHub', $credentials, $storage, array('user'));
    }
}
