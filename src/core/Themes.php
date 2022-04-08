<?php

declare(strict_types=1);

 /**
 * Flextype - Hybrid Content Management System with the freedom of a headless CMS 
 * and with the full functionality of a traditional CMS!
 * 
 * Copyright (c) Sergey Romanenko (https://awilum.github.io)
 *
 * Licensed under The MIT License.
 *
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 */

namespace Flextype\Plugin\Site;

use RuntimeException;
use function array_merge;
use function array_replace_recursive;
use function count;
use function filemtime;
use function is_array;
use function md5;

class Themes
{
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init themes
     */
    protected function init() : void
    {
        // Set empty themes list item
        registry()->set('themes', []);

        // Get themes list
        $themesList = $this->getThemes();

        // If Themes List isnt empty then continue
        if (! is_array($themesList) || count($themesList) <= 0) {
            return;
        }

        // Get themes cache ID
        $themesCacheID = $this->getThemesCacheID($themesList);

        // Get themes list from cache or scan themes folder and create new themes cache item in the registry
        if (cache()->has($themesCacheID)) {
            registry()->set('themes', cache()->get($themesCacheID));
        } else {
            $themes                 = [];
            $defaultThemeSettings   = [];
            $defaultThemeManifest   = [];

            // Go through the themes list...
            foreach ($themesList as $theme) {

                // Set custom theme directory
                $customThemeSettingsDir = PATH['project'] . '/config/themes/' . $theme['dirname'];

                // Set default theme settings and manifest files
                $defaultThemeSettingsFile = PATH['project'] . '/themes/' . $theme['dirname'] . '/settings.yaml';
                $defaultThemeManifestFile = PATH['project'] . '/themes/' . $theme['dirname'] . '/theme.yaml';

                // Set custom theme settings and manifest files
                $customThemeSettingsFile = PATH['project'] . '/config/themes/' . $theme['dirname'] . '/settings.yaml';

                // Create custom theme settings directory
                ! filesystem()->directory($customThemeSettingsDir)->exists() and filesystem()->directory($customThemeSettingsDir)->create(0755, true);

                // Check if default theme settings file exists
                if (! filesystem()->file($defaultThemeSettingsFile)->exists()) {
                    throw new RuntimeException('Load ' . $theme['dirname'] . ' theme settings - failed!');
                }

                // Get default theme manifest content
                $defaultThemeSettingsFileContent = filesystem()->file($defaultThemeSettingsFile)->get();
                if (trim($defaultThemeSettingsFileContent) === '') {
                    $defaultThemeSettings = [];
                } else {
                    $defaultThemeSettings = serializers()->yaml()->decode($defaultThemeSettingsFileContent);
                }

                // Create custom theme settings file
                ! filesystem()->file($customThemeSettingsFile)->exists() and filesystem()->file($customThemeSettingsFile)->put($defaultThemeSettingsFileContent); 

                // Get custom theme settings content
                $customThemeSettingsFileContent = filesystem()->file($customThemeSettingsFile)->get();
                if (trim($customThemeSettingsFileContent) === '') {
                    $customThemeSettings = [];
                } else {
                    $customThemeSettings = serializers()->yaml()->decode($customThemeSettingsFileContent);
                }

                // Check if default theme manifest file exists
                if (! filesystem()->file($defaultThemeManifestFile)->exists()) {
                    RuntimeException('Load ' . $theme['dirname'] . ' theme manifest - failed!');
                }

                // Get default theme manifest content
                $defaultThemeManifestFileContent = filesystem()->file($defaultThemeManifestFile)->get();
                $defaultThemeManifest          = serializers()->yaml()->decode($defaultThemeManifestFileContent);

                // Merge theme settings and manifest data
                $themes[$theme['dirname']]['manifest'] = $defaultThemeManifest;
                $themes[$theme['dirname']]['settings'] = array_replace_recursive($defaultThemeSettings, $customThemeSettings);

            }

            // Save parsed themes list in the registry themes
            registry()->set('themes', $themes);

            // Save parsed themes list in the cache
            cache()->set($themesCacheID, $themes);
        }

        // Emit onThemesInitialized
        emitter()->emit('onThemesInitialized');
    }

