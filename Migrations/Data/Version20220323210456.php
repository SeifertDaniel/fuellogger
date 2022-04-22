<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Daniels\FuelLogger\Application\Model\Entities\OilPrice;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

final class Version20220323210456 extends AbstractMigration
{
    private string $oilPricesTableName;

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
        $this->oilPricesTableName = $em->getClassMetadata( OilPrice::class)->getTableName();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'create oil price table';
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        if (false === $schema->hasTable($this->oilPricesTableName)) {
            $oilPriceTable = $schema->createTable( $this->oilPricesTableName )->addOption( 'engine', 'InnoDb' );
            $oilPriceTable->addColumn( 'ID', Types::STRING )->setLength( 36 )->setNotnull( true );
            $oilPriceTable->addColumn( 'PRICE', Types::DECIMAL )->setPrecision( 7 )->setScale( 4 )->setNotnull( true );
            $oilPriceTable->addColumn( 'DATE', Types::DATE_MUTABLE )->setNotnull( true );
            $oilPriceTable->setPrimaryKey( [ 'ID' ] );
            $oilPriceTable->addUniqueIndex( [ 'DATE' ], 'DATE' );
        }
    }

    public function down( Schema $schema ): void
    {
        if ($schema->hasTable($this->oilPricesTableName)) {
            $schema->dropTable( $this->oilPricesTableName );
        }
    }
}
