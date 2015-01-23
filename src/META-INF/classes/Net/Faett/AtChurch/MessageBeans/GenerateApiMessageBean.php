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

use GitWrapper\GitWrapper;
use phpDocumentor\Bootstrap;
use phpDocumentor\Application;
use AppserverIo\Psr\Pms\Message;
use AppserverIo\Messaging\AbstractMessageListener;

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
class GenerateApiMessageBean extends AbstractMessageListener
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
            $this->getApplication()->getInitialContext()->getSystemLogger()->info('Successfully received / finished message');

            // initialize the GIT wrapper
            $wrapper = new GitWrapper('/usr/bin/git');

            // load the content from the message
            $content = json_decode($message->getMessage());

            // if we've already cloned the repository
            if (is_dir($workingCopy = '/tmp/' . $content->repository->full_name)) {

                // reference the working copy
                $git = $wrapper->workingCopy($workingCopy);

            } elseif ($gitUrl = $content->repository->git_url) { // check if we've a repository URL

                // clone the repo into a temporary working directory
                $git = $wrapper->clone($gitUrl, $workingCopy);

            } else { // we don't have a working copy nor can we find a repository URL
                throw new \Exception('Can\'t find a working copy or a valid respository URL to clone');
            }

            // prepare the target directory
            $webappDir = '/opt/appserver/webapps/' . $content->repository->full_name;

            // create/clean up the target directory
            if (is_dir($webappDir)) {
                $this->cleanUpDir($webappDir);
            } else {
                mkdir($webappDir, 0755, true);
            }

            // prepare the vendor directory
            $vendorDir = sprintf('%s/vendor', $this->getApplication()->getWebappPath());

            // prepare the $_SERVER variable
            $_SERVER = array(
                'argv' => array(
                    sprintf('%s/bin/phpdoc', $vendorDir),
                    '--title',
                    $content->repository->full_name,
                    '--target',
                    $webappDir,
                    '--directory',
                    $workingCopy,
                    '--ignore',
                    'vendor',
                    '--sourcecode',
                    '--template',
                    'responsive'
                )
            );

            error_log(print_r($_SERVER, true));

            // create a phpDocumentor application instance
            $bootstrap = Bootstrap::createInstance();
            $app = new Application(null, array('composer.vendor_path' => $vendorDir));
            $app->run();

            // update the message monitor for this message
            $this->updateMonitor($message);

        } catch (\Exception $e) { // if we've a problem, log an exception
            $this->getApplication()->getInitialContext()->getSystemLogger()->error($e->__toString());
        }
    }

    /**
     * Deletes all files and subdirectories from the passed directory.
     *
     * @param string $dir             The directory to remove
     * @param bool   $alsoRemoveFiles The flag for removing files also
     *
     * @return void
     */
    public function cleanUpDir($dir, $alsoRemoveFiles = true)
    {

        // first check if the directory exists, if not return immediately
        if (is_dir($dir) === false) {
            return;
        }

        // remove old archive from webapps folder recursively
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            // skip . and .. dirs
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } elseif ($file->isFile() && $alsoRemoveFiles) {
                unlink($file->getRealPath());
            } else {
                // do nothing, because file should NOT be deleted obviously
            }
        }
    }
}
