<?php

/*
 * This file is part of the 'octris/container' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Octris\Container;

/**
 * NotFoundException.
 *
 * @copyright   copyright (c) 2018 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class NotFoundException extends \RuntimeException implements \Psr\Container\NotFoundExceptionInterface {
}
