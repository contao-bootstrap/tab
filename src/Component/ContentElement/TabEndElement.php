<?php

/**
 * Contao Bootstrap
 *
 * @package    contao-bootstrap
 * @subpackage Tab
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2018 netzmacht David Molineus. All rights reserved.
 * @license    LGPL-3.0 https://github.com/contao-bootstrap/tab
 * @filesource
 */

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

/**
 * Class TabSeparatorElement
 */
class TabEndElement extends AbstractTabElement
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $strTemplate = 'ce_bs_tab_end';

    /**
     * {@inheritdoc}
     */
    protected function compile()
    {
        $parent = $this->getParent();

        $this->Template->showNavigation = ($parent && $parent->bs_tab_nav_position === 'after');
        $this->Template->navigation     = $this->getIterator()->navigation();
    }
}