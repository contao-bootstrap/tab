<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab\Item;

use Contao\StringUtil;

class NavItem
{
    /**
     * Css id.
     */
    private readonly string $cssId;

    /**
     * Nav css id.
     */
    private readonly string $navCssId;

    /**
     * @param string      $title    Title of the nav item.
     * @param bool        $active   Active state.
     * @param string|null $cssId    Css id.
     * @param string|null $navCssId Nav css id.
     */
    public function __construct(
        private readonly string $title,
        private readonly bool $active = false,
        string|null $cssId = null,
        string|null $navCssId = null,
    ) {
        $this->cssId    = $cssId ?: StringUtil::standardize($title);
        $this->navCssId = $navCssId ?: $this->cssId . '-tab';
    }

    /**
     * Create nav item from an array definition.
     *
     * @param array<string,mixed> $definition The array definition.
     */
    public static function fromArray(array $definition): NavItem
    {
        /** @psalm-suppress UnsafeInstantiation */

        return new static(
            $definition['title'],
            (bool) $definition['active'],
            $definition['cssId'] ?? null,
            $definition['navCssId'] ?? null,
        );
    }

    /**
     * Get the title.
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Get the navigation item css id.
     */
    public function navCssId(): string
    {
        return $this->navCssId;
    }

    /**
     * Get the css id.
     */
    public function cssId(): string
    {
        return $this->cssId;
    }

    /**
     * Active state.
     */
    public function active(): bool
    {
        return $this->active;
    }
}
