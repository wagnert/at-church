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

use phpDocumentor\Application;
use AppserverIo\Psr\Pms\Message;

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
class GenerateApiMessageBean extends AbstractRepositoryMessageBean
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

        try {
            // log a message that the message has successfully been received
            $this->getApplication()->getInitialContext()->getSystemLogger()->info('Successfully received message');

            // decode the payload first
            $payload = $this->decodePayload($message);

            // prepare the vendor, target and working directory
            $vendorDir = $this->getVendorDir($payload);
            $targetDir = $this->prepareTargetDir($payload);
            $workingDir = $this->prepareWorkingDir($payload);

            // checkout the tag specified in the payload
            $this->getGitWrapper()->workingCopy($workingDir)->checkout($this->getTag($payload));

            // prepare the $_SERVER variable
            $_SERVER = array(
                'argv' => array(
                    sprintf('%s/bin/phpdoc', $vendorDir),
                    '--title',
                    $this->getFullName($payload),
                    '--target',
                    $targetDir,
                    '--directory',
                    $workingDir,
                    '--ignore',
                    'vendor',
                    '--template',
                    'responsive',
                    '--sourcecode'
                )
            );

            // create a phpDocumentor application instance
            $app = new Application(null, array('composer.vendor_path' => $vendorDir));
            $app->run();

            // update the message monitor for this message
            $this->updateMonitor($message);

        // log an exception if we've a problem
        } catch (\Exception $e) {
            $this->getApplication()->getInitialContext()->getSystemLogger()->error($e->__toString());
        }
    }

    /**
     * Returns the path to the composer vendor directory of this application.
     *
     * @return string The absolute path to the composer vendor directory
     */
    protected function getVendorDir()
    {
        return sprintf('%s/vendor', $this->getApplication()->getWebappPath());
    }

    /**
     * Prepares the target directory where we want to store the generated
     * API documentation later.
     *
     * @param \stdClass $payload The payload
     *
     * @return string The absolute path to the target directory
     * @throws \Exception Is thrown if the target directory can't be prepared
     */
    protected function prepareTargetDir(\stdClass $payload)
    {
        $targetDirectory = sprintf('/opt/appserver/webapps/%s/%s', $this->getFullName($payload), $this->getTag($payload));
        $this->prepareDir($targetDirectory);
        return $targetDirectory;
    }
}
