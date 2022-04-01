<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220401065403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set foreign keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE `openingtimes`
             ADD CONSTRAINT `openingtimes_ibfk_1` FOREIGN KEY (`STATIONID`) REFERENCES `stations` (`ID`);'
        );

        $this->addSql(
            'ALTER TABLE `prices`
             ADD CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stationid`) REFERENCES `stations` (`ID`);'
        );
    }
}
