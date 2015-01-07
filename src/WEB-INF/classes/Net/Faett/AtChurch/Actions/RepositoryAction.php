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

use GitWrapper\GitWrapper;
use Net\Faett\AtChurch\Util\RequestKeys;
use AppserverIo\Server\Dictionaries\ServerVars;
use AppserverIo\Psr\Servlet\Http\HttpServletRequest;
use AppserverIo\Psr\Servlet\Http\HttpServletResponse;

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

            // initialize the GIT wrapper
            $wrapper = new GitWrapper();

            // load the content sent by the POST request
            // $content = json_decode($servletRequest->getBodyContent());
            $content = json_decode(file_get_contents('/tmp/github_push_callback.json'));

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

            // initialize the markdown parser
            $parsedown = new \Parsedown();

            // parse the markdown files and create the HTML code
            foreach (glob($workingCopy .'/*.md') as $sourceFilename) {
                $targetFilename = $webappDir . '/' . strtolower(basename($sourceFilename, 'md')) . 'html';
                file_put_contents($targetFilename, $parsedown->text(file_get_contents($sourceFilename)));
            }

        }  catch (\Exception $e) { // if we've a problem, try to re-login

            // log the exception
            $servletRequest->getContext()->getInitialContext()->getSystemLogger()->error($e->__toString());

            // append the exception trace
            $servletResponse->appendBodyStream($e->__toString());
            $servletResponse->setStatusCode(500);
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
