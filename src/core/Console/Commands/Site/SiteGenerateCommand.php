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

namespace Flextype\Plugin\Site\Console\Commands\Site;

use Flextype\Plugin\Site\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use function Thermage\div;
use function Thermage\renderToString;

class SiteGenerateCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('site:generate');
        $this->setDescription('Generate site.');
        $this->addOption('site-path', null, InputOption::VALUE_REQUIRED, 'Destination for generated static site files (without trailing and without starting slash)');
        $this->addOption('site-url', null, InputOption::VALUE_REQUIRED, 'Site url (without trailing slash).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('site-path')) {
            $staticSitePath = ROOT_DIR . '/' . $input->getOption('site-path');
        } else {
            $staticSitePath = ROOT_DIR . '/' . registry()->get('plugins.site.settings.static.site_path');
        }

        if ($input->getOption('site-url')) {
            registry()->set('flextype.settings.base_url', $input->getOption('site-url'));
            registry()->set('flextype.settings.base_path', '');
        } else {
            registry()->set('flextype.settings.base_url', registry()->get('plugins.site.settings.static.site_url'));
            registry()->set('flextype.settings.base_path', '');
        }

        $staticSitePathMessage = registry()->get('plugins.site.settings.static.site_path');

        // Site items.
        $items = [];

        $site = new Site();

        $elapsedTimeStartPoint = microtime(true);

        $entries = entries()->fetch('', ['collection' => true, 'find' => ['depth' => '> 0']])
                            ->sortBy('modified_at', 'asc')
                            ->toArray();

        foreach ($entries as $entry) {

            // Check entry visibility field
            if (isset($entry['visibility']) && ($entry['visibility'] === 'draft' || $entry['visibility'] === 'hidden')) {
                continue;
            }

            // Check entry routable field
            if (isset($entry['routable']) && ($entry['routable'] === false)) {
                continue;
            }

            // Check entry ignore field
            if (isset($entry['ignore']) && ($entry['ignore'] === true)) {
                continue;
            }

            // Check ignore list
            if (in_array($entry['id'], (array) registry()->get('plugins.site.settings.static.ignore.entries'))) {
                continue;
            }

            // Add entry to site
            $items[] = $entry;
        }

        // Additions
        $additions = (array) registry()->get('plugins.site.settings.static.additions');
        foreach ($additions as $addition) {
            $items[] = $addition;
        }

        // Set entry to the SitemapController class property $sitemap
        registry()->set('plugins.site.static.entries', $items);

        // Run event onSitemapAfterInitialized
        emitter()->emit('onStaticSiteAfterInitialized');

        // Start site generation process...
        $output->write(
            renderToString(
                div('Static site generation...', 
                    'px-2 pt-1 pb-1')
            )
        );

        // Clear cache.
        if (filesystem()->directory(PATH_TMP)->exists()) {
            filesystem()->directory(PATH_TMP)->delete();
            
            $output->write(
                renderToString(
                    div('[b]Success[/b]: Cache cleared successfully.', 
                        'color-success px-2')
                )
            );    
        }
        
        // Create folder for static site.
        if (filesystem()->directory($staticSitePath)->exists()) {
            filesystem()->directory($staticSitePath)->delete();
            filesystem()->directory($staticSitePath)->create(0755, true);
        } else {
            filesystem()->directory($staticSitePath)->create(0755, true);
        }

        if (! filesystem()->directory($staticSitePath)->exists()) {
            $output->write(
                renderToString(
                    div('[b]Failure[/b]: Static site folder "' . $staticSitePathMessage . '" wasn\'t created.', 
                        'color-danger px-2 pb-2')
                )
            );

            return Command::FAILURE;
        }

        // proceed..
        $output->write(
            renderToString(
                div('[b]Success[/b]: Static site folder "' . $staticSitePathMessage . '" created successfully.', 
                    'color-success px-2')
            )
        );
        
        // Iterate through the pages.
        foreach(registry()->get('plugins.site.static.entries') as $page) {

            // Create page folder.
            filesystem()->directory($staticSitePath . '/' . $page['id'])->ensureExists(0755, true);

            if (! filesystem()->directory($staticSitePath . '/' . $page['id'])->exists()) {
                $output->write(
                    renderToString(
                        div('[b]Failure[/b]: Static site page folder "' . $staticSitePathMessage . '/' . $page['id'] . '" wasn\'t created.', 
                            'color-danger px-2 pb-1')
                    )
                );
    
                return Command::FAILURE;
            }

            $renderedPage = $site->render($page);

            // Save rendered page.
            filesystem()->file($staticSitePath . '/' . $page['id'] . '/index.html')->put($renderedPage);

            if (! filesystem()->file($staticSitePath . '/' . $page['id'])->exists()) {
                $output->write(
                    renderToString(
                        div('[b]Failure[/b]: Page "' . $page['title'] . '" wasn\'t created.', 
                            'color-danger px-2 pb-1')
                    )
                );
    
                return Command::FAILURE;
            }

            $output->write(
                renderToString(
                    div('[b]Success[/b]: Page "' . $page['title'] . '" created successfully.', 
                        ' color-success px-2')
                )
            );
        }

        // Create home page
        filesystem()->file($staticSitePath . '/index.html')->put($site->render(entries()->fetch(registry()->get('plugins.site.settings.entries.main'))->toArray()));

        if (! filesystem()->file($staticSitePath . '/index.html')->exists()) {
            $output->write(
                renderToString(
                    div('[b]Failure[/b]: Home page wasn\'t created.', 
                        'color-danger px-2 pb-1')
                )
            );

            return Command::FAILURE;
        }

        $output->write(
            renderToString(
                div('[b]Success[/b]: Home page created successfully.', 
                    ' color-success px-2')
            )
        );

        // Proceed assets.
        if (filesystem()->directory(PATH_PROJECT . '/assets')->exists()) {
            filesystem()->directory($staticSitePath . '/' . PROJECT_NAME . '/assets')->ensureExists(0755, true);
            
            $directories = [];
            foreach (filesystem()->directory(PATH_PROJECT . '/assets')->directories() as $directory) {
                $directories[] = strings($directory)->replace(PATH_PROJECT . '/assets/', '')->toString();
            }
            
            foreach ($directories as $directory) {
                filesystem()->directory(PATH_PROJECT . '/assets/' . $directory)->copy($staticSitePath . '/' . PROJECT_NAME . '/assets/' . $directory);            
            }

            filesystem()->file($staticSitePath . '/' . PROJECT_NAME . '/index.html')->put('');

            if (! filesystem()->directory($staticSitePath . '/' . PROJECT_NAME . '/assets/')->exists()) {
                $output->write(
                    renderToString(
                        div('[b]Failure[/b]: Assets wasn\'t created.', 
                            'color-danger px-2 pb-1')
                    )
                );

                return Command::FAILURE;
            }

            $output->write(
                renderToString(
                    div('[b]Success[/b]: Assets created successfully.', 
                        ' color-success px-2')
                )
            );
        }
        
        $output->write(
            renderToString(
                div('Site generated in [b]'. sprintf("%01.4f", microtime(true) - $elapsedTimeStartPoint) .'[/b] seconds.', 'px-2 pt-1 pb-1')
            )
        );
        
        return Command::SUCCESS;
    }
}