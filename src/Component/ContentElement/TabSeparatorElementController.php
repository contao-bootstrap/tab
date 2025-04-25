<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function rtrim;

/** @ContentElement("bs_tab_separator", category="bootstrap_tabs", template="ce_bs_tab_separator") */
final class TabSeparatorElementController extends AbstractTabElementController
{
    /** {@inheritDoc} */
    protected function preGenerate(
        Request $request,
        Model $model,
        string $section,
        array|null $classes = null,
    ): Response|null {
        if (! $this->isBackendRequest($request)) {
            return null;
        }

        $iterator = $this->getIterator($model);
        $iterator?->next();

        return $this->renderContentBackendView($this->getParent($model), $iterator);
    }

    /** {@inheritDoc} */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $data     = parent::prepareTemplateData($data, $request, $model);
        $iterator = $this->getIterator($model);
        $parent   = $this->getParent($model);

        $data['fade'] = $parent && $parent->bs_tab_fade ? ' fade' : '';

        if ($iterator) {
            $iterator->next();

            if ($iterator->valid()) {
                $currentItem = $iterator->current();

                $data['currentItem'] = $currentItem;

                if ($parent && $parent->bs_tab_fade && $currentItem && $currentItem->active()) {
                    $data['fade'] = rtrim($data['fade'] . ' show');
                }
            }
        }

        return $data;
    }
}
