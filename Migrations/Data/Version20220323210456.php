<?php

declare(strict_types=1);

namespace Daniels\Benzinlogger\Migrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220323210456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create oil price table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE `oilprices` ( 
                `ID` CHAR(36) NOT NULL , 
                `PRICE` DECIMAL(7,4) NOT NULL , 
                `DATE` DATE NOT NULL , 
                PRIMARY KEY (`ID`),
                UNIQUE KEY `DATE` (`DATE`)
             ) ENGINE = InnoDB; 
        ');
    }
}
