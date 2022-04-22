<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Daniels\FuelLogger\Application\Model\Entities\Price;
use Daniels\FuelLogger\Application\Model\Entities\Station;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

final class Version20220314093924 extends AbstractMigration
{
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
        $this->stationsTableName = $em->getClassMetadata( Station::class)->getTableName();
        $this->pricesTableName = $em->getClassMetadata( Price::class)->getTableName();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'create price and stations table';
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        $pricesTable = $schema->createTable($this->pricesTableName)->addOption('engine', 'InnoDb')->addOption( "charset", "utf8mb4" );
        $pricesTable->addColumn('id', Types::STRING)->setLength(36)->setNotnull(true);
        $pricesTable->addColumn('stationid', Types::STRING)->setLength(36)->setNotnull(true);
        $pricesTable->addColumn('type', Types::STRING)->setLength(10)->setNotnull(true);
        $pricesTable->addColumn('price', Types::DECIMAL)->setPrecision(4)->setScale(3)->setNotnull(true);
        $pricesTable->addColumn('datetime', Types::DATETIME_MUTABLE)->setNotnull(true);
        $pricesTable->addColumn('timestamp', Types::DATETIME_IMMUTABLE, ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']);
        $pricesTable->setPrimaryKey(['id']);

        $stationsTable = $schema->createTable($this->stationsTableName)->addOption('engine', 'InnoDb')->addOption( "charset", "utf8mb4" );
        $stationsTable->addColumn('ID', Types::STRING)->setLength(36)->setNotnull(true);
        $stationsTable->addColumn('TKID', Types::STRING)->setLength(36)->setNotnull(true);
        $stationsTable->addColumn('NAME', Types::STRING)->setLength(100)->setNotnull(true);
        $stationsTable->addColumn('BRAND', Types::STRING)->setLength(100)->setNotnull(true);
        $stationsTable->addColumn('STREET', Types::STRING)->setLength(100)->setNotnull(true);
        $stationsTable->addColumn('HOUSENUMBER', Types::STRING)->setLength(10)->setNotnull(true);
        $stationsTable->addColumn('POSTCODE', Types::STRING)->setLength(10)->setNotnull(true);
        $stationsTable->addColumn('PLACE', Types::STRING)->setLength(50)->setNotnull(true);
        $stationsTable->addColumn('OPENINGTIMES', Types::TEXT)->setNotnull(true);
        $stationsTable->addColumn('LAT', Types::DECIMAL)->setPrecision(8)->setScale(6)->setNotnull(true);
        $stationsTable->addColumn('LON', Types::DECIMAL)->setPrecision(8)->setScale(6)->setNotnull(true);
        $stationsTable->addColumn('STATE', Types::STRING)->setLength(10)->setNotnull(true);
        $stationsTable->addColumn('TIMESTAMP', Types::DATETIME_IMMUTABLE, ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']);
        $stationsTable->setPrimaryKey(['ID']);
        $stationsTable->addUniqueIndex(['TKID'], 'TKID');
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function down( Schema $schema ): void
    {
        $schema->dropTable($this->pricesTableName);
        $schema->dropTable($this->stationsTableName);
    }
}
