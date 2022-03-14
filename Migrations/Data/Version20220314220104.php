<?php

declare(strict_types=1);

namespace Daniels\Benzinlogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220314220104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add indices to price table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `prices` ADD INDEX (`datetime`)');

        $this->addSql('ALTER TABLE `prices` ADD INDEX (`stationid`)');
    }
}
