<?php

declare(strict_types=1);

namespace ContaoBootstrap\Tab\Migration;

use ContaoBootstrap\Core\Migration\AbstractGroupWidgetIndexMigration;
use Doctrine\DBAL\Connection;

final class TabsGroupIndexMigration extends AbstractGroupWidgetIndexMigration
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, 'tl_content', 'bs_tabs');
    }
}
