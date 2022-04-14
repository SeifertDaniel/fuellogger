<?php

namespace Daniels\FuelLogger\Application\Model\Entities;

use Daniels\FuelLogger\Application\Model\DBConnection;
use Daniels\FuelLogger\Core\Registry;
use DateTime;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Exception;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table('openingtimes')]
class openingTimes
{
    const WDAY_MON = 1;  // 1
    const WDAY_TUE = 2;  // 2
    const WDAY_WED = 3;  // 4
    const WDAY_THU = 4;  // 8
    const WDAY_FRI = 5;  // 16
    const WDAY_SAT = 6;  // 32
    const WDAY_SUN = 7;  // 64
    const WDAY_FEA = 8;  // 128

    #[Id]
    #[Column(type: 'uuid', unique: true), GeneratedValue(strategy: 'CUSTOM'), CustomIdGenerator(class: UuidGenerator::class)]
    private string $id;

    #[ManyToOne(targetEntity: Station::class)]
    #[Column(type: 'uuid')]
    private string $stationId;

    #[Column]
    private int $weekday;

    #[Column(type: 'time')]
    private string $from;

    #[Column(type: 'time')]
    private string $to;

    protected string $loadedStationId;

    protected array $openingTimes = [];

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStationId(): string
    {
        return $this->stationId;
    }

    /**
     * @param string $stationId
     * @return openingTimes
     */
    public function setStationId(string $stationId): openingTimes
    {
        $this->stationId = $stationId;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeekday(): int
    {
        return $this->weekday;
    }

    /**
     * @param int $weekday
     * @return openingTimes
     */
    public function setWeekday(int $weekday): openingTimes
    {
        $this->weekday = $weekday;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return openingTimes
     */
    public function setFrom(string $from): openingTimes
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return openingTimes
     */
    public function setTo(string $to): openingTimes
    {
        $this->to = $to;
        return $this;
    }

    public function load($stationId)
    {
        $this->loadedStationId = $stationId;
    }

    /**
     * @return int[]
     */
    public function getWeekdayList(): array
    {
        return [
            self::WDAY_MON  => 'Montag',
            self::WDAY_TUE  => 'Dienstag',
            self::WDAY_WED  => 'Mittwoch',
            self::WDAY_THU  => 'Donnerstag',
            self::WDAY_FRI  => 'Freitag',
            self::WDAY_SAT  => 'Samstag',
            self::WDAY_SUN  => 'Sonntag',
            self::WDAY_FEA  => 'Feiertag'
        ];
    }

    /**
     * @param $weekday
     * @param $checkDate
     *
     * @return bool
     * @throws DoctrineException
     */
    public function isOpen($weekday, $checkDate): bool
    {
        $weekdayInt = 1 << ((int) $weekday) -1;

        $qb = DBConnection::getConnection()->createQueryBuilder();

        $em = Registry::getEntityManager();
        $openingTimesTable = $em->getClassMetadata( openingTimes::class)->getTableName();

        $qb->select(1)
            ->from($openingTimesTable, 'ot')
            ->where($qb->expr()->and(
                $qb->expr()->eq(
                    'ot.stationid',
                    $qb->createNamedParameter($this->loadedStationId)
                ),
                'ot.weekday & '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER).' = '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER),
                $qb->createNamedParameter($checkDate).' BETWEEN ot.from AND ot.to'
            ));

        return (bool) $qb->fetchOne();
    }

    /**
     * @param $weekday
     * @param $checkdate
     *
     * @return bool
     * @throws DoctrineException
     */
    public function isClosed($weekday, $checkdate): bool
    {
        return false === $this->isOpen($weekday, $checkdate);
    }

    /**
     * @param int $weekday
     *
     * @return array
     * @throws DoctrineException
     */
    public function getOpeningTimes(int $weekday): array
    {
        $weekdayInt = 1 << $weekday - 1;

        $qb = DBConnection::getConnection()->createQueryBuilder();

        $em = Registry::getEntityManager();
        $openingTimesTable = $em->getClassMetadata( openingTimes::class)->getTableName();

        $qb->select('ot.from', 'ot.to')
            ->from($openingTimesTable, 'ot')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'ot.stationid',
                        $qb->createNamedParameter($this->loadedStationId)
                    ),
                    'ot.weekday & '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER).' = '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER)
                )
            );

        return $qb->fetchAllAssociative();
    }

    /**
     * @param int $weekday
     *
     * @return array
     * @throws DoctrineException
     */
    public function getOpeningTimesList(): array
    {
        $qb = DBConnection::getConnection()->createQueryBuilder();

        $em = Registry::getEntityManager();
        $openingTimesTable = $em->getClassMetadata( openingTimes::class)->getTableName();

        $qb->select('ot.*')
            ->from($openingTimesTable, 'ot')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'ot.stationid',
                        $qb->createNamedParameter($this->loadedStationId)
                    )
                )
            );

        $times = array_fill_keys($this->getWeekdayList(), []);
        foreach ($qb->fetchAllAssociative() as $item) {
            $item = array_change_key_case($item, CASE_LOWER);
            foreach ($this->getWeekdayList() as $wd => $wdName) {
                $wdInt = (1 << $wd - 1);
                if (($item['weekday'] & $wdInt) == $wdInt) {
                    $times[$wdName][DateTime::createFromFormat('H:i:s', $item['from'])->format('U')] = [
                        'from' => DateTime::createFromFormat('H:i:s', $item['from'])->format('H:i'),
                        'to'   => DateTime::createFromFormat('H:i:s', $item['to'])->format('H:i')
                    ];
                }
            }
        }

        return $times;
    }

    /**
     * @param      $checktime
     * @param null $weekday
     *
     * @return bool
     * @throws Exception
     */
    public function isOpenCached($checktime, $weekday = null): bool
    {
        $checktime = (new DateTime($checktime))->format('H:i:s');

        if (false === isset($this->openingTimes[$weekday])) {
            $this->openingTimes[$weekday] = $this->getOpeningTimes($weekday);
        }

        if (isset($this->openingTimes[$weekday]) && count($this->openingTimes[$weekday])) {
            foreach ($this->openingTimes[$weekday] as $ot) {
                if ($ot['from'] <= $checktime && $checktime <= $ot['to']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param      $checkTime
     * @param null $weekday
     *
     * @return bool
     * @throws Exception
     */
    public function isClosedCached($checkTime, $weekday = null): bool
    {
        return false === $this->isOpenCached($checkTime, $weekday);
    }

    /**
     * @param $date
     *
     * @return string
     * @throws Exception
     */
    public function getWeekdayByDate($date): string
    {
        return (new DateTime($date))->format('N');
    }
}