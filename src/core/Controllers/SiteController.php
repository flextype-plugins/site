<?php

declare(strict_types=1);

/**
 * @link https://awilum.github.io/flextype
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flextype\Plugin\Site\Controllers;

use Glowy\View\View;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function ltrim;

class SiteController
{
    /**
     * Index page
     *
     * @param Request  $request  PSR7 request
     * @param Response $response PSR7 response
     * @param array    $args     Args
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, $uri): ResponseInterface
    {
        // Get Query Params.
        $query = $request->getQueryParams();

        // Page format.
        $format = isset($query['format']) ? $query['format'] : 'html';

        // If uri is empty then it is main entry else use entry uri
        if ($uri === '/') {
            $entryUri = registry()->get('plugins.site.settings.entries.main');
        } else {
            $entryUri = ltrim($uri, '/');
        }

        // Get entry body
        $entryBody = entries()->fetch($entryUri)->toArray();

        // is entry not found
        $isEntryNotFound = false;

        // If entry body is not false
        if (is_array($entryBody) and count($entryBody) > 0) {
            // Get 404 page if entry visibility is draft or hidden and if routable is false
            if ((isset($entryBody['visibility']) && ($entryBody['visibility'] === 'draft' || $entryBody['visibility'] === 'hidden')) ||
                (isset($entryBody['routable']) && ($entryBody['routable'] === false))) {
                $entry           = $this->error404();
                $isEntryNotFound = true;
            } else {
                $entry = $entryBody;
            }
        } else {
            $entry           = $this->error404();
            $isEntryNotFound = true;
        }

        // Set template path for current entry
        $path = 'themes/' . registry()->get('plugins.site.settings.theme.name') . '/' . (empty($entry['template']) ? 'templates/' . registry()->get('plugins.site.settings.theme.template.default') : 'templates/' . $entry['template']);

        // Check template file
        if (! file_exists(PATH['project'] . '/' . $path . '.' . registry()->get('plugins.site.settings.theme.template.extension'))) {
            $response->getBody()->write("Template {$entry['template']} not found");
            $response = $response->withStatus(404);
            return $response;
        }

        $data = ['entry' => $entry, 'uri' => $uri, 'request' => $request];

        switch ($format) {
            case 'json':
                if (count($entry) > 0) {
                    $response->getBody()->write(serializers()->json()->encode($entry));
                }
        
                $response = $response->withStatus($isEntryNotFound ? 404 : 200);
                $response = $response->withHeader('Content-Type', 'application/json;charset=' . registry()->get('flextype.settings.charset'));
        
                return $response;
                break;
            
            case 'html':
            default:
     
                switch (registry()->get('plugins.site.settings.theme.template.engine')) {
                    case 'twig':
                        if (registry()->has('plugins.twig')) {
                            $response = twig()->render($isEntryNotFound ? $response->withStatus(404) : $response->withStatus(200), $path . '.' . registry()->get('plugins.site.settings.theme.template.extension'), $data);
                        } else {
                            $response->getBody()->write("Twig plugin not found");
                            $response = $response->withStatus(404);
                            return $response;
                        }
                        break;

                    case 'view': 
                    default:
                        View::setDirectory(PATH['project']);
                        View::setExtension(registry()->get('plugins.site.settings.theme.template.extension'));
                        $response->getBody()->write(view($path)->with($data)->render());
                        $response = $response->withStatus($isEntryNotFound ? 404 : 200);
                        break;
                }
                break;
        }

        return $response;
    }

    /**
     * Error404 page
     *
     * @return array The 404 error entry array data.
     *
     * @access public
     */
    public function error404(): array
    {
        return [
            'title'       => registry()->get('plugins.site.settings.entries.error404.title'),
            'description' => registry()->get('plugins.site.settings.entries.error404.description'),
            'content'     => registry()->get('plugins.site.settings.entries.error404.content'),
            'template'    => registry()->get('plugins.site.settings.entries.error404.template'),
        ];
    }
}
