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

use Net\Faett\AtChurch\Util\RequestKeys;
use AppserverIo\Server\Dictionaries\ServerVars;
use AppserverIo\Psr\Servlet\Http\HttpServletRequest;
use AppserverIo\Psr\Servlet\Http\HttpServletResponse;
use AppserverIo\Messaging\StringMessage;

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
     * The queue session for messages that starts page generation on gh-pages branch.
     *
     * @var \AppserverIo\Messaging\QueueSession
     * @Resource(name="pms/generatePage")
     */
    protected $generatePageSender;

    /**
     * The queue session for messages that starts generation of API documentation.
     *
     * @var \AppserverIo\Messaging\QueueSession
     * @Resource(name="pms/generateApi")
     */
    protected $generateApiSender;

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

        // load the content sent by the POST request
        $content = json_decode($message = $servletRequest->getBodyContent());

        // z1 -> we've a tag
        if (isset($content->ref_type) && $content->ref_type === 'tag') {
            // send a message to generate the API documentation
            $this->generateApiSender->send(new StringMessage($message));

            $servletResponse->appendBodyStream('Successfully start to generate API documentation');

            return;
        }

        // z2 -> pushed a commit to master branch
        if (isset($content->ref_type) === false && $content->ref === 'refs/heads/master') {
            // functionality not implemented yet
            $servletResponse->appendBodyStream('Functionality not implemented yet');

            return;
        }

        // z3 -> pushed a commit to gh-pages branch
        if (isset($content->ref_type) === false && $content->ref === 'refs/heads/gh-pages') {
            // send a message to generate the HTML page
            $this->generatePageSender->send(new StringMessage($message));

            $servletResponse->appendBodyStream('Successfully start to generate HTML page');

            return;
        }

        $servletResponse->setStatusCode(500);
        $servletResponse->appendBodyStream('Can\'t load necessary data from payload');
    }

    /**
     * This is a dummy action that prepares the request with dummy data and invokes the
     * callback action.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequest  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponse $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/dummy")
     */
    public function dummyAction(HttpServletRequest $servletRequest, HttpServletResponse $servletResponse)
    {

        // prepare the path to the application
        $webappPath = $servletRequest->getContext()->getWebappPath();

        // prepare the event -> defaults to 'create'
        if ($servletRequest->hasParameter('event')) {
            $event = $servletRequest->getParameter('event');
        } else {
            $event = 'create';
        }

        // prepare the name of the file containing the dummy data
        $dummyFilename = sprintf('%s/META-INF/data/github_%s_callback.json', $webappPath, $event);

        error_log("now try to laod $dummyFilename");

        // load the dummy data and add it to the request body
        $servletRequest->setBodyStream(file_get_contents($dummyFilename));

        // invoke the callback method
        $this->callbackAction($servletRequest, $servletResponse);
    }
}
