<?php

/**
 * Net\Faett\AtChurch\MessageBeans\GenerateApiMessageBean
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

namespace Net\Faett\AtChurch\MessageBeans;

use AppserverIo\Psr\MessageQueueProtocol\Message;
use AppserverIo\Appserver\MessageQueue\Receiver\AbstractReceiver;
use phpDocumentor\Bootstrap;
use phpDocumentor\Application;

/**
 * Clones GIT repository and starts to generate the API documentation.
 *
 * @category   Net
 * @package    Faett
 * @subpackage AtChurch
 * @author     Tim Wagner <wagner_tim78@hotmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/faett-net/at-church
 *
 * @MessageDriven
 */
class GenerateApiMessageBean extends AbstractReceiver
{

    /**
     * Will be invoked when a new message for this message bean will be available.
     *
     * @param \AppserverIo\Psr\MessageQueueProtocol\Message $message   A message this message bean is listen for
     * @param string                                        $sessionId The session ID
     *
     * @return void
     * @see \AppserverIo\Psr\MessageQueueProtocol\Receiver::onMessage()
     */
    public function onMessage(Message $message, $sessionId)
    {

        // log a message that the message has successfully been received
        $this->getApplication()->getInitialContext()->getSystemLogger()->info('Successfully received / finished message');

        // prepare the vendor directory
        $vendorDir = sprintf('%s/vendor', $this->getApplication()->getWebappPath());

        /*
        $_SERVER = array(
            'argv' => array(
                '/Users/wagnert/Documents/Workspace/appserver/master/at-church/src/vendor/bin/phpdoc',
                '--title',
                'faett-net/at-church',
                '--target',
                '/Users/wagnert/Documents/Workspace/appserver/master/at-church/target/vendor/faett-net/at-church/apidoc',
                '--directory',
                '/Users/wagnert/Documents/Workspace/appserver/master/at-church/src',
                '--ignore',
                'vendor',
                '--sourcecode'
            )
        );
        */

        $_SERVER = array(
            'argv' => array(
                '/Users/wagnert/Documents/Workspace/appserver/master/at-church/src/vendor/bin/phpdoc',
                '--title',
                'faett-net/at-church',
                '--target',
                '/tmp',
                '--directory',
                '/Users/wagnert/Documents/Workspace/appserver/master/at-church/src',
                '--ignore',
                'vendor',
                '--sourcecode'
            )
        );

        // create a phpDocumentor application instance
        $bootstrap = Bootstrap::createInstance();
        $app = new Application(null, array('composer.vendor_path' => $vendorDir));
        $app->run();

        // update the message monitor for this message
        $this->updateMonitor($message);
    }
}
