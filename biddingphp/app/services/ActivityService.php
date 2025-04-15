<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityService
{
    /**
     * Log an activity.
     *
     * @param string $description
     * @param \Illuminate\Database\Eloquent\Model|null $subject
     * @param array $properties
     * @return \App\Models\Activity
     */
    public function log($description, $subject = null, $properties = [])
    {
        $activity = new Activity();
        $activity->log_name = 'default';
        $activity->description = $description;

        if ($subject) {
            $activity->subject_type = get_class($subject);
            $activity->subject_id = $subject->getKey();
        }

        if (Auth::check()) {
            $activity->causer_type = get_class(Auth::user());
            $activity->causer_id = Auth::id();
        }

        $activity->properties = collect($properties);
        $activity->save();

        return $activity;
    }

    /**
     * Get recent activities.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent($limit = 10)
    {
        return Activity::with(['causer', 'subject'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Get activities for a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserActivities($userId, $limit = 20)
    {
        return Activity::where('causer_type', User::class)
                     ->where('causer_id', $userId)
                     ->with('subject')
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }
}
