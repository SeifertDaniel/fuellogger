<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Daniels\FuelLogger\Application\Model\Entities\PriceArchive;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

final class Version20220404204509 extends AbstractMigration
{
    private string $priceArchiveTableName;

    /**
     * @param Connection      $connection
     * @param LoggerInterface $logger
     *
     * @throws ORMException
     */
    public function __construct( Connection $connection, LoggerInterface $logger )
    {
        parent::__construct( $connection, $logger );

        $em = Registry::getEntityManager();
        $this->priceArchiveTableName = $em->getClassMetadata( PriceArchive::class)->getTableName();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'create price archive table';
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        if (false === $schema->hasTable($this->priceArchiveTableName)) {
            $pricesArchive = $schema->createTable( $this->priceArchiveTableName )->addOption( "engine", "InnoDB" )->addOption( "charset", "utf8mb4" );
            $pricesArchive->addColumn( 'ID', Types::STRING )->setLength( 36 )->setNotnull( true );
            $pricesArchive->addColumn( 'DATE', Types::DATE_MUTABLE )->setNotnull( true );
            $pricesArchive->addColumn( 'TYPE', Types::STRING )->setLength( 10 )->setNotnull( true );
            $pricesArchive->addColumn( 'MIN', Types::DECIMAL )->setPrecision( 4 )->setScale( 3 )->setNotnull( true );
            $pricesArchive->addColumn( 'AVG', Types::DECIMAL )->setPrecision( 4 )->setScale( 3 )->setNotnull( true );
            $pricesArchive->addColumn( 'MAX', Types::DECIMAL )->setPrecision( 4 )->setScale( 3 )->setNotnull( true );

            $pricesArchive->setPrimaryKey( [ 'ID' ] );
            $pricesArchive->addUniqueIndex( [ 'DATE', 'TYPE' ], 'DATETYPE' );
        }
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function down( Schema $schema ): void
    {
        if ($schema->hasTable($this->priceArchiveTableName)) {
            $schema->dropTable( $this->priceArchiveTableName );
        }
    }
}
