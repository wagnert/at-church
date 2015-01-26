<?php

/**
 * Net\Faett\AtChurch\MessageBeans\AbstractRepositoryMessageBean
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
 */
abstract class AbstractRepositoryMessageBean extends AbstractMessageListener
{

    /**
     * Returns the GIT wrapper instance.
     *
     * @return \GitWrapper\GitWrapper The GIT wrapper instance
     */
    protected function getGitWrapper()
    {
        return new GitWrapper('/usr/bin/git');
    }

    /**
     * Decodes the payload and returns the \stdClass instance.
     *
     * @param \AppserverIo\Psr\Pms\Message $message A message containing the payload
     *
     * @return \stdClass The decoded message
     */
    protected function decodePayload(Message $message)
    {
        return json_decode($message->getMessage());
    }

    /**
     * Returns the tag from the payload.
     *
     * @param \stdClass $payload The payload
     *
     * @return string The tag
     * @throws \Exception Is thrown if the payload doesn't define a tag
     */
    protected function getTag(\stdClass $payload)
    {

        // first query type
        if ($payload->ref_type === 'tag') {
            // if we've a tag, return it
            return $payload->ref;
        }

        // throw an exception if we didn't have a tag
        throw \Exception('Can\'t find tag in payload');
    }

    /**
     * Returns the repositories full name from the payload.
     *
     * @param \stdClass $payload The payload
     *
     * @return string The repositories full name
     * @throws \Exception Is thrown if the payload doesn't define a tag
     */
    protected function getFullName(\stdClass $payload)
    {

        // query for a repository with a full_name
        if (isset($payload->repository->full_name)) {
            return $payload->repository->full_name;
        }

        // throw an exception if we didn't have a full_name
        throw \Exception('Can\'t find full_name in payload');
    }

    /**
     * Prepares the working directory and clones or updates the Github repository
     * specified in the passed message.
     *
     * @param \stdClass $payload The payload
     *
     * @return string The absolute path to the working directory
     * @throws \Exception Is thrown if the working directory can't be created
     */
    protected function prepareWorkingDir(\stdClass $payload)
    {

        // prepare the path of the working directory -> temporary directory to clone/update the GIT repo
        $workingDir = sprintf('%s/%s', $this->getApplication()->getTmpDir(), $payload->repository->full_name);

        // if we've already cloned the repository
        if (is_dir($workingDir)) {
            // reference the working copy and update it
            $this->getGitWrapper()->workingCopy($workingDir)->pull('origin', $payload->master_branch);

        // check if we've a repository URL
        } elseif ($gitUrl = $payload->repository->git_url) {
            // clone the repo into a temporary working directory
            $this->getGitWrapper()->clone($gitUrl, $workingDir);

        // we don't have a working copy nor can we find a repository URL
        } else {
            throw new \Exception('Can\'t find a working copy or a valid respository URL to clone');
        }

        // return the working directory
        return $workingDir;
    }

    /**
     * Prepares the directory. If the directory alreads exists, the directory will be
     * deleted recursively, else it will be created.
     *
     * @param string $dir The directory to be prepared
     *
     * @return void
     */
    protected function prepareDir($dir)
    {
        if (is_dir($dir)) {
            $this->cleanUpDir($dir);
        } else {
            $this->createDir($dir);
        }
    }

    /**
     * Creates the passed directory.
     *
     * @param string $dir Absolute path of the directory to be created
     *
     * @return void
     * @throws \Exception Is thrown if the directory can't be prepared
     */
    protected function createDir($dir)
    {
        if (mkdir($dir, 0755, true) === false) {
            throw new \Exception(sprintf('Directory %s can\'t be created', $dir));
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
    protected function cleanUpDir($dir, $alsoRemoveFiles = true)
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
