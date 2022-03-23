<?php

/**
 * Contao Bootstrap
 *
 * @package    contao-bootstrap
 * @subpackage Tab
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2020 netzmacht David Molineus. All rights reserved.
 * @license    LGPL-3.0-or-later https://github.com/contao-bootstrap/tab/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab;

use ContaoBootstrap\Tab\View\Tab\Item\Dropdown;
use ContaoBootstrap\Tab\View\Tab\Item\NavItem;

/**
 * Class NavigationIterator
 *
 * @package ContaoBootstrap\Tab\View\Tab
 */
final class NavigationIterator implements \Iterator
{
    /**
     * Tab navigation view.
     *
     * @var Navigation
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
     *
     * @var NavItem|null
     */
    private ?NavItem $currentItem;

    /**
     * Current dropdown item.
     *
     * @var NavItem|null
     */
    private ?NavItem $currentDropdownItem;

    /**
     * NavigationIterator constructor.
     *
     * @param Navigation $navigation Tab navigation view.
     */
    public function __construct(Navigation $navigation)
    {
        $this->navigation = $navigation;
        $this->items      = $navigation->items();

        $this->rewind();
    }

    /**
     * Get the tab navigation.
     *
     * @return Navigation
     */
    public function navigation(): Navigation
    {
        return $this->navigation;
    }

    /**
     * Get the current item.
     *
     * @return NavItem|null
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
     *
     * @return void
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
            $this->currentDropdownItem = current($this->dropdownItems);
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
     * @throws \RuntimeException Method is not supported.
     */
    public function key()
    {
        throw new \RuntimeException('Method key() not supported.');
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        if ($this->currentItem instanceof Dropdown) {
            return $this->currentDropdownItem instanceof NavItem;
        }

        return $this->currentItem instanceof NavItem;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->items               = $this->navigation->items();
        $this->currentItem         = current($this->items);
        $this->dropdownItems       = [];
        $this->currentDropdownItem = null;

        if ($this->currentItem instanceof Dropdown) {
            $this->dropdownItems       = $this->currentItem->items();
            $this->currentDropdownItem = current($this->dropdownItems);
        }
    }

    /**
     * Get the title of the current item.
     *
     * @return array
     */
    public function currentTitle(): array
    {
        if (!$this->currentItem) {
            return [];
        }

        $title = [$this->currentItem->title()];

        if ($this->currentItem instanceof Dropdown && $this->currentDropdownItem) {
            $title[] = $this->currentDropdownItem->title();
        }

        return $title;
    }
}
