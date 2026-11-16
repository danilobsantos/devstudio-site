<?php

namespace Tests\Unit;

use App\Rules\PageSections;
use PHPUnit\Framework\TestCase;

class PageSectionsTest extends TestCase
{
    public function test_section_envelopes_and_urls_are_checked(): void
    {
        $rule = new PageSections;
        $valid = [
            [],
            [['type' => 'cta', 'data' => ['title' => 'Contato', 'label' => 'Abrir', 'url' => '/contato']]],
            [['type' => 'hero', 'data' => ['title' => 'Olá', 'ctas' => [['label' => 'Abrir', 'url' => 'https://example.com/']]]]],
        ];
        $invalid = [
            [['type' => 'testimonials', 'data' => ['title' => 'Teste']]],
            [['type' => 'hero', 'data' => ['title' => 'Teste', 'image' => 'x']]],
            [['type' => 'cta', 'data' => ['title' => 'Teste', 'label' => 'Abrir', 'url' => '/%2fexample.com']]],
            [['type' => 'cta', 'data' => ['title' => 'Teste', 'label' => 'Abrir', 'url' => 'https://user@example.com/']]],
            [['type' => 'faq', 'data' => ['title' => 'Teste', 'items' => [['question' => 'Q', 'answer' => '<script>']]]]],
            [['type' => 'services_grid', 'data' => ['title' => 'Teste', 'service_ids' => [1, 1]]]],
        ];
        foreach ([$valid, $invalid] as $group => $cases) {
            foreach ($cases as $case) {
                $failed = false;
                $rule->validate('sections', $case, function () use (&$failed) {
                    $failed = true;
                });
                $this->assertSame($group === 1, $failed);
            }
        }
    }
}
