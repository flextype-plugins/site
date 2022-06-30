<?php

namespace Flextype\Plugin\Site;

class Site {
    /**
     * Fetch.
     */
    public function fetch(array $data)
    {
        $template = isset($data['template']) ? $data['template'] : registry()->get('plugins.site.settings.templates.default');
        $template = registry()->get('plugins.site.settings.templates.directory') . '/' .  $template;

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
     * Render response.
     */
    public function renderResponse($response, array $data) 
    {
        $response->getBody()->write($this->render($data));
        $response = $response->withStatus($data['http_status_code']);
        return $response;
    }

    /**
     * Render.
     */
    public function render(array $data) 
    {
        if (registry()->get('plugins.site.settings.cache.enabled')) {
            filesystem()->directory(PATH_TMP . '/site/')->ensureExists(0755, true);

            $cacheFileID = PATH_TMP . '/site/' . $this->getCacheID($data['id']) . '.html';

            if (filesystem()->file($cacheFileID)->exists()) {
                $renderedTemplate = filesystem()->file(PATH_TMP . '/site/' . $this->getCacheID($data['id']) . '.html')->get();
            } else {
                $renderedTemplate = $this->fetch($data);
                filesystem()->file(PATH_TMP . '/site/' . $this->getCacheID($data['id']) . '.html')->put($renderedTemplate);
            }
            
        } else {
            $renderedTemplate = $this->fetch($data);
        }

 
        return $renderedTemplate;
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
            'title'            => registry()->get('plugins.site.settings.entries.error404.title'),
            'description'      => registry()->get('plugins.site.settings.entries.error404.description'),
            'content'          => registry()->get('plugins.site.settings.entries.error404.content'),
            'template'         => registry()->get('plugins.site.settings.entries.error404.template'),
            'http_status_code' => 404,
        ];
    }
}