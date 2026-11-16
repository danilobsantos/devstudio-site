<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilesystemPromotionProbeTest extends TestCase
{
    public function test_exclusive_copy_keeps_partial_unreadable_and_refuses_collision(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $private = Storage::disk('local');
        $public = Storage::disk('public');
        $partial = $private->path('partial.png');
        file_put_contents($partial, 'complete sanitized image');
        $this->assertSame([], $public->allFiles());

        $destination = $public->path('image.png');
        $previousUmask = umask(0777);
        try {
            $output = fopen($destination, 'xb');
        } finally {
            umask($previousUmask);
        }
        $this->assertIsResource($output);
        fwrite($output, 'complete');
        $this->assertSame(0, fileperms($destination) & 0777);
        fwrite($output, ' sanitized image');
        fflush($output);
        fclose($output);
        chmod($destination, 0644);
        $this->assertSame('complete sanitized image', $public->get('image.png'));
        $this->assertFalse(@fopen($destination, 'xb'));
        $this->assertSame('complete sanitized image', $public->get('image.png'));
        $this->assertSame('complete sanitized image', $private->get('partial.png'));
    }

    public function test_copy_promotion_uses_a_distinct_inode_and_symlink_directory_can_redirect(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $private = Storage::disk('local');
        $public = Storage::disk('public');
        $private->put('complete.png', 'synthetic complete');
        $source = $private->path('complete.png');
        $destination = $public->path('image.png');
        $previousUmask = umask(0777);
        try {
            $output = fopen($destination, 'xb');
        } finally {
            umask($previousUmask);
        }
        $this->assertIsResource($output);
        $input = fopen($source, 'rb');
        stream_copy_to_stream($input, $output);
        fclose($input);
        fflush($output);
        fclose($output);
        chmod($destination, 0644);
        $this->assertNotSame(fileinode($source), fileinode($destination));
        file_put_contents($source, 'changed source');
        $this->assertSame('synthetic complete', $public->get('image.png'));

        $private->makeDirectory('outside');
        $this->assertTrue(symlink($private->path('outside'), $public->path('redirect')));
        file_put_contents($public->path('redirect/redirected.png'), 'redirected');
        $this->assertSame('redirected', $private->get('outside/redirected.png'));
    }
}
