<?php

namespace Tests\Unit;

use App\Enums\LeadStatus;
use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

class EnumsTest extends TestCase
{
    public static function enums(): array
    {
        return [
            [PublicationStatus::class, ['draft', 'published']],
            [LeadStatus::class, ['new', 'in_progress', 'qualified', 'converted', 'discarded']],
            [UserRole::class, ['admin', 'editor']],
        ];
    }

    #[DataProvider('enums')]
    public function test_values_round_trip_and_invalid_values_are_rejected(string $enum, array $values): void
    {
        $this->assertSame($values, array_column($enum::cases(), 'value'));

        foreach ($enum::cases() as $case) {
            $this->assertSame($case, $enum::from($case->value));
        }

        $this->assertNull($enum::tryFrom('invalid'));
        $this->expectException(ValueError::class);
        $enum::from('invalid');
    }
}
