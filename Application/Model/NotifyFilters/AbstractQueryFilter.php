<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace Daniels\FuelLogger\Application\Model\NotifyFilters;

abstract class AbstractQueryFilter extends AbstractFilter
{
    protected array $filters = [];

    /**
     * @param DatabaseQueryFilter $filter
     * @return $this
     */
    public function addQueryFilter(DatabaseQueryFilter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryFilters(): array
    {
        return $this->filters;
    }
}