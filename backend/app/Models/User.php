<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use App\Models\UserProfile;
use App\Models\UserPreference;
use App\Models\UserTrainingProgress;
use App\Models\TrainingModule;
use App\Traits\HasRolesAndPermissions;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string $status
 * @property bool $can_bypass_prerequisites
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserAchievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PharmaiConversation> $pharmaiConversations
 * @property-read int|null $pharmai_conversations_count
 * @property-read UserPreference|null $preferences
 * @property-read UserProfile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserTrainingProgress> $trainingProgress
 * @property-read int|null $training_progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrAiInteraction> $vrAiInteractions
 * @property-read int|null $vr_ai_interactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSession> $vrSessions
 * @property-read int|null $vr_sessions_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCanBypassPrerequisites($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    // Core Roles
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_INSTRUCTOR = 'instructor';
    const ROLE_STUDENT = 'student';

    // Account Statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'can_bypass_prerequisites',
    ];

    // Helper Methods
    public function isSuperAdmin() { return $this->role === self::ROLE_SUPER_ADMIN; }
    public function isAdmin() { return $this->role === self::ROLE_ADMIN || $this->role === self::ROLE_SUPER_ADMIN; }
    public function isInstructor() { return $this->role === self::ROLE_INSTRUCTOR; }
    public function isStudent() { return $this->role === self::ROLE_STUDENT; }
    public function isActive() { return $this->status === self::STATUS_ACTIVE; }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's preferences.
     */
    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get the training progress records for the user.
     */
    public function trainingProgress()
    {
        return $this->hasMany(UserTrainingProgress::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the PharmAI conversations for the user.
     */
    public function pharmaiConversations()
    {
        return $this->hasMany(PharmaiConversation::class);
    }

    /**
     * Get the VR AI interactions for the user.
     */
    public function vrAiInteractions()
    {
        return $this->hasMany(VrAiInteraction::class);
    }

    public function vrSessions()
    {
        return $this->hasMany(VrSession::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_bypass_prerequisites' => 'boolean',
        ];
    }

    /**
     * Get the achievements for the user.
     */
    public function achievements()
    {
        return $this->hasMany(UserAchievement::class);
    }
}
