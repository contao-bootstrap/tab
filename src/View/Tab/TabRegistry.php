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

use Assert\Assertion;
use Contao\ContentModel;

/**
 * Class TabRegistry
 *
 * @package ContaoBootstrap\Tab\View\Tab
 */
final class TabRegistry
{
    /**
     * Cached navigation instances.
     *
     * @var array|Navigation[]
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
     *
     * @return Navigation
     *
     * @throws \Assert\AssertionFailedException When an invalid element is given.
     */
    public function getNavigation(string $elementId): Navigation
    {
        if (!isset($this->navigations[$elementId])) {
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
     *
     * @return NavigationIterator
     *
     * @throws \Assert\AssertionFailedException When an invalid element is given.
     */
    public function getIterator(string $elementId): NavigationIterator
    {
        if (!isset($this->iterators[$elementId])) {
            $this->iterators[$elementId] = new NavigationIterator($this->getNavigation($elementId));
        }

        return $this->iterators[$elementId];
    }
}
