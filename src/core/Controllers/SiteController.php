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
use Glowy\Macroable\Macroable;
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
        $template = isset($entry['template']) ? $entry['template'] : registry()->get('plugins.site.settings.templates.default');
                
        // Check template file
        if (! file_exists(PATH_PROJECT . '/' . registry()->get('plugins.site.settings.templates.directory') . '/' . $template . '.' . registry()->get('plugins.site.settings.templates.extension'))) {
            $response->getBody()->write("Template {$template} not found");
            $response = $response->withStatus(404);
            return $response;
        }

        $data   = ['entry' => $entry, 'uri' => $uri, 'request' => $request];
        $status = $isEntryNotFound ? 404 : 200;

        switch ($format) {
            case 'json':
                if (count($entry) > 0) {
                    $response->getBody()->write(serializers()->json()->encode($entry));
                }
        
                $response = $response->withStatus($status);
                $response = $response->withHeader('Content-Type', 'application/json;charset=' . registry()->get('flextype.settings.charset'));
        
                return $response;
                break;
            case 'html':
            default:
                return $this->render($response, $template, $entryUri, $data, $status);
                break;
        }

        return $response;
    }

    /**
     * Fetch template
     */
    public function fetch($template, $data)
    {
        $template = registry()->get('plugins.site.settings.templates.directory') . '/' . $template;

        switch (registry()->get('plugins.site.settings.templates.engine')) {
            case 'twig':
                return twig()->fetch($template . '.'. registry()->get('plugins.site.settings.templates.extension'), $data);
                break;

            case 'view':
            default: 
                View::setExtension(registry()->get('plugins.site.settings.templates.extension'));
                return view($template)->fetch($template, $data);
                break;
        }
    }

    /**
     * Render template
     */
    public function render($response, $template, $entryUri, $data, $status) 
    {
        if (registry()->get('plugins.site.settings.cache.enabled')) {
            filesystem()->directory(PATH_TMP . '/site/')->ensureExists();

            $cacheFileID = PATH_TMP . '/site/' . $this->getCacheID($entryUri) . '.html';

            if (filesystem()->file($cacheFileID)->exists()) {
                $renderedTemplate = filesystem()->file(PATH_TMP . '/site/' . $this->getCacheID($entryUri) . '.html')->get();
            } else {
                $renderedTemplate = $this->fetch($template, $data);
                filesystem()->file(PATH_TMP . '/site/' . $this->getCacheID($entryUri) . '.html')->put($renderedTemplate);
            }
            
        } else {
            $renderedTemplate = $this->fetch($template, $data);
        }

        $response->getBody()->write($renderedTemplate);
        $response = $response->withStatus($status);
        return $response;
    }

    /**
     * Get Cache ID for entry.
     *
     * @param  string $id Unique identifier of the entry.
     *
     * @return string Cache ID.
     *
     * @access public
     */
    public function getCacheID(string $id): string
    {
        return strings(registry()->get('plugins.site.settings.templates.directory') .
                       registry()->get('plugins.site.settings.templates.engine') .
                       registry()->get('plugins.site.settings.templates.extension') . 
                       $id)->hash()->toString();
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
