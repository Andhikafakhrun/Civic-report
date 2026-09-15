<?php

namespace Tests\Feature;

use App\Jobs\ClassifyReportJob;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_can_be_submitted_with_valid_data(): void
    {
        Storage::fake('public');
        Queue::fake();

        $response = $this->post('/reports', [
            'category' => 'pothole',
            'photo' => UploadedFile::fake()->image('pothole.jpg'),
            'description' => 'Lubang besar di depan gang.',
            'phone_number' => '081234567890',
            'latitude' => -6.914,
            'longitude' => 107.609,
        ]);

        $response->assertRedirect('/lapor');
        $response->assertSessionHas('tracking_code');

        $this->assertDatabaseHas('reports', [
            'category' => 'pothole',
            'description' => 'Lubang besar di depan gang.',
            'phone_number' => '081234567890',
        ]);

        $report = Report::first();
        $this->assertNotNull($report->tracking_code);
        $this->assertEquals(8, strlen($report->tracking_code));

        Queue::assertPushed(ClassifyReportJob::class);
    }

    public function test_report_submission_fails_without_description(): void
    {
        Storage::fake('public');

        $response = $this->post('/reports', [
            'category' => 'pothole',
            'photo' => UploadedFile::fake()->image('pothole.jpg'),
            'phone_number' => '081234567890',
            'latitude' => -6.914,
            'longitude' => 107.609,
        ]);

        $response->assertSessionHasErrors('description');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_submission_fails_with_invalid_phone_number(): void
    {
        Storage::fake('public');

        $response = $this->post('/reports', [
            'category' => 'pothole',
            'photo' => UploadedFile::fake()->image('pothole.jpg'),
            'description' => 'Test laporan.',
            'phone_number' => 'bukan-nomor-telepon',
            'latitude' => -6.914,
            'longitude' => 107.609,
        ]);

        $response->assertSessionHasErrors('phone_number');
        $this->assertDatabaseCount('reports', 0);
    }
}