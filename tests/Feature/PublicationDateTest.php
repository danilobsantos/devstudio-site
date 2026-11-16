<?php

namespace Tests\Feature;

use App\Support\PublicationDate;
use InvalidArgumentException;
use Tests\TestCase;

class PublicationDateTest extends TestCase
{
    public function test_local_time_is_stored_as_utc_and_displayed_back_locally(): void
    {
        $this->assertSame('2026-11-12 15:00:00', PublicationDate::toUtc('2026-11-12T12:00'));
        $this->assertSame('2026-11-12T12:00', PublicationDate::toDisplay('2026-11-12 15:00:00'));
    }

    public function test_leap_day_is_accepted(): void
    {
        $this->assertSame('2028-02-29 13:00:00', PublicationDate::toUtc('2028-02-29T10:00'));
    }

    public function test_nonexistent_dates_are_rejected_instead_of_rolled_over(): void
    {
        foreach (['2026-02-30T10:00', '2026-02-29T10:00', '2026-04-31T10:00', '2026-13-01T10:00', '2026-00-10T10:00'] as $value) {
            try {
                PublicationDate::toUtc($value);
                $this->fail("Data inexistente aceita: {$value}.");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_non_display_formats_are_rejected(): void
    {
        foreach (['2026-11-12T12:00:00.000000Z', '2026-11-12 12:00', '12/11/2026 12:00', '2026-11-12T12:00:30', '2026-2-3T10:00', 'lixo'] as $value) {
            try {
                PublicationDate::toUtc($value);
                $this->fail("Formato inválido aceito: {$value}.");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_null_and_empty_become_null(): void
    {
        $this->assertNull(PublicationDate::toUtc(null));
        $this->assertNull(PublicationDate::toUtc(''));
        $this->assertNull(PublicationDate::normalize(['published_at' => null])['published_at']);
    }

    public function test_normalize_converts_once_and_leaves_other_keys_alone(): void
    {
        $data = PublicationDate::normalize(['title' => 'Agenda', 'published_at' => '2026-11-12T12:00']);

        $this->assertSame(['title' => 'Agenda', 'published_at' => '2026-11-12 15:00:00'], $data);
        $this->assertSame(['title' => 'Agenda'], PublicationDate::normalize(['title' => 'Agenda']));
    }

    public function test_direct_caller_gets_the_same_rejection_outside_the_form(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PublicationDate::normalize(['published_at' => '2026-02-30T10:00']);
    }
}
