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

    /** @var array */
    protected $searchable = [];

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Get searchable columns
     *
     * @return array
     */
    public function getSearchable()
    {
        return $this->searchable;
    }
}
