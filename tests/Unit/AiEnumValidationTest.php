<?php

namespace Tests\Unit;

use App\Services\AiClassificationService;
use ReflectionMethod;
use Tests\TestCase;

class AiEnumValidationTest extends TestCase
{
    private function callValidateEnums(array $data): array
    {
        $service = new AiClassificationService();
        $method = new ReflectionMethod($service, 'validateEnums');
        $method->setAccessible(true);

        return $method->invoke($service, $data);
    }

    public function test_valid_category_and_severity_are_kept(): void
    {
        $result = $this->callValidateEnums([
            'confirmed_category' => 'pothole',
            'severity' => 'high',
            'reasoning' => 'test',
        ]);

        $this->assertEquals('pothole', $result['confirmed_category']);
        $this->assertEquals('high', $result['severity']);
    }

    public function test_invalid_category_is_stripped(): void
    {
        $result = $this->callValidateEnums([
            'confirmed_category' => 'unknown_category',
            'severity' => 'high',
        ]);

        $this->assertArrayNotHasKey('confirmed_category', $result);
        $this->assertEquals('high', $result['severity']);
    }

    public function test_invalid_severity_is_stripped(): void
    {
        $result = $this->callValidateEnums([
            'confirmed_category' => 'trash',
            'severity' => 'very_high',
        ]);

        $this->assertEquals('trash', $result['confirmed_category']);
        $this->assertArrayNotHasKey('severity', $result);
    }

    public function test_missing_fields_do_not_crash(): void
    {
        $result = $this->callValidateEnums([]);

        $this->assertArrayNotHasKey('confirmed_category', $result);
        $this->assertArrayNotHasKey('severity', $result);
    }
}