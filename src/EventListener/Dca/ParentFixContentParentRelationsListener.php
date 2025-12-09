<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\EventListener\Dca;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\Adapter;
use Contao\DataContainer;
use Contao\Input;
use Contao\Model\Collection;
use Doctrine\DBAL\Connection;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\Definition;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;

use function array_unique;
use function assert;
use function is_int;
use function is_string;
use function time;

/**
 * Class ParentFixContentParentRelationsListener fixes the parent relation of tab element for parent tables
 */
final class ParentFixContentParentRelationsListener
{
    /**
     * @param Connection        $connection        Database connection.
     * @param DcaManager        $dcaManager        Data container manager.
     * @param RepositoryManager $repositoryManager Repository manager.
     * @param Adapter<Input>    $inputAdapter      Input adapter.
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly DcaManager $dcaManager,
        private readonly RepositoryManager $repositoryManager,
        private readonly Adapter $inputAdapter,
    ) {
    }

    /**
     * Handle the oncopy_callback.
     *
     * @param string|int    $insertId      Id of new created record.
     * @param DataContainer $dataContainer Data container.
     */
    public function onCopy(string|int $insertId, DataContainer $dataContainer): void
    {
        $this->fixChildRecords((int) $insertId, $dataContainer->table);
    }

    /**
     * Fix record of a table.
     *
     * It checks each record of the child tables and recreates the parent information.
     *
     * @param int    $recordId  The id of the prent record.
     * @param string $tableName The table name of the parent record.
     */
    private function fixChildRecords(int $recordId, string $tableName): void
    {
        $definition  = $this->dcaManager->getDefinition($tableName);
        $childTables = (array) $definition->get(['config', 'ctable'], []);
        $columns     = $this->repositoryManager
            ->getConnection()
            ->getSchemaManager()
            ->listTableColumns($definition->getName());

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (
            ! $definition->has(['config', 'ptable'])
            && $this->inputAdapter->get('childs')
            && isset($columns['pid'], $columns['sorting'])
        ) {
            $childTables[] = $definition->getName();
        }

        $schemaManager = $this->repositoryManager->getConnection()->getSchemaManager();

        foreach (array_unique($childTables) as $childTable) {
            if (! $schemaManager->tablesExist([$childTable])) {
                continue;
            }

            if ($childTable === 'tl_content') {
                $this->fixParentRelations($definition->getName(), $recordId);
                continue;
            }

            $childRecords = $this->fetchChildRecordIds($recordId, $definition, $childTable);
            foreach ($childRecords as $childRecordId) {
                $this->fixChildRecords((int) $childRecordId, $childTable);
            }
        }
    }

    /**
     * Fix parent relations for content elements of current parent record.
     *
     * @param string $parentTable The parent table.
     * @param int    $parentId    The parent id.
     */
    private function fixParentRelations(string $parentTable, int $parentId): void
    {
        $collection = $this->loadContentModels($parentTable, $parentId);
        if ($collection === null) {
            return;
        }

        $activeParent = null;
        foreach ($collection as $model) {
            if ($model->type === 'bs_tab_start') {
                $activeParent = $model;
                continue;
            }

            // Broken configuration
            if ($activeParent === null) {
                continue;
            }

            $this->repositoryManager->getConnection()->update(
                ContentModel::getTable(),
                [
                    'bs_tab_parent' => $activeParent->id,
                    'tstamp'         => time(),
                ],
                [
                    'id' => $model->id,
                ],
            );
        }
    }

    /**
     * Load tab content elements which have to be adjusted.
     *
     * @param string $parentTable The parent table.
     * @param int    $parentId    The parent id.
     *
     * @return Collection<ContentModel>|null
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function loadContentModels(string $parentTable, int $parentId): Collection|null
    {
        $constraints = ['.pid=?', 'FIND_IN_SET( .type, \'bs_tab_start,bs_tab_separator,bs_tab_end\')'];
        $values      = [$parentId, $parentTable];

        if ($parentTable === 'tl_article') {
            $constraints[] = '( .ptable=? OR .ptable=?)';
            $values[]      = '';
        } else {
            $constraints[] = '.ptable=?';
        }

        return $this->repositoryManager
            ->getRepository(ContentModel::class)
            ->findBy($constraints, $values, ['order' => '.sorting']);
    }

    /**
     * Fetch child record for given definition.
     *
     * @param int        $recordId   The record id.
     * @param Definition $definition The parent definition.
     * @param string     $childTable The child table.
     *
     * @return list<string|int>
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function fetchChildRecordIds(int $recordId, Definition $definition, string $childTable): array
    {
        $childDefinition = $this->dcaManager->getDefinition($childTable);
        $queryBuilder    = $this->connection->createQueryBuilder()
            ->select('id')
            ->from($childTable)
            ->where('pid=:pid')
            ->setParameter('pid', $recordId);

        if ($childDefinition->get(['config', 'dynamicPtable'])) {
            $queryBuilder
                ->andWhere('ptable=:ptable')
                ->setParameter('ptable', $definition->getName());
        }

        $result = $queryBuilder->execute();
        assert(! is_string($result) && ! is_int($result));

        return $result->fetchFirstColumn();
    }
}
