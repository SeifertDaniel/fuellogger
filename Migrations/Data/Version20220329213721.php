<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329213721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add station prices index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE `prices`
            ADD KEY `stationprices` (`stationid`,`type`,`datetime`);'
        );
    }
}
