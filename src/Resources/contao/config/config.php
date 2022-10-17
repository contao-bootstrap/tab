<?php

declare(strict_types=1);

use ContaoBootstrap\Tab\Component\ContentElement\TabEndElementController;
use ContaoBootstrap\Tab\Component\ContentElement\TabSeparatorElementController;
use ContaoBootstrap\Tab\Component\ContentElement\TabStartElementController;

// Content elements
$GLOBALS['TL_CTE']['bootstrap_tabs']['bs_tab_start']     = TabStartElementController::class;
$GLOBALS['TL_CTE']['bootstrap_tabs']['bs_tab_separator'] = TabSeparatorElementController::class;
$GLOBALS['TL_CTE']['bootstrap_tabs']['bs_tab_end']       = TabEndElementController::class;

// Wrapper settings
$GLOBALS['TL_WRAPPERS']['start'][]     = 'bs_tab_start';
$GLOBALS['TL_WRAPPERS']['separator'][] = 'bs_tab_separator';
$GLOBALS['TL_WRAPPERS']['stop'][]      = 'bs_tab_end';
