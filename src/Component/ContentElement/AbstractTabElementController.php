<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Assert\AssertionFailedException;
use Contao\ContentModel;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use ContaoBootstrap\Core\Helper\ColorRotate;
use ContaoBootstrap\Grid\GridIterator;
use ContaoBootstrap\Grid\GridProvider;
use ContaoBootstrap\Tab\View\Tab\NavigationIterator;
use ContaoBootstrap\Tab\View\Tab\TabRegistry;
use Netzmacht\Contao\Toolkit\Controller\ContentElement\AbstractContentElementController;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;

abstract class AbstractTabElementController extends AbstractContentElementController
{
    public function __construct(
        TemplateRenderer $templateRenderer,
        RequestScopeMatcher $scopeMatcher,
        ResponseTagger $responseTagger,
        TokenChecker $tokenChecker,
        private readonly Translator $translator,
        private readonly ColorRotate $colorRotate,
        private readonly TabRegistry $tabRegistry,
        private readonly RepositoryManager $repositories,
        private readonly GridProvider|null $gridProvider,
    ) {
        parent::__construct($templateRenderer, $scopeMatcher, $responseTagger, $tokenChecker);
    }

    protected function renderContentBackendView(
        ContentModel|null $start,
        NavigationIterator|null $iterator = null,
    ): Response {
        return $this->renderResponse(
            'fe:be_bs_tab',
            [
                'name'    => $start ? $start->bs_tab_name : null,
                'color'   => $start ? $this->rotateColor('ce:' . $start->id) : null,
                'error'   => ! $start
                    ? $this->translator->trans('ERR.bsTabParentMissing', [], 'contao_default')
                    : null,
                'title' => $iterator ? $iterator->currentTitle() : [],
            ],
        );
    }

    /**
     * Get the tab navigation iterator.
     */
    protected function getIterator(ContentModel $model): NavigationIterator|null
    {
        $parent = $this->getParent($model);

        if (! $parent) {
            return null;
        }

        try {
            return $this->tabRegistry->getIterator((string) $parent->id);
        } catch (AssertionFailedException) {
            return null;
        }
    }

    /**
     * Get the parent model
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    protected function getParent(ContentModel $model): ContentModel|null
    {
        return $this->repositories->getRepository(ContentModel::class)->find((int) $model->bs_tab_parent);
    }

    /**
     * Get the grid iterator.
     */
    protected function getGridIterator(ContentModel $model): GridIterator|null
    {
        if (! $this->gridProvider) {
            return null;
        }

        $parent = $this->getParent($model);

        if (! $parent || ! $parent->bs_grid) {
            return null;
        }

        try {
            return $this->gridProvider->getIterator('ce:' . $parent->id, (int) $parent->bs_grid);
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * Rotate the color for an identifier.
     *
     * @param string $identifier The color identifier.
     */
    protected function rotateColor(string $identifier): string
    {
        return $this->colorRotate->getColor($identifier);
    }
}
