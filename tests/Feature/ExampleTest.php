<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_without_published_cms_page_fails_closed(): void
    {
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);

        $this->get('/')->assertNotFound();
    }
}
