<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\View\Tab;

use Assert\Assertion;
use Contao\ContentModel;

final class TabRegistry
{
    /**
     * Cached navigation instances.
     *
     * @var Navigation[]
     */
    private array $navigations = [];

    /**
     * Cached navigation iterators.
     *
     * @var NavigationIterator[]
     */
    private array $iterators = [];

    /**
     * Get a navigation.
     *
     * @param string $elementId The element id.
     */
    public function getNavigation(string $elementId): Navigation
    {
        if (! isset($this->navigations[$elementId])) {
            $element = ContentModel::findByPk($elementId);
            Assertion::isInstanceOf($element, ContentModel::class);
            Assertion::eq($element->type, 'bs_tab_start');

            $this->navigations[$elementId] = Navigation::fromSerialized((string) $element->bs_tabs, $elementId);
        }

        return $this->navigations[$elementId];
    }

    /**
     * Get the iterator.
     *
     * @param string $elementId The element id.
     */
    public function getIterator(string $elementId): NavigationIterator
    {
        if (! isset($this->iterators[$elementId])) {
            $this->iterators[$elementId] = new NavigationIterator($this->getNavigation($elementId));
        }

        return $this->iterators[$elementId];
    }
}
