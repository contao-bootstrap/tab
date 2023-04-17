<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab;

use ContaoBootstrap\Tab\View\Tab\Item\Dropdown;
use ContaoBootstrap\Tab\View\Tab\Item\NavItem;
use Iterator;
use RuntimeException;

use function current;
use function next;

final class NavigationIterator implements Iterator
{
    /**
     * Tab navigation view.
     */
    private Navigation $navigation;

    /**
     * Current items of the tab navigation.
     *
     * @var NavItem[]
     */
    private array $items;

    /**
     * Nav items.
     *
     * @var NavItem[]
     */
    private array $dropdownItems;

    /**
     * Current nav item.
     */
    private ?NavItem $currentItem;

    /**
     * Current dropdown item.
     */
    private ?NavItem $currentDropdownItem;

    public function __construct(Navigation $navigation)
    {
        $this->navigation = $navigation;
        $this->items      = $navigation->items();

        $this->rewind();
    }

    /**
     * Get the tab navigation.
     */
    public function navigation(): Navigation
    {
        return $this->navigation;
    }

    /**
     * Get the current item.
     */
    public function current(): ?NavItem
    {
        if ($this->currentItem instanceof Dropdown) {
            return $this->currentDropdownItem;
        }

        return $this->currentItem;
    }

    /**
     * Get the nex item.
     */
    public function next(): void
    {
        if ($this->currentItem instanceof Dropdown) {
            $this->currentDropdownItem = next($this->dropdownItems) ?: null;

            if ($this->currentDropdownItem) {
                return;
            }
        }

        $this->currentItem = next($this->items) ?: null;

        if ($this->currentItem instanceof Dropdown) {
            $this->dropdownItems       = $this->currentItem->items();
            $this->currentDropdownItem = current($this->dropdownItems) ?: null;
        } else {
            $this->dropdownItems       = [];
            $this->currentDropdownItem = null;
        }
    }

    /**
     * Get the key. Not supported.
     *
     * @return mixed|void
     *
     * @throws RuntimeException Method is not supported.
     */
    public function key()
    {
        throw new RuntimeException('Method key() not supported.');
    }

    public function valid(): bool
    {
        if ($this->currentItem instanceof Dropdown) {
            return $this->currentDropdownItem instanceof NavItem;
        }

        return $this->currentItem instanceof NavItem;
    }

    public function rewind(): void
    {
        $this->items               = $this->navigation->items();
        $this->currentItem         = current($this->items) ?: null;
        $this->dropdownItems       = [];
        $this->currentDropdownItem = null;

        if (! $this->currentItem instanceof Dropdown) {
            return;
        }

        $this->dropdownItems       = $this->currentItem->items();
        $this->currentDropdownItem = current($this->dropdownItems) ?: null;
    }

    /**
     * Get the title of the current item.
     *
     * @return string[]
     */
    public function currentTitle(): array
    {
        if (! $this->currentItem) {
            return [];
        }

        $title = [$this->currentItem->title()];

        if ($this->currentItem instanceof Dropdown && $this->currentDropdownItem) {
            $title[] = $this->currentDropdownItem->title();
        }

        return $title;
    }
}
