<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab\Item;

use ContaoBootstrap\Tab\View\Tab\ItemList;

final class Dropdown extends NavItem implements ItemList
{
    /**
     * Dropdown items.
     *
     * @var array|NavItem[]
     */
    private array $items = [];

    /**
     * {@inheritDoc}
     */
    public function items(): array
    {
        return $this->items;
    }

    public function addItem(NavItem $item): ItemList
    {
        $this->items[] = $item;

        return $this;
    }

    public function active(): bool
    {
        foreach ($this->items as $item) {
            if ($item->active()) {
                return true;
            }
        }

        return false;
    }
}
