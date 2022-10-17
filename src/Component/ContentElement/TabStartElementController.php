<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function rtrim;

/** @ContentElement("bs_tab_start", category="bootstrap_tabs") */
final class TabStartElementController extends AbstractTabElementController
{
    /** {@inheritDoc} */
    protected function preGenerate(Request $request, Model $model, string $section, ?array $classes = null): ?Response
    {
        if (! $this->isBackendRequest($request)) {
            return null;
        }

        assert($model instanceof ContentModel);

        return $this->renderContentBackendView($model, $this->getIterator($model));
    }

    /** {@inheritDoc} */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        assert($model instanceof ContentModel);

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

    protected function getParent(ContentModel $model): ?ContentModel
    {
        return $model;
    }
}
