<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Assert\AssertionFailedException;
use Contao\ContentModel;
use Contao\Database\Result;
use Contao\Model;
use Contao\Model\Collection;
use ContaoBootstrap\Core\Helper\ColorRotate;
use ContaoBootstrap\Grid\GridIterator;
use ContaoBootstrap\Grid\GridProvider;
use ContaoBootstrap\Tab\View\Tab\NavigationIterator;
use ContaoBootstrap\Tab\View\Tab\TabRegistry;
use Netzmacht\Contao\Toolkit\Component\ContentElement\AbstractContentElement;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateReference as ToolkitTemplateReference;
use Symfony\Component\Templating\EngineInterface as TemplateEngine;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;

abstract class AbstractTabElement extends AbstractContentElement
{
    /**
     * Color rotate.
     *
     * @var ColorRotate
     */
    private ColorRotate $colorRotate;

    /**
     * Request scope matcher.
     *
     * @var RequestScopeMatcher
     */
    private RequestScopeMatcher $scopeMatcher;

    /**
     * Tab registry.
     *
     * @var TabRegistry
     */
    private TabRegistry $tabRegistry;

    /**
     * Grid provider.
     *
     * @var GridProvider|null
     */
    private ?GridProvider $gridProvider;

    /**
     * The translator.
     *
     * @var Translator
     */
    private Translator $translator;

    /**
     * AbstractContentElement constructor.
     *
     * @param Model|Collection|Result $model          Object model or result.
     * @param TemplateEngine          $templateEngine Template engine.
     * @param Translator              $translator     The translator.
     * @param ColorRotate             $colorRotate    ColorRotate helper.
     * @param RequestScopeMatcher     $scopeMatcher   Request scope matcher.
     * @param TabRegistry             $tabRegistry    Tab registry.
     * @param GridProvider|null       $gridProvider   Grid provider.
     * @param string                  $column         Column or section name.
     */
    public function __construct(
        $model,
        TemplateEngine $templateEngine,
        Translator $translator,
        ColorRotate $colorRotate,
        RequestScopeMatcher $scopeMatcher,
        TabRegistry $tabRegistry,
        ?GridProvider $gridProvider,
        string $column = 'main'
    ) {
        parent::__construct($model, $templateEngine, $column);

        $this->translator   = $translator;
        $this->colorRotate  = $colorRotate;
        $this->scopeMatcher = $scopeMatcher;
        $this->tabRegistry  = $tabRegistry;
        $this->gridProvider = $gridProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        if ($this->isBackendRequest()) {
            $iterator = $this->getIterator();

            if ($iterator && $this->getParent() !== $this->getModel()) {
                $iterator->next();
            }

            return $this->renderBackendView($this->getParent(), $iterator);
        }

        return parent::generate();
    }

    /**
     * Get the grid provider.
     *
     * @return TabRegistry
     */
    protected function getTabRegistry(): TabRegistry
    {
        return $this->tabRegistry;
    }

    /**
     * Render the backend view.
     *
     * @param ContentModel|null  $start    Start element.
     * @param NavigationIterator $iterator Iterator.
     *
     * @return string
     */
    protected function renderBackendView($start, NavigationIterator $iterator = null): string
    {
        $parent = $this->getParent();

        return $this->render(
            new ToolkitTemplateReference(
                'be_bs_tab',
                'html5',
                ToolkitTemplateReference::SCOPE_BACKEND
            ),
            [
                'name'  => $parent ? $parent->bs_tab_name : null,
                'color' => $start ? $this->rotateColor('ce:' . $start->id) : null,
                'error' => $start ? null : $this->translator->trans('ERR.bsTabParentMissing', [], 'contao_default'),
                'title' => $iterator ? $iterator->currentTitle() : null,
            ]
        );
    }

    /**
     * Check if we are in backend mode.
     *
     * @return bool
     */
    protected function isBackendRequest(): bool
    {
        return $this->scopeMatcher->isBackendRequest();
    }

    /**
     * Get the tab navigation iterator.
     *
     * @return NavigationIterator
     */
    protected function getIterator(): ?NavigationIterator
    {
        $parent = $this->getParent();

        if (!$parent) {
            return null;
        }

        try {
            return $this->getTabRegistry()->getIterator((string) $parent->id);
        } catch (AssertionFailedException $e) {
            return null;
        }
    }

    /**
     * Get the parent model.
     *
     * @return ContentModel|null
     */
    protected function getParent(): ?ContentModel
    {
        return ContentModel::findByPk($this->get('bs_tab_parent'));
    }

    /**
     * Get the grid iterator.
     *
     * @return GridIterator|null
     */
    protected function getGridIterator(): ?GridIterator
    {
        $parent = $this->getParent();

        if (!$parent || !$this->gridProvider || !$parent->bs_grid) {
            return null;
        }

        try {
            $gridIterator = $this->gridProvider->getIterator('ce:' . $parent->id, (int) $parent->bs_grid);

            return $gridIterator;
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * Rotate the color for an identifier.
     *
     * @param string $identifier The color identifier.
     *
     * @return string
     */
    protected function rotateColor(string $identifier): string
    {
        return $this->colorRotate->getColor($identifier);
    }
}
