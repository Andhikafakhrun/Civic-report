<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cluster_id',
        'user_id',
        'previous_status',
        'new_status',
        'note',
        'photo_path',
    ];

    public function cluster()
    {
        return $this->belongsTo(ReportCluster::class, 'cluster_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function statusLabel(string $status): string
    {
        return match ($status) {
            'reported' => 'Dilaporkan',
            'in_progress' => 'Sedang Ditangani',
            'resolved' => 'Selesai',
            default => ucfirst($status),
        };
    }
}