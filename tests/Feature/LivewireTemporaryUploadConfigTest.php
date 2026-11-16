<?php

namespace Tests\Feature;

use Tests\TestCase;

class LivewireTemporaryUploadConfigTest extends TestCase
{
    public function test_temporary_uploads_use_private_disk_and_conservative_limits(): void
    {
        $this->assertSame('local', config('livewire.temporary_file_upload.disk'));
        $this->assertSame('file|max:5120', config('livewire.temporary_file_upload.rules'));
        $this->assertSame('storage/app/private', str_replace(base_path().'/', '', config('filesystems.disks.local.root')));
        $this->assertSame([], config('livewire.temporary_file_upload.preview_mimes'));
        $this->assertTrue(config('livewire.temporary_file_upload.cleanup'));
    }
}
