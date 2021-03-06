<?php

declare(strict_types=1);

/**
 * @link https://flextype.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Site;

use Flextype\Plugin\Site\Models\Themes;

/**
 * Add themes service to Flextype container
 */
flextype()->container()['themes'] = fn() => new Themes();

/**
 * Init themes
 */
flextype()->container()['themes']->init();

/**
 * Init current activated theme
 */
$themeBootstrapPath = PATH['project']. '/themes/' . flextype('registry')->get('plugins.site.settings.theme') . '/theme.php';
filesystem()->file($themeBootstrapPath)->exists() and include_once $themeBootstrapPath;
