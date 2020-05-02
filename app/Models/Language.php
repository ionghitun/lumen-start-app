<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use IonGhitun\MysqlEncryption\Models\BaseModel;

/**
 * Class Language
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 *
 * @method static Builder|Language newModelQuery()
 * @method static Builder|Language newQuery()
 * @method static Builder|Language query()
 * @method static Builder|Language whereCode($value)
 * @method static Builder|Language whereCreatedAt($value)
 * @method static Builder|Language whereDeletedAt($value)
 * @method static Builder|Language whereId($value)
 * @method static Builder|Language whereName($value)
 * @method static Builder|Language whereUpdatedAt($value)
 * @method static QueryBuilder|Language onlyTrashed()
 * @method static QueryBuilder|Language withTrashed()
 * @method static QueryBuilder|Language withoutTrashed()
 * @method static Builder|BaseModel orWhereEncrypted($column, $value)
 * @method static Builder|BaseModel orWhereNotEncrypted($column, $value)
 * @method static Builder|BaseModel orderByEncrypted($column, $direction)
 * @method static Builder|BaseModel whereEncrypted($column, $value)
 * @method static Builder|BaseModel whereNotEncrypted($column, $value)
 *
 * @package App\Models
 */
class Language extends Model
{
    use SoftDeletes;

    /** @var int */
    const ID_EN = 1;

    /** @var int */
    const ID_RO = 2;

    /** @var string */
    const CODE_EN = 'en';

    /** @var string */
    const CODE_RO = 'ro';

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
        'code'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'name',
        'code'
    ];

    /** @var array */
    protected $searchable = [
        'name',
        'code'
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
