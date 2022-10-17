<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

final class TabSeparatorElement extends AbstractTabElement
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $templateName = 'ce_bs_tab_separator';

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplateData(array $data): array
    {
        $iterator = $this->getIterator();
        $parent   = $this->getParent();
        $data     = parent::prepareTemplateData($data);

        $data['fade'] = ($parent && $parent->bs_tab_fade) ? ' fade' : '';

        if ($iterator) {
            $iterator->next();

            if ($iterator->valid()) {
                $currentItem = $iterator->current();

                $data['currentItem'] = $currentItem;

                if ($parent->bs_tab_fade && $currentItem && $currentItem->active()) {
                    $data['fade'] = rtrim($data['fade'] . ' show');
                }
            }
        }

        return $data;
    }
}
