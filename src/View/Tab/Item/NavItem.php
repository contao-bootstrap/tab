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

namespace ContaoBootstrap\Tab\View\Tab\Item;

use Contao\StringUtil;

/**
 * Class Item
 */
class NavItem
{
    /**
     * Navigation item title.
     *
     * @var string
     */
    private string $title;

    /**
     * Css id.
     *
     * @var string
     */
    private string $cssId;

    /**
     * Nav css id.
     *
     * @var string
     */
    private string $navCssId;

    /**
     * Active item.
     *
     * @var bool
     */
    private bool $active;

    /**
     * Item constructor.
     *
     * @param string      $title    Title of the nav item.
     * @param bool        $active   Active state.
     * @param string|null $cssId    Css id.
     * @param string|null $navCssId Nav css id.
     */
    public function __construct(string $title, bool $active = false, string $cssId = null, string $navCssId = null)
    {
        $this->title    = $title;
        $this->cssId    = $cssId ?: StringUtil::standardize($title);
        $this->navCssId = $navCssId ?: $this->cssId . '-tab';
        $this->active   = $active;
    }

    /**
     * Create nav item from an array definition.
     *
     * @param array $definition The array definition.
     *
     * @return NavItem
     */
    public static function fromArray(array $definition): NavItem
    {
        return new static(
            $definition['title'],
            (bool) $definition['active'],
            $definition['cssId'] ?? null,
            $definition['navCssId'] ?? null
        );
    }

    /**
     * Get the title.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Get the navigation item css id.
     *
     * @return string
     */
    public function navCssId(): string
    {
        return $this->navCssId;
    }

    /**
     * Get the css id.
     *
     * @return string
     */
    public function cssId(): string
    {
        return $this->cssId;
    }

    /**
     * Active state.
     *
     * @return bool
     */
    public function active(): bool
    {
        return $this->active;
    }
}