    /**
     * Get Themes Cache ID
     *
     * @param  array $themesList Themes list
     *
     * @access public
     */
    public function getThemesCacheID(array $themesList) : string
    {
        // Themes Cache ID
        $_themesCacheID = '';

        // Go through...
        if (is_array($themesList) && count($themesList) > 0) {
            foreach ($themesList as $theme) {
                $defaultThemeSettingsFile = PATH['project'] . '/themes/' . $theme['dirname'] . '/settings.yaml';
                $defaultThemeManifestFile = PATH['project'] . '/themes/' . $theme['dirname'] . '/theme.yaml';
                $siteThemeSettingsFile    = PATH['project'] . '/config/themes/' . $theme['dirname'] . '/settings.yaml';

                $f1 = filesystem()->file($defaultThemeSettingsFile)->exists() ? filemtime($defaultThemeSettingsFile) : '';
                $f2 = filesystem()->file($defaultThemeManifestFile)->exists() ? filemtime($defaultThemeManifestFile) : '';
                $f3 = filesystem()->file($siteThemeSettingsFile)->exists() ? filemtime($siteThemeSettingsFile) : '';

                $_themesCacheID .= $f1 . $f2 . $f3;
            }
        }

        // Create Unique Cache ID for Themes
        $themesCacheID = md5('themes' . PATH['project'] . '/themes/' . $_themesCacheID);

        // Return themes cache id
        return $themesCacheID;
    }

    /**
     * Get list of themes
     *
     * @return array
     *
     * @access public
     */
    public function getThemes(): array
    {
        $themes = [];

        foreach (filesystem()->find()->in(PATH['project'] . '/themes/')->directories()->depth(0) as $theme) {
            
            if (! filesystem()->file($theme->getPathname() . '/theme.yaml')->exists()) {
                continue;
            }

            $themes[$theme->getBasename()] = ['dirname' => $theme->getBasename(), 'pathname' => $theme->getPathname()];
        }

        return $themes;
    }

    /**
     * Get partials for theme
     *
     * @param string $theme Theme id
     *
     * @return array
     *
     * @access public
     */
    public function getPartials(string $theme): array
    {
        $partials = [];

        foreach (filesystem()->find()->in(PATH['project'] . '/themes/' . $theme . '/partials')->files()->depth(0) as $theme) {
            if (! filesystem()->file($theme['path'] . '/' . 'theme.yaml')->exists()) {
                continue;
            }

            $themes[] = $theme;
        }

        return $themes;

        // Init partials list
        $partialsList = [];

        // Get partials files
        $_partialsList = Filesystem::listContents(PATH['project'] . '/themes/' . $theme . '/templates/partials/');

        // If there is any partials file then go...
        if (count($_partialsList) > 0) {
            foreach ($_partialsList as $partial) {
                if ($partial['type'] !== 'file' || $partial['extension'] !== registry()->get('plugins.site.settings.theme.template.extension')) {
                    continue;
                }

                $partialsList[] = $partial;
            }
        }

        // return partials
        return $partialsList;
    }

    /**
     * Get templates for theme
     *
     * @param string $theme Theme id
     *
     * @return array
     *
     * @access public
     */
    public function getTemplates(string $theme) : array
    {
        // Init templates list
        $templates_list = [];

        // Get templates files
        $_templates_list = Filesystem::listContents(PATH['project'] . '/themes/' . $theme . '/templates/');

        // If there is any template file then go...
        if (count($_templates_list) > 0) {
            foreach ($_templates_list as $template) {
                if ($template['type'] !== 'file' || $template['extension'] !== registry()->get('plugins.site.settings.theme.template.extension')) {
                    continue;
                }

                $templates_list[] = $template;
            }
        }

        // return templates
        return $templates_list;
    }
}
