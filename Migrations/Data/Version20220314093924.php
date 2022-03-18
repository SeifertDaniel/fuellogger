<?php

declare(strict_types=1);

namespace Daniels\Benzinlogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220314093924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create price and stations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `prices` (
              `id` char(36) NOT NULL,
              `stationid` char(36) NOT NULL,
              `type` char(10) NOT NULL,
              `price` decimal(4,3) NOT NULL,
              `datetime` datetime NOT NULL,
              `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        $this->addSql('
            ALTER TABLE `prices`
                ADD PRIMARY KEY (`id`);
        ');

        $this->addSql('
            CREATE TABLE `stations` (
              `ID` varchar(36) NOT NULL,
              `TKID` char(36) NOT NULL,
              `NAME` varchar(100) NOT NULL,
              `BRAND` varchar(100) NOT NULL,
              `STREET` varchar(100) CHARACTER SET utf8 NOT NULL,
              `HOUSENUMBER` varchar(10) NOT NULL,
              `POSTCODE` varchar(10) NOT NULL,
              `PLACE` varchar(50) NOT NULL,
              `OPENINGTIMES` text NOT NULL,
              `LAT` decimal(8,6) NOT NULL,
              `LON` decimal(8,6) NOT NULL,
              `STATE` varchar(10) DEFAULT NULL,
              `TIMESTAMP` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $this->addSql('
            ALTER TABLE `stations`
              ADD PRIMARY KEY (`ID`),
              ADD UNIQUE KEY `TKID` (`TKID`);
        ');
    }
}
