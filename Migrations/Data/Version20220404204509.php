<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220404204509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create price archive table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE `prices_archive` (
              `ID` char(36) NOT NULL,
              `DATE` date NOT NULL,
              `TYPE` char(10) NOT NULL,
              `MIN` decimal(4,3) NOT NULL,
              `AVG` decimal(4,3) NOT NULL,
              `MAX` decimal(4,3) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $this->addSql('
            ALTER TABLE `prices_archive`
              ADD PRIMARY KEY (`ID`),
              ADD UNIQUE KEY `DATETYPE` (`DATE`,`TYPE`);
        ');
    }
}
