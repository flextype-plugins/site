<?php

declare(strict_types=1);

/**
 * @link https://flextype.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Site;

use Flextype\Plugin\Site\Controllers\SiteController;

/**
 * Add site controller to Flextype container
 */
$flextype['SiteController'] = static function ($container) {
    return new SiteController($container);
};
