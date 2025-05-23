<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\EventListener\Dca;

use Contao\ContentModel;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Model\Collection;
use Contao\StringUtil;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;
use Netzmacht\Contao\Toolkit\Dca\Listener\AbstractListener;
use Override;
use stdClass;

use function array_unshift;
use function assert;
use function sprintf;
use function time;

final class ContentListener extends AbstractListener
{
    public function __construct(
        DcaManager $dcaManager,
        private readonly RepositoryManager $repositories,
    ) {
        parent::__construct($dcaManager);
    }

    #[Override]
    public static function getName(): string
    {
        return 'tl_content';
    }

    /**
     * Initialize the dca.
     *
     * @Callback(table="tl_content", target="config.onload")
     */
    public function initializeDca(): void
    {
        if (! $this->getDefinition()->has(['fields', 'bs_grid'])) {
            return;
        }

        $this->getDefinition()->modify(
            ['fields', 'bs_grid', 'load_callback'],
            static function (array|null $value): array {
                $value   = (array) $value;
                $value[] = [
                    'contao_bootstrap.tab.listener.dca.content',
                    'configureGridField',
                ];

                return $value;
            },
        );
    }

    /**
     * Get all tab parent options.
     *
     * @return array<int|string,string>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @Callback(table="tl_content", target="fields.bs_tab_parent.options")
     */
    public function getTabParentOptions(DataContainer $dataContainer): array
    {
        $columns = [
            'tl_content.type = ?',
            'tl_content.pid = ?',
            'tl_content.ptable = ?',
        ];

        $values = [
            'bs_tab_start',
            $dataContainer->currentPid,
            $this->getDefinition()->get(['config', 'ptable']),
        ];

        $collection = $this->repositories->getRepository(ContentModel::class)->findBy($columns, $values);
        $options    = [];

        if ($collection instanceof Collection) {
            foreach ($collection as $model) {
                /** @psalm-suppress UndefinedMagicPropertyFetch */
                $options[$model->id] = sprintf(
                    '%s [%s]',
                    $model->bs_tab_name,
                    $model->id,
                );
            }
        }

        return $options;
    }

    /**
     * Configure the grid field.
     *
     * @param mixed              $value         The field value.
     * @param DataContainer|null $dataContainer The data container driver.
     */
    public function configureGridField(mixed $value, DataContainer|null $dataContainer): mixed
    {
        if ($dataContainer && $dataContainer->activeRecord && $dataContainer->activeRecord->type === 'bs_tab_start') {
            $this->getDefinition()->set(['fields', 'bs_grid', 'eval', 'mandatory'], false);
        }

        return $value;
    }

    /**
     * Generate the columns.
     *
     * @param DataContainer $dataContainer Data container driver.
     *
     * @Callback(table="tl_content", target="config.onsubmit")
     */
    public function generateColumns(DataContainer $dataContainer): void
    {
        if (! $dataContainer->activeRecord || $dataContainer->activeRecord->type !== 'bs_tab_start') {
            return;
        }

        $current = $dataContainer->activeRecord;
        assert($current instanceof Result || $current instanceof ContentModel || $current instanceof stdClass);

        $stopElement  = $this->getStopElement($current);
        $count        = $this->countRequiredSeparators($dataContainer->activeRecord->bs_tabs, $current);
        $nextElements = $this->getNextElements($stopElement);

        if ($count <= 0) {
            return;
        }

        $sorting = (int) $stopElement->sorting;
        $sorting = $this->createSeparators($count, $current, $sorting);

        array_unshift($nextElements, $stopElement);
        $this->updateSortings($nextElements, $sorting);
    }

