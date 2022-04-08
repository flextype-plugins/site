<?php

declare(strict_types=1);

if (! function_exists('themes')) {
    function themes()
    {
        return container()->get('themes');
    }
}
