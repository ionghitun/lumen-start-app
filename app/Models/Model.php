<?php

namespace App\Models;

use IonGhitun\MysqlEncryption\Models\BaseModel;

/**
 * Class Model
 *
 * Each model should extend this class.
 *
 * @package App\Models
 */
class Model extends BaseModel
{
    /** @var array */
    protected $sortable = [];

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function getSortable()
    {
        return $this->sortable;
    }
}
