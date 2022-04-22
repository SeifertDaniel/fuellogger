<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Daniels\FuelLogger\Application\Model\Entities\openingTimes;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

final class Version20220317211214 extends AbstractMigration
{
    private string $openingTimesTableName;

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
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'add opening times table';
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        if (false === $schema->hasTable($this->openingTimesTableName)) {
            $otTable = $schema->createTable( $this->openingTimesTableName )->addOption( 'engine', 'InnoDb' )->addOption( "charset", "utf8mb4" );
            $otTable->addColumn( 'ID', Types::STRING )->setLength( 36 )->setNotnull( true );
            $otTable->addColumn( 'STATIONID', Types::STRING )->setLength( 36 )->setNotnull( true );
            $otTable->addColumn( 'WEEKDAY', Types::INTEGER )->setLength( 2 )->setNotnull( true );
            $otTable->addColumn( 'FROM', Types::TIME_MUTABLE )->setNotnull( true );
            $otTable->addColumn( 'TO', Types::TIME_MUTABLE )->setNotnull( true );

            $otTable->setPrimaryKey( [ 'ID' ] );
            $otTable->addIndex( [ 'STATIONID' ], 'STATIONID' );
            $otTable->addIndex( [ 'WEEKDAY', 'STATIONID' ], 'WEEKDAY' );
        }
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function down( Schema $schema ): void
    {
        if ($schema->hasTable($this->openingTimesTableName)) {
            $schema->dropTable($this->openingTimesTableName);
        }
    }
}
