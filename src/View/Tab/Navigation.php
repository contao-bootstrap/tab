<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab;

use Contao\StringUtil;
use ContaoBootstrap\Tab\View\Tab\Item\Dropdown;
use ContaoBootstrap\Tab\View\Tab\Item\NavItem;

use function in_array;

final class Navigation implements ItemList
{
    /**
     * Navigation items.
     *
     * @var NavItem[]
     */
    private array $items = [];

    /**
     * Create instance from a serialized definition
     *
     * @param string $definition Serialized definition.
     * @param string $tabId      Tab id as string, used as css id suffix.
     *
     * @return Navigation
     */
    public static function fromSerialized(string $definition, string $tabId): self
    {
        $navigation = new self();
        $current    = $navigation;
        $definition = StringUtil::deserialize($definition, true);
        $cssIds     = [];

        foreach ($definition as $index => $tab) {
            if (! $tab['cssId']) {
                $tab['cssId']  = StringUtil::standardize($tab['title']);
                $tab['cssId'] .= '-' . $tabId;

                if (in_array($tab['cssId'], $cssIds)) {
                    $tab['cssId'] .= '-' . $index;
                }
            }

            if ($tab['type'] === 'dropdown') {
                $item    = Dropdown::fromArray($tab);
                $current = $item;

                $navigation->addItem($item);
            } else {
                if ($tab['type'] !== 'child') {
                    $current = $navigation;
                }

                if ($current instanceof ItemList) {
                    $item = NavItem::fromArray($tab);
                    $current->addItem($item);
                }
            }
        }

        return $navigation;
    }

    public function addItem(NavItem $item): ItemList
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function items(): array
    {
        return $this->items;
    }
}
