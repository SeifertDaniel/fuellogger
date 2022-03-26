<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220317211214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add opening times table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE `openingtimes` (
            `ID` char(36) NOT NULL,
            `STATIONID` char(36) NOT NULL,
            `WEEKDAY` int(2) NOT NULL,
            `FROM` time NOT NULL,
            `TO` time NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $this->addSql('
            ALTER TABLE `openingtimes`
            ADD PRIMARY KEY (`ID`),
            ADD KEY `STATIONID` (`STATIONID`),
            ADD KEY `WEEKDAY` (`WEEKDAY`,`STATIONID`) USING BTREE;
        ');
    }
}
