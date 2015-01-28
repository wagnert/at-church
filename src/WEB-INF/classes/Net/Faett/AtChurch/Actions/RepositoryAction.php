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
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 */

namespace Net\Faett\AtChurch\Actions;

use Net\Faett\AtChurch\Util\RequestKeys;
use AppserverIo\Messaging\StringMessage;
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
     * We actually only support the two events.
     *
     * * z1 -> Github `create` event
     *   When this event is fired, we checkout the tag referenced in the payload and start generating
     *   the API documentation using the latest PHPDocumentor implementation. The result will be
     *   stored in the webapps folder under the <repository-name>/<tag> directory.
     *
     * * z2 -> Github `push` event
     *   This event results in a pull to the latest commit of the `gh-pages` branch and we start to
     *   transform the Github markdown into a HTML page. The result will be stored in the webapps
     *   folder under the <repository-name> directory.
     *
     * All other events will be ignored actually!
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

        // load the content sent by the POST request
        $content = json_decode($message = $servletRequest->getBodyContent());

        // z1 -> we've a tag
        if (isset($content->ref_type) && $content->ref_type === 'tag') {
            // send a message to generate the API documentation
            $this->generateApiSender->send(new StringMessage($message));

            // append a success message and return
            $servletResponse->appendBodyStream('Successfully start to generate API documentation');
            return;
        }

        // z2 -> pushed a commit to gh-pages branch
        if (isset($content->ref_type) === false && $content->ref === 'refs/heads/gh-pages') {
            // send a message to generate the HTML page
            $this->generatePageSender->send(new StringMessage($message));

            // append an success message and return
            $servletResponse->appendBodyStream('Successfully start to generate HTML page');
            return;
        }

        // we didn't support the fired event yet
        $servletResponse->setStatusCode(500);
        $servletResponse->appendBodyStream('Event fired can\'t be handled by this callback');
    }

    /**
     * This is a dummy action that prepares the request with dummy data and invokes the
     * callback action.
     *
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\Http\HttpServletResponseInterface $servletResponse The response instance
     *
     * @return void
     *
     * @Action(name="/dummy")
     */
    public function dummyAction(HttpServletRequestInterface $servletRequest, HttpServletResponseInterface $servletResponse)
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

        // load the dummy data and add it to the request body
        $servletRequest->setBodyStream(file_get_contents($dummyFilename));

        // invoke the callback method
        $this->callbackAction($servletRequest, $servletResponse);
    }
}
