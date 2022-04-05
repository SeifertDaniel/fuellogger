<?php

namespace Daniels\FuelLogger\Application\Model;

class BaseModel
{
    public string $id;

    public function assign($record)
    {
        if (!is_array($record)) {
            return;
        }

        foreach ($record as $name => $value) {
            $this->setFieldData($name, $value);
        }
    }

    /**
     * Sets data field value
     *
     * @param string $fieldName  Index OR name (eg. 'oxarticles__oxtitle') of a data field to set
     * @param string $fieldValue Value of data field
     */
    protected function setFieldData(string $fieldName, string $fieldValue)
    {
        $this->$fieldName = $fieldValue;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}