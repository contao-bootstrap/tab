<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab;

use ContaoBootstrap\Tab\View\Tab\Item\NavItem;

interface ItemList
{
    /**
     * Add item.
     *
     * @param NavItem $navItem Nav item.
     */
    public function addItem(NavItem $navItem): ItemList;

    /**
     * Get all items.
     *
     * @return NavItem[]
     */
    public function items(): array;
}
