<?php

/**
 * Net\Faett\AtChurch\Actions\RepositoryAction
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
use Net\Faett\AtChurch\Util\RequestKeys;
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
 * @Path(name="/repository")
 */
class RepositoryAction extends AbstractAction
{

    /**
     * This is a callback action invoked by Github after a event.
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

            // log the POST content
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->info($servletRequest->getBodyContent());

        }  catch (\Exception $e) { // if we've a problem, try to re-login

            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append the exception trace
            $servletResponse->appendBodyStream($e->__toString());
            $servletResponse->setStatusCode(500);
        }
    }
}
