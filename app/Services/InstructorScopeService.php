<?php

namespace App\Services;

use App\Models\Cohort;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InstructorScopeService
{
    public function canSeeGlobal(User $actor): bool
    {
        return in_array($actor->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
    }

    public function assignedCohortIds(User $actor): Collection
    {
        if ($this->canSeeGlobal($actor)) {
            return Cohort::query()->pluck('id');
        }

        if (! $actor->isInstructor()) {
            return collect();
        }

        return $actor->cohorts()
            ->where('cohort_user.role_in_cohort', Cohort::MEMBER_ROLE_INSTRUCTOR)
            ->pluck('cohorts.id');
    }

    public function assignedStudentIds(User $actor): Collection
    {
        if ($this->canSeeGlobal($actor)) {
            return User::query()
                ->where('role', User::ROLE_STUDENT)
                ->pluck('id');
        }

        $cohortIds = $this->assignedCohortIds($actor);

        if ($cohortIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->where('role', User::ROLE_STUDENT)
            ->whereHas('cohorts', function (Builder $query) use ($cohortIds) {
                $query->whereIn('cohorts.id', $cohortIds)
                    ->where('cohort_user.role_in_cohort', Cohort::MEMBER_ROLE_STUDENT);
            })
            ->pluck('id');
    }

    public function scopeUsersForActor(Builder $query, User $actor): Builder
    {
        if ($this->canSeeGlobal($actor)) {
            return $query;
        }

        if (! $actor->isInstructor()) {
            return $query->whereRaw('1 = 0');
        }

        $studentIds = $this->assignedStudentIds($actor);

        return $studentIds->isEmpty()
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('id', $studentIds);
    }

    public function scopeStudentDataForActor(Builder $query, User $actor, string $userColumn = 'user_id'): Builder
    {
        if ($this->canSeeGlobal($actor)) {
            return $query;
        }

        if (! $actor->isInstructor()) {
            return $query->whereRaw('1 = 0');
        }

        $studentIds = $this->assignedStudentIds($actor);

        return $studentIds->isEmpty()
            ? $query->whereRaw('1 = 0')
            : $query->whereIn($userColumn, $studentIds);
    }

    public function canAccessStudent(User $actor, User|int $student): bool
    {
        if ($this->canSeeGlobal($actor)) {
            return true;
        }

        if (! $actor->isInstructor()) {
            return false;
        }

        $studentId = $student instanceof User ? $student->id : $student;

        return $this->assignedStudentIds($actor)->contains((int) $studentId);
    }

    public function canAccessCohort(User $actor, Cohort $cohort): bool
    {
        if ($this->canSeeGlobal($actor)) {
            return true;
        }

        if (! $actor->isInstructor()) {
            return false;
        }

        return $actor->cohorts()
            ->whereKey($cohort->id)
            ->where('cohort_user.role_in_cohort', Cohort::MEMBER_ROLE_INSTRUCTOR)
            ->exists();
    }
}
