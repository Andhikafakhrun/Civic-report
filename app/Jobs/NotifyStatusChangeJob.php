<?php

namespace App\Jobs;

use App\Models\ReportCluster;
use App\Services\FonnteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyStatusChangeJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public ReportCluster $cluster,
        public string $newStatus
    ) {
    }

    public function handle(FonnteService $fonnteService): void
    {
        $phoneNumbers = $this->cluster->reports()
            ->whereNotNull('phone_number')
            ->pluck('phone_number', 'tracking_code');

        $statusLabel = $this->statusLabel($this->newStatus);

        foreach ($phoneNumbers as $trackingCode => $phoneNumber) {
            $trackUrl = config('app.url') . '/track';
            $message = "Halo, laporan kamu dengan kode *{$trackingCode}* kini berstatus: *{$statusLabel}*.\n\nCek detail dan riwayat penanganan di:\n{$trackUrl}\n\nMasukkan kode pelacakan di atas untuk melihat status terbaru.";
            $fonnteService->sendMessage($phoneNumber, $message);
        }
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Sedang Ditangani',
            'resolved' => 'Selesai Ditangani',
            default => 'Dilaporkan',
        };
    }
}