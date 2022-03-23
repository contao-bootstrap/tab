<?php

/**
 * Contao Bootstrap
 *
 * @package    contao-bootstrap
 * @subpackage Tab
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2020 netzmacht David Molineus. All rights reserved.
 * @license    LGPL-3.0-or-later https://github.com/contao-bootstrap/tab/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Component\ContentElement;

use Contao\ContentModel;
use Contao\Database\Result;
use ContaoBootstrap\Core\Helper\ColorRotate;
use ContaoBootstrap\Grid\GridProvider;
use ContaoBootstrap\Tab\View\Tab\TabRegistry;
use Netzmacht\Contao\Toolkit\Component\Component;
use Netzmacht\Contao\Toolkit\Component\ComponentFactory;
use Netzmacht\Contao\Toolkit\Component\Exception\ComponentNotFound;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Symfony\Component\Templating\EngineInterface as TemplateEngine;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;

/**
 * Class TabElementFactory
 *
 * @package ContaoBootstrap\Tab\Component\ContentElement
 */
final class TabElementFactory implements ComponentFactory
{
    /**
     * Panel element types.
     *
     * @var array
     */
    private array $tabElements = [
        'bs_tab_start'     => TabStartElement::class,
        'bs_tab_separator' => TabSeparatorElement::class,
        'bs_tab_end'       => TabEndElement::class,
    ];

    /**
     * Template engine.
     *
     * @var TemplateEngine
     */
    private TemplateEngine $templateEngine;

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
     * Translator.
     *
     * @var Translator
     */
    private Translator $translator;

    /**
     * Grid provider.
     *
     * @var GridProvider|null
     */
    private ?GridProvider $gridProvider;

    /**
     * PanelElementFactory constructor.
     *
     * @param TemplateEngine      $templateEngine The template engine.
     * @param Translator          $translator
     * @param ColorRotate         $colorRotate    Color rotate service.
     * @param RequestScopeMatcher $scopeMatcher   Request scope matcher.
     * @param TabRegistry         $tabRegistry    Tab registry.
     * @param GridProvider|null   $gridProvider
     */
    public function __construct(
        TemplateEngine $templateEngine,
        Translator $translator,
        ColorRotate $colorRotate,
        RequestScopeMatcher $scopeMatcher,
        TabRegistry $tabRegistry,
        ?GridProvider $gridProvider = null
    ) {
        $this->templateEngine = $templateEngine;
        $this->colorRotate    = $colorRotate;
        $this->scopeMatcher   = $scopeMatcher;
        $this->tabRegistry    = $tabRegistry;
        $this->translator     = $translator;
        $this->gridProvider   = $gridProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($model): bool
    {
        if (!$model instanceof ContentModel && !($model instanceof Result)) {
            return false;
        }

        return isset($this->tabElements[$model->type]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ComponentNotFound When invalid type is given.
     */
    public function create($model, string $column): Component
    {
        if (!isset($this->tabElements[$model->type])) {
            throw ComponentNotFound::forModel($model);
        }

        $className = $this->tabElements[$model->type];

        return new $className(
            $model,
            $this->templateEngine,
            $this->translator,
            $this->colorRotate,
            $this->scopeMatcher,
            $this->tabRegistry,
            $this->gridProvider,
            $column
        );
    }
}
