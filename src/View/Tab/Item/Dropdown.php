<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab\Item;

use ContaoBootstrap\Tab\View\Tab\ItemList;
use Override;

final class Dropdown extends NavItem implements ItemList
{
    /**
     * Dropdown items.
     *
     * @var list<NavItem>
     */
    private array $items = [];

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function items(): array
    {
        return $this->items;
    }

    #[Override]
    public function addItem(NavItem $item): ItemList
    {
        $this->items[] = $item;

        return $this;
    }

    #[Override]
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
