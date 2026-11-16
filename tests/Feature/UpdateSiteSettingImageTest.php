<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\UpdateSiteSettingImage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Tests\TestCase;

class UpdateSiteSettingImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_admin_upload_reencodes_and_preserves_existing_references(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        Storage::disk('public')->put('shared.png', 'existing');
        $setting->logo_dark_background = 'shared.png';
        $setting->logo_light_background = 'shared.png';
        $setting->save();
        $png = UploadedFile::fake()->image('image.png');
        $input = UploadedFile::fake()->createWithContent('image.png', file_get_contents($png->getRealPath()).'synthetic-polyglot-marker');

        $path = app(UpdateSiteSettingImage::class)->update($setting, 'logo_light_background', $input);
        $this->assertMatchesRegularExpression('#^site-settings/[0-9a-f-]{36}\.png$#', $path);
        $this->assertSame($path, $setting->fresh()->logo_light_background);
        $this->assertSame('shared.png', $setting->fresh()->logo_dark_background);
        $this->assertSame('existing', Storage::disk('public')->get('shared.png'));
        $this->assertStringNotContainsString('synthetic-polyglot-marker', Storage::disk('public')->get($path));
        $this->assertSame('image/png', getimagesizefromstring(Storage::disk('public')->get($path))['mime']);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertFileExists($input->getRealPath());
    }

    public function test_jpeg_exif_orientation_is_applied_to_the_sanitized_derivative(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $image = imagecreatetruecolor(80, 40);
        ob_start();
        imagejpeg($image);
        $jpeg = ob_get_clean();
        unset($image);
        $exif = "Exif\0\0II\x2A\x00\x08\x00\x00\x00\x01\x00\x12\x01\x03\x00\x01\x00\x00\x00\x06\x00\x00\x00\x00\x00\x00\x00";
        $jpeg = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
        $file = UploadedFile::fake()->createWithContent('oriented.jpg', $jpeg);

        $path = app(UpdateSiteSettingImage::class)->update($setting, 'og_image', $file);
        $dimensions = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([40, 80], [$dimensions[0], $dimensions[1]]);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_editor_and_guest_cannot_upload(): void
    {
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        foreach ([null, $this->user(UserRole::Editor)] as $user) {
            if ($user) {
                $this->actingAs($user);
            } else {
                auth()->logout();
            }
            try {
                app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
                $this->fail('Unauthorized upload accepted.');
            } catch (AuthorizationException) {
                $this->assertNull($setting->fresh()->icon);
                $this->assertSame([], Storage::disk('public')->allFiles());
            }
        }
    }

    public function test_invalid_file_or_field_never_changes_existing_record(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $png = UploadedFile::fake()->image('image.png');
        $truncated = UploadedFile::fake()->createWithContent('image.png', substr(file_get_contents($png->getRealPath()), 0, 33));
        foreach ([['icon', UploadedFile::fake()->createWithContent('image.png', '<svg></svg>')], ['icon', $truncated], ['display_name', UploadedFile::fake()->image('image.png')]] as [$field, $file]) {
            try {
                app(UpdateSiteSettingImage::class)->update($setting, $field, $file);
                $this->fail('Invalid upload accepted.');
            } catch (ValidationException) {
                $this->assertSame('Synthetic', $setting->fresh()->display_name);
                $this->assertNull($setting->fresh()->icon);
                $this->assertSame([], Storage::disk('public')->allFiles());
            }
        }
    }

    public function test_failed_save_preserves_previous_reference_and_file(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $setting->icon = 'shared.png';
        $setting->save();
        Storage::disk('public')->put('shared.png', 'existing');
        SiteSetting::saving(fn () => throw new RuntimeException('Synthetic save failure'));
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Failure not propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic save failure', $exception->getMessage());
            $this->assertSame('shared.png', $setting->fresh()->icon);
            $this->assertSame('existing', Storage::disk('public')->get('shared.png'));
            $this->assertCount(1, Storage::disk('public')->files('site-settings'));
        } finally {
            SiteSetting::flushEventListeners();
        }
    }

    public function test_storage_failure_does_not_change_the_record(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $privateDisk = Storage::disk('local');
        $disk = $this->mock(FilesystemAdapter::class);
        $disk->shouldReceive('getAdapter')->once()->andReturn($this->mock(LocalFilesystemAdapter::class));
        $disk->shouldReceive('path')->with('site-settings')->once()->andReturn('/unwritable/site-settings');
        $disk->shouldReceive('makeDirectory')->once()->andReturnFalse();
        Storage::shouldReceive('disk')->with('local')->andReturn($privateDisk);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Storage failure not propagated.');
        } catch (RuntimeException) {
            $this->assertNull($setting->fresh()->icon);
        }
    }

    public function test_non_local_storage_is_rejected(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $privateDisk = Storage::disk('local');
        $disk = $this->mock(FilesystemAdapter::class);
        $disk->shouldReceive('getAdapter')->once()->andReturn($this->mock(\League\Flysystem\FilesystemAdapter::class));
        Storage::shouldReceive('disk')->with('local')->andReturn($privateDisk);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Unsupported disk accepted.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Armazenamento local sem publicação exclusiva suportada.', $exception->getMessage());
            $this->assertNull($setting->fresh()->icon);
        }
    }

    public function test_rollback_preserves_file_with_simulated_external_reference(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $externalReference = null;
        SiteSetting::saving(function ($record) use (&$externalReference) {
            $externalReference = $record->icon;
            throw new RuntimeException('Synthetic save failure');
        });
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Failure not propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic save failure', $exception->getMessage());
            $this->assertNull($setting->fresh()->icon);
            $this->assertNotNull($externalReference);
            Storage::disk('public')->assertExists($externalReference);
        } finally {
            SiteSetting::flushEventListeners();
        }
    }

    public function test_interleaved_collision_does_not_overwrite_existing_file(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        $uuid = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $path = 'site-settings/'.$uuid.'.png';
        Str::createUuidsUsing(fn () => Uuid::fromString($uuid));
        Event::listen(TransactionBeginning::class, function () use ($path) {
            $staged = Storage::disk('local')->files('site-settings/.staging');
            $this->assertCount(1, $staged);
            $this->assertSame(0700, fileperms(Storage::disk('local')->path('site-settings/.staging')) & 0777);
            $this->assertSame(0444, fileperms(Storage::disk('local')->path($staged[0])) & 0777);
            $this->assertSame(
                fileowner(Storage::disk('local')->path('site-settings/.staging')),
                fileowner(Storage::disk('local')->path($staged[0]))
            );
            $this->assertSame('image/png', getimagesize(Storage::disk('local')->path($staged[0]))['mime']);
            Storage::disk('public')->assertMissing($path);
            Storage::disk('public')->put($path, 'other writer');
        });
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Collision not rejected.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Não foi possível reservar a imagem sem sobrescrever.', $exception->getMessage());
            $this->assertSame('other writer', Storage::disk('public')->get($path));
            $this->assertNull($setting->fresh()->icon);
        } finally {
            Event::forget(TransactionBeginning::class);
            Str::createUuidsNormally();
        }
    }

    public function test_staging_mutation_before_copy_is_detected_and_not_published(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        Event::listen(TransactionBeginning::class, function () {
            $staged = Storage::disk('local')->files('site-settings/.staging');
            $this->assertCount(1, $staged);
            $path = Storage::disk('local')->path($staged[0]);
            $size = filesize($path);
            chmod($path, 0644);
            file_put_contents($path, str_repeat('X', $size));
            chmod($path, 0444);
        });
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Mutated staging content was published.');
        } catch (RuntimeException $exception) {
            $this->assertSame('A cópia não corresponde à imagem sanitizada.', $exception->getMessage());
            $this->assertNull($setting->fresh()->icon);
            $this->assertSame([], Storage::disk('public')->allFiles());
            $this->assertSame([], Storage::disk('local')->allFiles());
        } finally {
            Event::forget(TransactionBeginning::class);
            if (DB::connection()->transactionLevel() > 0) {
                DB::rollBack();
            }
        }
    }

    public function test_commit_failure_is_ambiguous_and_never_deletes_new_file(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        Event::listen(TransactionCommitting::class, fn () => throw new RuntimeException('Synthetic commit failure'));
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Failure not propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic commit failure', $exception->getMessage());
            $this->assertCount(1, Storage::disk('public')->files('site-settings'));
        } finally {
            Event::forget(TransactionCommitting::class);
            if (DB::connection()->transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        $this->assertNull($setting->fresh()->icon);
    }

    public function test_exception_after_commit_preserves_referenced_file(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        Event::listen(TransactionCommitted::class, function ($event) {
            if ($event->connection->transactionLevel() === 0) {
                throw new RuntimeException('Synthetic post-commit failure');
            }
        });
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Failure not propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic post-commit failure', $exception->getMessage());
            $this->assertNotNull($setting->fresh()->icon);
            Storage::disk('public')->assertExists($setting->fresh()->icon);
        } finally {
            Event::forget(TransactionCommitted::class);
        }
    }

    public function test_nested_transaction_is_rejected_without_writing(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        DB::beginTransaction();
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Nested transaction accepted.');
        } catch (RuntimeException) {
            $this->assertSame([], Storage::disk('public')->allFiles());
        } finally {
            DB::rollBack();
        }
    }

    public function test_begin_transaction_failure_removes_owned_private_staging(): void
    {
        $this->actingAs($this->user(UserRole::Admin));
        $setting = SiteSetting::create(['display_name' => 'Synthetic']);
        Event::listen(TransactionBeginning::class, fn () => throw new RuntimeException('Synthetic begin failure'));
        try {
            app(UpdateSiteSettingImage::class)->update($setting, 'icon', UploadedFile::fake()->image('image.png'));
            $this->fail('Begin failure not propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic begin failure', $exception->getMessage());
            $this->assertNull($setting->fresh()->icon);
            $this->assertSame([], Storage::disk('local')->allFiles());
            $this->assertSame([], Storage::disk('public')->allFiles());
        } finally {
            Event::forget(TransactionBeginning::class);
            if (DB::connection()->transactionLevel() > 0) {
                DB::rollBack();
            }
        }
    }

    private function user(UserRole $role): User
    {
        $user = new User;
        $user->name = 'Synthetic';
        $user->email = $role->value.'@example.invalid';
        $user->password = 'synthetic';
        $user->role = $role;
        $user->save();

        return $user;
    }
}
