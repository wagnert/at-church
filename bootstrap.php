<?php

/**
 * bootstrap.php
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

$loader = require 'src/vendor/autoload.php';
$loader->add('Net\\Faett\\AtChurch', array('src/common/classes', 'src/META-INF/classes', 'src/WEB-INF/classes'));