    /**
     * Count the required separator fields which should be created.
     *
     * @param mixed                        $definition The tab definitions.
     * @param ContentModel|Result|stdClass $current    The current content model.
     */
    private function countRequiredSeparators(mixed $definition, ContentModel|Result|stdClass $current): int
    {
        $definition = StringUtil::deserialize($definition, true);
        $count      = -1;

        foreach ($definition as $item) {
            if ($item['type'] === 'dropdown') {
                continue;
            }

            $count++;
        }

        return $count - $this->repositories->getRepository(ContentModel::class)->countBy(
            [
                'tl_content.ptable=?',
                'tl_content.pid=?',
                '(tl_content.type = ? AND tl_content.bs_tab_parent = ?)',
            ],
            [$current->ptable, $current->pid, 'bs_tab_separator', $current->id],
        );
    }

    /**
     * Create separators.
     *
     * @param int                          $value   Number of separators being created.
     * @param ContentModel|Result|stdClass $current Current model.
     * @param int                          $sorting Current sorting value.
     */
    protected function createSeparators(int $value, ContentModel|Result|stdClass $current, int $sorting): int
    {
        for ($count = 1; $count <= $value; $count++) {
            $sorting += 8;
            $this->createTabElement($current, 'bs_tab_separator', $sorting);
        }

        return $sorting;
    }

    /**
     * Update the sorting of given elements.
     *
     * @param ContentModel[] $elements    Model collection.
     * @param int            $lastSorting Last sorting value.
     */
    protected function updateSortings(array $elements, int $lastSorting): int
    {
        foreach ($elements as $element) {
            if ($lastSorting > $element->sorting) {
                $element->sorting = $lastSorting + 8;
                $element->save();
            }

            $lastSorting = (int) $element->sorting;
        }

        return $lastSorting;
    }

    /**
     * Create the stop element.
     *
     * @param ContentModel|Result|stdClass $current Model.
     * @param int                          $sorting Last sorting value.
     */
    protected function createStopElement(ContentModel|Result|stdClass $current, int $sorting): ContentModel
    {
        $sorting += 8;

        return $this->createTabElement($current, 'bs_tab_end', $sorting);
    }

    /**
     * Create a tab element.
     *
     * @param ContentModel|Result|stdClass $current Current content model.
     * @param string                       $type    Type of the content model.
     * @param int                          $sorting The sorting value.
     */
    protected function createTabElement(
        ContentModel|Result|stdClass $current,
        string $type,
        int &$sorting,
    ): ContentModel {
        $model                = new ContentModel();
        $model->tstamp        = time();
        $model->pid           = $current->pid;
        $model->ptable        = $current->ptable;
        $model->sorting       = $sorting;
        $model->type          = $type;
        $model->bs_tab_parent = $current->id;
        $model->save();

        return $model;
    }

    /**
     * Get the next content elements.
     *
     * @param ContentModel|Result|stdClass $current Current content model.
     *
     * @return ContentModel[]
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    protected function getNextElements(ContentModel|Result|stdClass $current): array
    {
        $collection = $this->repositories->getRepository(ContentModel::class)->findBy(
            [
                'tl_content.ptable=?',
                'tl_content.pid=?',
                'tl_content.sorting > ?',
            ],
            [$current->ptable, $current->pid, $current->sorting],
            ['order' => 'tl_content.sorting ASC'],
        );

        if ($collection) {
            return $collection->getIterator()->getArrayCopy();
        }

        return [];
    }

    /**
     * Get related stop element.
     *
     * @param ContentModel|Result|stdClass $current Current element.
     */
    protected function getStopElement(ContentModel|Result|stdClass $current): ContentModel
    {
        $stopElement = $this->repositories->getRepository(ContentModel::class)->findOneBy(
            ['tl_content.type=?', 'tl_content.bs_tab_parent=?'],
            ['bs_tab_end', $current->id],
        );

        if ($stopElement instanceof ContentModel) {
            return $stopElement;
        }

        $nextElements = $this->getNextElements($current);
        $stopElement  = $this->createStopElement($current, (int) $current->sorting);
        $this->updateSortings($nextElements, (int) $stopElement->sorting);

        return $stopElement;
    }
}
