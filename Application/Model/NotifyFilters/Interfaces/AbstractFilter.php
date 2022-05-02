<?php

declare(strict_types=1);

namespace Daniels\FuelLogger\Application\Model\NotifyFilters\Interfaces;

use Daniels\FuelLogger\Application\Model\Exceptions\filterPreventsNotificationException;
use Daniels\FuelLogger\Application\Model\Notifier\AbstractNotifier;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesItem;
use Daniels\FuelLogger\Application\Model\PriceUpdates\UpdatesList;
use Daniels\FuelLogger\Core\Registry;

abstract class AbstractFilter
{
    protected bool $isInverted = false;

    protected string $debugMessage = 'no debug message set';

    abstract public function filterItem(UpdatesItem $item): bool;

    protected AbstractNotifier $notifier;

    protected FilterCollection $parentFilter;

    /**
     * @return $this
     */
    public function invert(): static
    {
        $this->isInverted = true;

        return $this;
    }

    /**
     * @param string $message
     */
    public function setDebugMessage(string $message)
    {
        $this->debugMessage = $message;
    }

    /**
     * @return string
     */
    public function getDebugMessage(): string
    {
        return $this->debugMessage;
    }

    /**
     * @param UpdatesList $priceUpdates
     *
     * @return UpdatesList
     * @throws filterPreventsNotificationException
     */
    public function filterPriceUpdates(UpdatesList $priceUpdates): UpdatesList
    {
        Registry::getLogger()->debug(__METHOD__.__LINE__);

        if ($this->isItemFilter()) {
            /** @var UpdatesItem $priceUpdate */
            foreach ($priceUpdates->getList() as $id => $priceUpdate) {
                $filtered = $this->filterItem($priceUpdate);
                $filtered = $this->isInverted ? !$filtered : $filtered;
                if ($filtered) {
                    $priceUpdates->remove($id);
                }
Registry::getLogger()->debug(__METHOD__.__LINE__);
Registry::getLogger()->debug($priceUpdates->count());
                if (! (bool) $priceUpdates->count()) {
Registry::getLogger()->debug(__METHOD__.__LINE__);
                    throw new filterPreventsNotificationException($this);
                }
            }
        } else {
            $filtered = $this->filterItem(new UpdatesItem());
            $filtered = $this->isInverted ? !$filtered : $filtered;
            if ($filtered) {
                throw new filterPreventsNotificationException($this);
            }
        }

        return $priceUpdates;
    }

    /**
     * @return AbstractNotifier
     */
    public function getNotifier(): AbstractNotifier
    {
        return $this->notifier;
    }

    /**
     * @param AbstractNotifier $notifier
     */
    public function setNotifier(AbstractNotifier $notifier): void
    {
        $this->notifier = $notifier;
    }

    /**
     * @return bool
     */
    public function isItemFilter(): bool
    {
        return $this instanceof ItemFilter;
    }

    /**
     * @return bool
     */
    public function hasParentFilter(): bool
    {
        return isset($this->parentFilter);
    }

    /**
     * @return FilterCollection
     */
    public function getParentFilter(): FilterCollection
    {
        return $this->parentFilter;
    }

    /**
     * @param FilterCollection $parentFilter
     */
    public function setParentFilter(FilterCollection $parentFilter): void
    {
        $this->parentFilter = $parentFilter;
    }
}