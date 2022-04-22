<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Migrations\Data;

use Daniels\FuelLogger\Application\Model\Entities\Price;
use Daniels\FuelLogger\Core\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

final class Version20220314220104 extends AbstractMigration
{
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
        $this->pricesTableName = $em->getClassMetadata( Price::class)->getTableName();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'add indices to price table';
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        $priceTable = $schema->getTable($this->pricesTableName);
        $priceTable->addIndex(['datetime'], 'datetime');
        $priceTable->addIndex(['stationid'], 'stationid');
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    public function down( Schema $schema ): void
    {
        $priceTable = $schema->getTable($this->pricesTableName);
        $priceTable->dropIndex('datetime');
        $priceTable->dropIndex('stationid');
    }
}
