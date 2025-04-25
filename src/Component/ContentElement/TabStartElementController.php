<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Model;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function rtrim;

#[AsContentElement('bs_tab_start', 'bootstrap_tabs', 'ce_bs_tab_start')]
final class TabStartElementController extends AbstractTabElementController
{
    /** {@inheritDoc} */
    #[Override]
    protected function preGenerate(
        Request $request,
        Model $model,
        string $section,
        array|null $classes = null,
    ): Response|null {
        if (! $this->isBackendRequest($request)) {
            return null;
        }

        return $this->renderContentBackendView($model, $this->getIterator($model));
    }

    /** {@inheritDoc} */
    #[Override]
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $data = parent::prepareTemplateData($data, $request, $model);

        $data['fade']     = $model->bs_tab_fade ? ' fade' : '';
        $data['grid']     = $this->getGridIterator($model);
        $data['navClass'] = $model->bs_tab_nav_class;

        $iterator = $this->getIterator($model);
        if ($iterator) {
            $iterator->rewind();

            $currentItem = $iterator->current();

            $data['navigation']  = $iterator->navigation();
            $data['currentItem'] = $currentItem;

            if ($model->bs_tab_fade && $currentItem && $currentItem->active()) {
                $data['fade'] = rtrim($data['fade'] .= ' show');
            }
        }

        return $data;
    }

    #[Override]
    protected function getParent(ContentModel $model): ContentModel|null
    {
        return $model;
    }
}
