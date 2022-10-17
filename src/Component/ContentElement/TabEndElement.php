<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

final class TabEndElement extends AbstractTabElement
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $templateName = 'ce_bs_tab_end';

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplateData(array $data): array
    {
        $data         = parent::prepareTemplateData($data);
        $data['grid'] = $this->getGridIterator();

        if ($iterator = $this->getIterator()) {
            $data['navigation'] = $iterator->navigation();
        }

        if ($parent = $this->getParent()) {
            $data['showNavigation'] = $parent->bs_tab_nav_position === 'after';
            $data['navClass']       = $parent->bs_tab_nav_class;
        }

        return $data;
    }
}
