<?php

/**
 * Net\Faett\AtChurch\MessageBeans\GeneratePageMessageBean
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

namespace Net\Faett\AtChurch\MessageBeans;

use AppserverIo\Psr\Pms\MessageInterface;

/**
 * Clones GIT repository, checkout the gh-pages branch and starts to generate the page.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 *
 * @MessageDriven
 */
class GeneratePageMessageBean extends AbstractRepositoryMessageBean
{

    /**
     * Will be invoked when a new message for this message bean will be available.
     *
     * @param \AppserverIo\Psr\Pms\MessageInterface $message   A message this message bean is listen for
     * @param string                                $sessionId The session ID
     *
     * @return void
     * @see \AppserverIo\Psr\Pms\MessageListenerInterface::onMessage()
     */
    public function onMessage(MessageInterface $message, $sessionId)
    {

        try {
            // log a message that the message has successfully been received
            $this->getApplication()->getInitialContext()->getSystemLogger()->info('Successfully received message');

            // decode the payload first
            $payload = $this->decodePayload($message);

            // prepare the target and working directory
            $workingDirectory = $this->prepareWorkingDir($payload);
            $targetDirectory = $this->prepareTargetDir($payload);

            // initialize the markdown parser
            $parsedown = new \Parsedown();

            // parse the markdown files and create the HTML code
            foreach (glob($workingDirectory .'/*.md') as $sourceFilename) {
                $targetFilename = $targetDirectory . '/' . strtolower(basename($sourceFilename, 'md')) . 'html';
                file_put_contents($targetFilename, $parsedown->text(file_get_contents($sourceFilename)));
            }

            // update the message monitor for this message
            $this->updateMonitor($message);

        // if we've a problem, log an exception
        } catch (\Exception $e) {
            $this->getApplication()->getInitialContext()->getSystemLogger()->error($e->__toString());
        }
    }

    /**
     * Prepares the target directory where we want to store the generated
     * HTML pages later.
     *
     * @param \stdClass $payload The payload
     *
     * @return string The absolute path to the target directory
     * @throws \Exception Is thrown if the target directory can't be prepared
     */
    protected function prepareTargetDir(\stdClass $payload)
    {
        $targetDirectory = sprintf('%s/%s', $this->getApplication()->getAppBase(), $this->getFullName($payload));
        $this->prepareDir($targetDirectory);
        return $targetDirectory;
    }
}
