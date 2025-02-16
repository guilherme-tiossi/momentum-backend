<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'finished',
        'level',
        'user_id',
        'parent_id',
        'includes_weekend'
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function scopeIncludeWeekend($query, $includes, $date)
    {
        $query = $query->where('tasks.includes_weekend', $includes);

        if ($date) {
            $query = $query->orWhere('tasks.date', $date);
        }

        return $query;
    }

    public function scopeByFinished($query, $finished)
    {
        if ($finished) {
            return $query->where('tasks.finished', $finished);
        }
    }

    public function scopeByLevel($query, $level)
    {
        if (!is_null($level)) {
            return $query->where('tasks.level', $level);
        }
    }

    public function scopeByDate($query, $date)
    {
        if ($date) {
            return $query->where('tasks.date', $date)->orWhereNull('tasks.date');
        }
    }

    public function scopeByUser($query, $user_id)
    {
        return $query->where('tasks.user_id', $user_id);
    }

    public function scopeByParent($query, $parent_id)
    {
        return $query->where('tasks.parent_id', $parent_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id', 'id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }
}
