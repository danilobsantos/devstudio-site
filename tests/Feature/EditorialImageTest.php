<?php

namespace Tests\Feature;

use App\Rules\EditorialImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EditorialImageTest extends TestCase
{
    public function test_only_valid_matching_raster_images_within_limits_are_accepted(): void
    {
        $png = UploadedFile::fake()->image('photo.png', 20, 20);
        $this->assertTrue($this->valid($png));
        $this->assertTrue($this->valid(UploadedFile::fake()->image('photo.jpg', 20, 20)));
        $image = imagecreatetruecolor(20, 20);
        ob_start();
        imagewebp($image);
        $webp = ob_get_clean();
        unset($image);
        $this->assertTrue($this->valid(UploadedFile::fake()->createWithContent('photo.webp', $webp)));
        $this->assertFalse($this->valid(UploadedFile::fake()->image('photo.php.png', 20, 20)));
        $this->assertFalse($this->valid(UploadedFile::fake()->createWithContent('photo.png', '<svg></svg>')));
        $this->assertFalse($this->valid(UploadedFile::fake()->createWithContent('photo.jpg', file_get_contents($png->getRealPath()))));
        $this->assertFalse($this->valid(UploadedFile::fake()->createWithContent('photo.svg', '<svg></svg>')));
        $this->assertFalse($this->valid(UploadedFile::fake()->image('large.png', 2049, 1)));
        $this->assertFalse($this->valid(UploadedFile::fake()->image('large.png')->size(5121)));
        $this->assertFalse($this->valid('media/photo.png'));
    }

    private function valid(mixed $file): bool
    {
        return Validator::make(['image' => $file], ['image' => [new EditorialImage]])->passes();
    }
}
