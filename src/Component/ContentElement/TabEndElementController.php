<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;

/** @ContentElement("bs_tab_end", category="bootstrap_tabs") */
final class TabEndElementController extends AbstractTabElementController
{
    /** {@inheritDoc} */
    protected function preGenerate(Request $request, Model $model, string $section, ?array $classes = null): ?Response
    {
        assert($model instanceof ContentModel);

        if (! $this->isBackendRequest($request)) {
            $iterator = $this->getIterator($model);
            if ($iterator) {
                $iterator->rewind();
            }

            return null;
        }

        return $this->renderContentBackendView($this->getParent($model));
    }

    /** {@inheritDoc} */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        assert($model instanceof ContentModel);

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
