<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportCluster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransparencyIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_transparency_page_shows_honest_empty_state_without_resolved_cases(): void
    {
        ReportCluster::create([
            'category' => 'pothole',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 1,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

        Report::create([
            'category' => 'pothole',
            'photo_path' => 'reports/dummy.jpg',
            'description' => 'Laporan belum ditangani',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'status' => 'reported',
        ]);

        $response = $this->get('/transparansi');

        $response->assertSee('Belum ada kasus yang selesai dengan bukti foto');
        $response->assertDontSee('Laporan belum ditangani');
    }

    public function test_reopened_cluster_photo_does_not_appear_in_public_evidence(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);

        $cluster = ReportCluster::create([
            'category' => 'pothole',
            'center_latitude' => -6.9,
            'center_longitude' => 107.6,
            'report_count' => 1,
            'priority_score' => 0,
            'status' => 'reported',
            'first_reported_at' => now(),
            'last_reported_at' => now(),
        ]);

        Report::create([
            'category' => 'pothole',
            'photo_path' => 'reports/dummy.jpg',
            'description' => 'Laporan untuk tes reopen',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'status' => 'reported',
            'cluster_id' => $cluster->id,
        ]);

        $this->actingAs($admin)->patch("/clusters/{$cluster->id}", [
            'status' => 'resolved',
            'note' => 'CATATAN_UNIK_REOPEN_TEST',
            'proof_photo' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response = $this->get('/bukti-nyata');
        $response->assertSee('CATATAN_UNIK_REOPEN_TEST');

        $this->actingAs($admin)->patch("/clusters/{$cluster->id}", ['status' => 'in_progress']);

        $responseProof = $this->get('/bukti-nyata');
        $responseProof->assertDontSee('CATATAN_UNIK_REOPEN_TEST');

        $responseTransparency = $this->get('/transparansi');
        $responseTransparency->assertDontSee('CATATAN_UNIK_REOPEN_TEST');
    }
}