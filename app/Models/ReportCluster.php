<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCluster extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'center_latitude',
        'center_longitude',
        'report_count',
        'priority_score',
        'status',
        'first_reported_at',
        'last_reported_at',
    ];

    protected $casts = [
        'first_reported_at' => 'datetime',
        'last_reported_at' => 'datetime',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'cluster_id');
    }
    public function statusLogs()
    {
        return $this->hasMany(StatusLog::class, 'cluster_id')->latest();
    }
}