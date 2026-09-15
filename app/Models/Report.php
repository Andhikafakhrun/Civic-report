<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'original_category',
        'photo_path',
        'description',
        'phone_number',
        'latitude',
        'longitude',
        'severity',
        'status',
        'cluster_id',
    ];

    public function cluster()
    {
        return $this->belongsTo(ReportCluster::class, 'cluster_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (!$report->tracking_code) {
                $report->tracking_code = strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }
    public static function categories(): array
    {
        return [
            'pothole' => ['label' => 'Jalan Berlubang', 'color' => 'var(--bs-warning)'],
            'trash' => ['label' => 'Sampah Menumpuk', 'color' => 'var(--bs-secondary)'],
            'streetlight' => ['label' => 'Lampu Jalan Mati', 'color' => '#6D28D9'],
            'drainage' => ['label' => 'Saluran Air Tersumbat', 'color' => '#2B4C7E'],
            'fallen_tree' => ['label' => 'Pohon Tumbang', 'color' => '#15803D'],
        ];
    }

    public static function categoryLabel(string $slug): string
    {
        return self::categories()[$slug]['label'] ?? ucfirst($slug);
    }

    public static function categoryColor(string $slug): string
    {
        return self::categories()[$slug]['color'] ?? 'var(--bs-secondary)';
    }
}