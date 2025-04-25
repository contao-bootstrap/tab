<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Model;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('bs_tab_end', 'bootstrap_tabs', 'ce_bs_tab_end')]
final class TabEndElementController extends AbstractTabElementController
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
            $iterator = $this->getIterator($model);
            $iterator?->rewind();

            return null;
        }

        return $this->renderContentBackendView($this->getParent($model));
    }

    /** {@inheritDoc} */
    #[Override]
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $data         = parent::prepareTemplateData($data, $request, $model);
        $data['grid'] = $this->getGridIterator($model);

        $iterator = $this->getIterator($model);
        if ($iterator) {
            $data['navigation'] = $iterator->navigation();
        }

        $parent = $this->getParent($model);
        if ($parent) {
            $data['showNavigation'] = $parent->bs_tab_nav_position === 'after';
            $data['navClass']       = $parent->bs_tab_nav_class;
        }

        return $data;
    }
}
