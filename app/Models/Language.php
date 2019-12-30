<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Language
 *
 * @package App\Models
 */
class Language extends Model
{
    use SoftDeletes;

    public $id;
    public $name;
    public $code;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'languages';

    /** @var array */
    protected $fillable = [
        'name',
        'code'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'name',
        'code',
        'created_at',
        'updated_at'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'name',
        'code',
        'created_at',
        'updated_at'
    ];

    /**
     * language users.
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'language_id', 'id');
    }
}
