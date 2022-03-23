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

use Contao\StringUtil;
use ContaoBootstrap\Tab\View\Tab\Item\Dropdown;
use ContaoBootstrap\Tab\View\Tab\Item\NavItem;

/**
 * Class Navigation
 */
final class Navigation implements ItemList
{
    /**
     * Navigation items.
     *
     * @var array
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
        $navigation = new Navigation();
        $current    = $navigation;
        $definition = StringUtil::deserialize($definition, true);
        $cssIds     = [];

        foreach ($definition as $index => $tab) {
            if (!$tab['cssId']) {
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

                $item = NavItem::fromArray($tab);
                $current->addItem($item);
            }
        }

        return $navigation;
    }

    /**
     * {@inheritdoc}
     */
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
