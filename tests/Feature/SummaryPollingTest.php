<?php

namespace Tests\Feature;

use App\Jobs\GenerateSummaryJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SummaryPollingTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_summary_dispatches_job_instead_of_blocking(): void
    {
        Queue::fake();

        $user = User::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($user)->post('/dashboard/summary');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('info');

        Queue::assertPushed(GenerateSummaryJob::class);
    }

    public function test_summary_status_endpoint_returns_json(): void
    {
        $user = User::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($user)->get('/dashboard/summary/status');

        $response->assertOk();
        $response->assertJsonStructure(['status']);
    }
}