<?php

/**
 * Net\Faett\AtChurch\Actions\AbstractAction
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

use AppserverIo\Routlt\DispatchAction;
use AppserverIo\Routlt\Util\ServletContextAware;
use AppserverIo\Psr\Servlet\ServletContextInterface;
use AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface;
use AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface;

/**
 * Abstract example implementation that provides some kind of basic MVC functionality
 * to handle requests by subclasses action methods.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 */
abstract class AbstractAction extends DispatchAction implements ServletContextAware
{

    /**
     * The applications base URL.
     *
     * @var string
     */
    const BASE_URL = '/';

    /**
     * The servlet context instance.
     *
     * @var \AppserverIo\Psr\Servlet\ServletContextInterface
     */
    protected $servletContext;

    /**
     * The servlet request instance.
     *
     * @var \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface
     */
    protected $servletRequest;

    /**
     * The servlet response instance.
     *
     * @var \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface
     */
    protected $servletResponse;

    /**
     * This method implements the functionality to invoke a method implemented in its subclass.
     *
     * The method that should be invoked has to be specified by a HTTPServletRequest parameter
     * which name is specified in the configuration file as parameter for the ActionMapping.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     */
    public function perform(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
    {

        // set servlet request/response
        $this->setServletRequest($servletRequest);
        $this->setServletResponse($servletResponse);

        // call parent method
        parent::perform($servletRequest, $servletResponse);
    }

    /**
     * Sets the actual servlet context instance.
     *
     * @param \AppserverIo\Psr\Servlet\ServletContextInterface $servletContext The servlet context instance
     *
     * @return void
     */
    public function setServletContext(ServletContextInterface $servletContext)
    {
        $this->servletContext = $servletContext;
    }

    /**
     * Returns the servlet context instance.
     *
     * @return \AppserverIo\Psr\Servlet\ServletContextInterface The servlet context instance
     */
    public function getServletContext()
    {
        return $this->servletContext;
    }

    /**
     * Sets the servlet request instance.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface $servletRequest The request instance
     *
     * @return void
     */
    public function setServletRequest(HttpServletRequestInterface $servletRequest)
    {
        $this->servletRequest = $servletRequest;
    }

    /**
     * Sets the servlet response instance.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The request instance
     *
     * @return void
     */
    public function setServletResponse(HttpServletResponseInterface $servletResponse)
    {
        $this->servletResponse = $servletResponse;
    }

    /**
     * Returns the servlet response instance.
     *
     * @return \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface The request instance
     */
    public function getServletRequest()
    {
        return $this->servletRequest;
    }

    /**
     * Returns the servlet request instance.
     *
     * @return \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface The response instance
     */
    public function getServletResponse()
    {
        return $this->servletResponse;
    }

    /**
     * Returns base URL for the html base tag.
     *
     * @return string The base URL depending on the vhost
     */
    public function getBaseUrl()
    {

        // if we ARE in a virtual host, return the base URL
        if ($this->getServletRequest()->getContext()->isVhostOf($this->getServletRequest()->getServerName())) {
            return AbstractAction::BASE_URL;
        }

        // if not, prepend it with the context path
        return $this->getServletRequest()->getContextPath() . AbstractAction::BASE_URL;
    }
}
