<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use IonGhitun\MysqlEncryption\Models\BaseModel;

/**
 * Class Model
 *
 * Each model should extend this class.
 *
 * @mixin Builder
 * @mixin QueryBuilder
 *
 * @package App\Models
 */
class Model extends BaseModel
{
    /** @var array */
    protected $visible = [];

    /** @var array */
    protected $sortable = [];

    /** @var array */
    protected $searchable = [];

    /** @var array */
    protected $filterable = [];

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

    /**
     * Get filterable columns
     *
     * @return array
     */
    public function getFilterable()
    {
        return $this->filterable;
    }
}
