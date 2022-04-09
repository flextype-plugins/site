<?php

declare(strict_types=1);

/**
 * @link https://awilum.github.io/flextype
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Site;

use Middlewares\TrailingSlash;
use function is_file;

/**
 * Ensure vendor libraries exist
 */
! is_file($siteAutoload = __DIR__ . '/vendor/autoload.php') and exit('Please run: <i>composer install</i> for site plugin');

/**
 * Register The Auto Loader
 *
 * Composer provides a convenient, automatically generated class loader for
 * our application. We just need to utilize it! We'll simply require it
 * into the script here so that we don't have to worry about manual
 * loading any of our classes later on. It feels nice to relax.
 * Register The Auto Loader
 */
$siteLoader = require_once $siteAutoload;

// Add middleware TrailingSlash for all routes
if (getUriString() !== strings(registry()->get('flextype.settings.base_path'))->append('/')->toString()) {
    app()->add((new TrailingSlash(registry()->get('plugins.site.settings.trailing_slash')))->redirect());
}

// Load routes
require_once __DIR__ . '/src/routes/web.php';