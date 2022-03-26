<?php

namespace Daniels\FuelLogger\Application\Model;

use DateTime;
use Doctrine\DBAL\ParameterType;

class openingTimes
{
    const WDAY_MON = 1;  // 1
    const WDAY_TUE = 2;  // 2
    const WDAY_WED = 3;  // 4
    const WDAY_THU = 4;  // 8
    const WDAY_FRI = 5;  // 16
    const WDAY_SAT = 6;  // 32
    const WDAY_SUN = 7;  // 64

    protected $stationId;

    protected $openingTimes = [];

    public function __construct($stationId)
    {
        $this->stationId = $stationId;
    }

    public function getCoreTableName()
    {
        return 'openingtimes';
    }

    public function isOpen($weekday, $checkDate)
    {
        $weekdayInt = 1 << ((int) $weekday) -1;

        $qb = DBConnection::getConnection()->createQueryBuilder();

        $qb->select(1)
            ->from($this->getCoreTableName(), 'ot')
            ->where($qb->expr()->and(
                $qb->expr()->eq(
                    'ot.stationid',
                    $qb->createNamedParameter($this->stationId)
                ),
                'ot.weekday & '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER).' = '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER),
                $qb->createNamedParameter($checkDate).' BETWEEN ot.from AND ot.to'
            ));

        return (bool) $qb->fetchOne();
    }

    public function isClosed($weekday, $checkdate)
    {
        return false === $this->isOpen($weekday, $checkdate);
    }

    public function getOpeningTimes($weekday = null)
    {
        $weekdayInt = 1 << ((int) $weekday) -1;

        ini_set('display_errors', 1);
        $qb = DBConnection::getConnection()->createQueryBuilder();

        $qb->select('ot.from', 'ot.to')
            ->from($this->getCoreTableName(), 'ot')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'ot.stationid',
                        $qb->createNamedParameter($this->stationId)
                    ),
                    $weekday === null ? 1 : 'ot.weekday & '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER).' = '.$qb->createNamedParameter($weekdayInt, ParameterType::INTEGER)
                )
            );

        return $qb->fetchAllAssociative();
    }

    public function isOpenCached($checktime, $weekday = null)
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

    public function isClosedCached($checkTime, $weekday = null)
    {
        return false === $this->isOpenCached($checkTime, $weekday);
    }

    public function getWeekdayByDate($date)
    {
        return (new DateTime($date))->format('N');
    }
}