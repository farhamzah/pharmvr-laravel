<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cohort extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const MEMBER_ROLE_STUDENT = 'student';
    public const MEMBER_ROLE_INSTRUCTOR = 'instructor';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role_in_cohort')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->members()->wherePivot('role_in_cohort', self::MEMBER_ROLE_STUDENT);
    }

    public function instructors(): BelongsToMany
    {
        return $this->members()->wherePivot('role_in_cohort', self::MEMBER_ROLE_INSTRUCTOR);
    }
}
