<?php

namespace Netflex\Commerce\Contracts;

interface Lockable
{
    /**
     *
     * Determines if the object is locked and should not be modified
     *
     * @return bool
     */
    public function isLocked(): bool;

    /**
     *
     * Sets if the object is locked or not
     *
     * @param bool $isLocked
     * @return mixed
     */
    public function setLocked(bool $isLocked);

}