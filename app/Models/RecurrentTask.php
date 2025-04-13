<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurrentTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'recurrency_type',
        'days_of_week',
        'start_date',
        'end_date',
        'level',
        'user_id',
        'parent_id',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'days_of_week' => 'array'
    ];

    public function getDaysOfWeekAttribute($value)
    {
        return array_map('intval', explode(',', $value));
    }

    public function setDaysOfWeekAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['days_of_week'] = implode(',', $value);
        } else {
            $this->attributes['days_of_week'] = $value;
        }
    }

    public function scopeByUser($query, $user_id)
    {
        return $query->where('recurrent_tasks.user_id', $user_id);
    }

    public function scopeActiveOnDate($query, $date)
    {
        if ($date) {
            $dayOfWeek = $date->dayOfWeek;

            return $query
                ->where(function ($q) use ($date) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $date);
                })
                ->where(function ($q) use ($date) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
                })
                ->where(function ($q) use ($dayOfWeek) {
                    $q->where('days_of_week', 'like', "%{$dayOfWeek}%");
                });
        }
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RecurrentTask::class, 'parent_id', 'id');
    }

    public function subtasks()
    {
        return $this->hasMany(RecurrentTask::class, 'parent_id');
    }

    public function generatedTasks()
    {
        return $this->hasMany(Task::class);
    }
}
