<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCluster;
use App\Services\PriorityScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $existingPhoto = Report::whereNotNull('photo_path')->value('photo_path');

        if (!$existingPhoto) {
            $this->command->error('Belum ada foto laporan asli di database. Submit minimal 1 laporan lewat form dulu sebelum menjalankan seeder ini.');
            return;
        }

        $priorityService = new PriorityScoringService();

        $scenarios = [
            [
                'category' => 'pothole',
                'base_lat' => -6.9147,
                'base_lng' => 107.6098,
                'status' => 'reported',
                'reports' => [
                    ['severity' => 'high', 'days_ago' => 2, 'desc' => 'Lubang besar dan dalam, sering bikin motor oleng.'],
                    ['severity' => 'high', 'days_ago' => 2, 'desc' => 'Jalan berlubang parah dekat gang, tergenang air kalau hujan.'],
                    ['severity' => 'high', 'days_ago' => 1, 'desc' => 'Lubang makin melebar, sudah ada motor yang jatuh di sini.'],
                    ['severity' => 'medium', 'days_ago' => 1, 'desc' => 'Aspal retak dan berlubang, cukup mengganggu.'],
                    ['severity' => 'high', 'days_ago' => 0, 'desc' => 'Kondisi jalan makin parah, mohon segera ditangani.'],
                ],
            ],
            [
                'category' => 'trash',
                'base_lat' => -6.9034,
                'base_lng' => 107.6181,
                'status' => 'reported',
                'reports' => [
                    ['severity' => 'medium', 'days_ago' => 21, 'desc' => 'Sampah menumpuk di pinggir jalan, mulai bau.'],
                    ['severity' => 'medium', 'days_ago' => 15, 'desc' => 'Tumpukan sampah belum diangkut sejak seminggu lalu.'],
                    ['severity' => 'low', 'days_ago' => 9, 'desc' => 'Sampah rumah tangga menumpuk di sudut jalan.'],
                    ['severity' => 'medium', 'days_ago' => 4, 'desc' => 'Masih menumpuk, jumlahnya bertambah terus.'],
                ],
            ],
            [
                'category' => 'pothole',
                'base_lat' => -6.9256,
                'base_lng' => 107.6019,
                'status' => 'reported',
                'reports' => [
                    ['severity' => 'medium', 'days_ago' => 0, 'desc' => 'Lubang kecil baru muncul setelah hujan deras kemarin.'],
                    ['severity' => 'low', 'days_ago' => 0, 'desc' => 'Ada lubang di tepi jalan, belum terlalu besar.'],
                ],
            ],
            [
                'category' => 'trash',
                'base_lat' => -6.8983,
                'base_lng' => 107.6312,
                'status' => 'resolved',
                'reports' => [
                    ['severity' => 'low', 'days_ago' => 35, 'desc' => 'Sampah menumpuk di dekat pasar.'],
                    ['severity' => 'medium', 'days_ago' => 33, 'desc' => 'Tumpukan sampah makin banyak, mengganggu pejalan kaki.'],
                    ['severity' => 'medium', 'days_ago' => 30, 'desc' => 'Sudah dilaporkan beberapa hari, belum ada tindakan.'],
                ],
            ],
            [
                'category' => 'streetlight',
                'base_lat' => -6.9089,
                'base_lng' => 107.6234,
                'status' => 'reported',
                'reports' => [
                    ['severity' => 'medium', 'days_ago' => 3, 'desc' => 'Lampu jalan mati sejak 3 hari lalu, gelap sekali malam hari.'],
                    ['severity' => 'high', 'days_ago' => 2, 'desc' => 'Beberapa lampu jalan berturut-turut mati, rawan kecelakaan.'],
                ],
            ],
            [
                'category' => 'drainage',
                'base_lat' => -6.9201,
                'base_lng' => 107.6402,
                'status' => 'in_progress',
                'reports' => [
                    ['severity' => 'high', 'days_ago' => 5, 'desc' => 'Saluran air tersumbat, meluap tiap hujan deras.'],
                    ['severity' => 'medium', 'days_ago' => 4, 'desc' => 'Got mampet, air menggenang di jalan.'],
                    ['severity' => 'high', 'days_ago' => 1, 'desc' => 'Genangan makin parah, mengganggu kendaraan lewat.'],
                ],
            ],
            [
                'category' => 'fallen_tree',
                'base_lat' => -6.8956,
                'base_lng' => 107.6089,
                'status' => 'reported',
                'reports' => [
                    ['severity' => 'high', 'days_ago' => 0, 'desc' => 'Pohon tumbang menutup separuh jalan setelah angin kencang.'],
                ],
            ],
        ];

        foreach ($scenarios as $scenario) {
            $cluster = ReportCluster::create([
                'category' => $scenario['category'],
                'center_latitude' => $scenario['base_lat'],
                'center_longitude' => $scenario['base_lng'],
                'report_count' => count($scenario['reports']),
                'priority_score' => 0,
                'status' => $scenario['status'],
                'first_reported_at' => now(),
                'last_reported_at' => now(),
            ]);

            $latestTimestamp = null;
            $earliestTimestamp = null;

            foreach ($scenario['reports'] as $reportData) {
                $timestamp = Carbon::now()->subDays($reportData['days_ago'])->subHours(rand(0, 23));

                $report = Report::create([
                    'category' => $scenario['category'],
                    'photo_path' => $existingPhoto,
                    'description' => $reportData['desc'],
                    'latitude' => $scenario['base_lat'] + $this->jitter(),
                    'longitude' => $scenario['base_lng'] + $this->jitter(),
                    'severity' => $reportData['severity'],
                    'status' => $scenario['status'],
                    'cluster_id' => $cluster->id,
                ]);

                DB::table('reports')->where('id', $report->id)->update([
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                    'tracking_code' => strtoupper(Str::random(8)),
                ]);

                if (!$latestTimestamp || $timestamp->greaterThan($latestTimestamp)) {
                    $latestTimestamp = $timestamp;
                }
                if (!$earliestTimestamp || $timestamp->lessThan($earliestTimestamp)) {
                    $earliestTimestamp = $timestamp;
                }
            }

            $cluster->update([
                'first_reported_at' => $earliestTimestamp,
                'last_reported_at' => $latestTimestamp,
            ]);

            $priorityService->updateClusterScore($cluster->fresh());
        }

        $this->command->info('Berhasil membuat ' . count($scenarios) . ' cluster demo dengan total laporan seed.');
    }

    private function jitter(): float
    {
        return (mt_rand(-30, 30) / 100000);
    }
}