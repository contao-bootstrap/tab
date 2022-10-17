<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\ContentModel;

final class TabStartElement extends AbstractTabElement
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $templateName = 'ce_bs_tab_start';

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplateData(array $data): array
    {
        $data = parent::prepareTemplateData($data);

        $data['fade']     = $this->get('bs_tab_fade') ? ' fade' : '';
        $data['grid']     = $this->getGridIterator();
        $data['navClass'] = $this->get('bs_tab_nav_class');

        $iterator = $this->getIterator();
        if ($iterator) {
            $iterator->rewind();

            $currentItem = $iterator->current();

            $data['navigation']  = $iterator->navigation();
            $data['currentItem'] = $currentItem;

            if ($this->get('bs_tab_fade') && $currentItem && $currentItem->active()) {
                $data['fade'] = rtrim($data['fade'] .= ' show');
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        if ($this->isBackendRequest()) {
            $iterator = $this->getIterator();

            if ($iterator) {
                $iterator->rewind();
            }
        }

        return parent::generate();
    }

    /**
     * {@inheritdoc}
     */
    protected function getParent(): ?ContentModel
    {
        return $this->getModel();
    }
}
