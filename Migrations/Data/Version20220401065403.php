<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Daniels\FuelLogger\Application\Model\Entities\openingTimes;
use Daniels\FuelLogger\Application\Model\Entities\Price;
use Daniels\FuelLogger\Application\Model\Entities\Station;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

final class Version20220401065403 extends AbstractMigration
{
    private string $openingTimesTableName;
    private string $stationsTableName;
    private string $pricesTableName;

    /**
     * @param Connection      $connection
     * @param LoggerInterface $logger
     *
     * @throws ORMException
     */
    public function __construct( Connection $connection, LoggerInterface $logger )
    {
        parent::__construct( $connection, $logger );

        $em                      = Registry::getEntityManager();
        $this->openingTimesTableName = $em->getClassMetadata( openingTimes::class)->getTableName();
        $this->stationsTableName = $em->getClassMetadata( Station::class)->getTableName();
        $this->pricesTableName = $em->getClassMetadata( Price::class)->getTableName();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'set foreign keys';
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        $otTable = $schema->getTable($this->openingTimesTableName);
        $otTable->addForeignKeyConstraint($this->stationsTableName, ['STATIONID'], ['ID'], [], 'openingtimes_ibfk_1');

        $pricesTable = $schema->getTable($this->pricesTableName);
        $pricesTable->addForeignKeyConstraint($this->stationsTableName, ['stationid'], ['ID'], [], 'prices_ibfk_1');
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function down( Schema $schema ): void
    {
        $otTable = $schema->getTable($this->openingTimesTableName);
        $otTable->removeForeignKey('openingtimes_ibfk_1');

        $pricesTable = $schema->getTable($this->pricesTableName);
        $pricesTable->removeForeignKey('prices_ibfk_1');
    }
}
