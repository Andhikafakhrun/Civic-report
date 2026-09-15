<?php

namespace Tests\Unit;

use Tests\TestCase;

class AiServiceConfigTest extends TestCase
{
    public function test_ai_classification_service_has_timeout_and_retry(): void
    {
        $source = file_get_contents(app_path('Services/AiClassificationService.php'));

        $this->assertStringContainsString('timeout(', $source,
            'AiClassificationService harus punya Http::timeout() untuk mencegah request menggantung tanpa batas.');

        $this->assertStringContainsString('->retry(', $source,
            'AiClassificationService harus punya Http::retry() untuk tahan terhadap kegagalan jaringan sesaat.');
    }

    public function test_trend_summary_service_has_timeout_and_retry(): void
    {
        $source = file_get_contents(app_path('Services/TrendSummaryService.php'));

        $this->assertStringContainsString('timeout(', $source,
            'TrendSummaryService harus punya Http::timeout() untuk mencegah request menggantung tanpa batas.');

        $this->assertStringContainsString('->retry(', $source,
            'TrendSummaryService harus punya Http::retry() untuk tahan terhadap kegagalan jaringan sesaat.');
    }
}