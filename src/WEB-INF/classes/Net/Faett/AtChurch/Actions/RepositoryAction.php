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
use AppserverIo\Psr\MessageQueueProtocol\Messages\StringMessage;

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
     * @var \AppserverIo\MessageQueueClient\QueueSession
     * @Resource(name="pms/generatePage")
     */
    protected $generatePageSender;

    /**
     * The queue session for messages that starts generation of API documentation.
     *
     * @var \AppserverIo\MessageQueueClient\QueueSession
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
        // $content = json_decode($message = file_get_contents('/tmp/github_push_callback.json'));

        switch ($content->ref) {

            case 'refs/heads/master':

                $this->generateApiSender->send(new StringMessage($message));
                break;

            case 'refs/heads/gh-pages':

                $this->generatePageSender->send(new StringMessage($message));
                break;

            default:

                break;

        }
    }
}
