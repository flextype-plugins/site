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

namespace Flextype\Plugin\Site\Console\Commands\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use function Thermage\div;
use function Thermage\renderToString;

class CacheClearSiteStaticCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cache:clear-site-static');
        $this->setDescription('Clear cache site static.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = PATH_TMP . '/site';

        if (filesystem()->directory($configPath)->exists()) {
            if (filesystem()->directory($configPath)->delete()) {
                $output->write(
                    renderToString(
                        div('Site static were successfully cleared from the cache.', 
                            'color-success px-2 py-1')
                    )
                );
                $result = Command::SUCCESS;
            } else {
                $output->write(
                    renderToString(
                        div('Site static cache wasn\'t cleared.', 
                            'color-danger px-2 py-1')
                    )
                );
                $result = Command::FAILURE;
            }
        } else {
            $output->write(
                renderToString(
                    div('Site static cache directory ' . $configPath . ' doesn\'t exist.', 
                        'color-danger px-2 py-1')
                )
            );
            $result = Command::FAILURE;
        }

        return $result;
    }
}