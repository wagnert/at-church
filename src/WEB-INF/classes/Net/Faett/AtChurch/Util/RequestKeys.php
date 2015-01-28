<?php

/**
 * Net\Faett\AtChurch\Util\RequestKeys
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

namespace Net\Faett\AtChurch\Util;

/**
 * Utility class containing the request keys.
 *
 * @author  Tim Wagner <wagner_tim78@hotmail.com>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://github.com/faett-net/at-church
 */
class RequestKeys
{

    /**
     * Make constructor private to avoid direct initialization.
     */
    private function __construct()
    {
    }

    /**
     * The key for the Github OAuth code passed to the callback.
     *
     * @var string
     */
    const CODE = 'code';
}
