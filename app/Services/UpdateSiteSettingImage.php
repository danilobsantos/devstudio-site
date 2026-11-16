<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Rules\EditorialImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RuntimeException;
use Throwable;

class UpdateSiteSettingImage
{
    public function update(SiteSetting $setting, string $field, UploadedFile $file): string
    {
        Gate::authorize('update', $setting);

        if (! in_array($field, ['logo_light_background', 'logo_dark_background', 'icon', 'og_image'], true)) {
            throw ValidationException::withMessages(['image' => 'Campo de imagem inválido.']);
        }

        Validator::make(['image' => $file], ['image' => [new EditorialImage]])->validate();
        $orientation = $this->jpegOrientation($file);
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if ($image === false) {
            throw ValidationException::withMessages(['image' => 'Imagem inválida.']);
        }

        try {
            $this->applyOrientation($image, $orientation);
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            ob_start();
            try {
                if (! imagepng($image)) {
                    throw new RuntimeException('Não foi possível processar a imagem.');
                }
                $content = ob_get_contents();
            } finally {
                ob_end_clean();
            }
        } finally {
            unset($image);
        }

        $db = DB::connection();
        if ($db->transactionLevel() !== 0) {
            throw new RuntimeException('Operação indisponível dentro de outra transação.');
        }
        $private = Storage::disk('local');
        $public = Storage::disk('public');
        if (! $private->getAdapter() instanceof LocalFilesystemAdapter || ! $public->getAdapter() instanceof LocalFilesystemAdapter) {
            throw new RuntimeException('Armazenamento local sem publicação exclusiva suportada.');
        }

        $path = 'site-settings/'.Str::uuid().'.png';
        $stagingPath = 'site-settings/.staging/'.Str::uuid().'.png';
        $published = false;
        $stagingReady = false;
        $stagingInode = false;
        try {
            $this->writeStagingFile($private, $stagingPath, $content);
            $stagingReady = true;
            $stagingInode = fileinode($private->path($stagingPath));
            if ($stagingInode === false) {
                throw new RuntimeException('Não foi possível verificar o staging privado.');
            }

            $db->beginTransaction();
            $current = SiteSetting::query()->lockForUpdate()->findOrFail($setting->getKey());
            Gate::authorize('update', $current);
            $this->publish($private, $public, $stagingPath, $path, $stagingInode, hash('sha256', $content));
            $published = true;

            $current->{$field} = $path;
            if (! $current->saveOrFail()) {
                throw new RuntimeException('Não foi possível salvar a referência da imagem.');
            }
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->transactionLevel() > 0) {
                try {
                    $db->rollBack();
                } catch (Throwable) {
                    Log::warning('Rollback de imagem não confirmado; reconciliação necessária.');
                }
            }
            if ($published) {
                Log::warning('Imagem publicada sem confirmação da referência; reconciliação necessária.');
            }

            throw $exception;
        } finally {
            $stagingFile = $private->path($stagingPath);
            if ($stagingReady && ! is_link($stagingFile) && is_file($stagingFile) && fileinode($stagingFile) === $stagingInode &&
                ! @unlink($stagingFile)) {
                Log::warning('Staging privado próprio não removido; reconciliação necessária.');
            }
        }

        return $path;
    }

    private function jpegOrientation(UploadedFile $file): int
    {
        if ($file->getMimeType() !== 'image/jpeg') {
            return 1;
        }
        if (! function_exists('exif_read_data')) {
            throw ValidationException::withMessages(['image' => 'Não é possível processar a orientação da imagem.']);
        }

        $metadata = @exif_read_data($file->getRealPath(), 'IFD0', true);
        $orientation = $metadata['IFD0']['Orientation'] ?? 1;
        if (! is_numeric($orientation) || ! in_array((int) $orientation, range(1, 8), true)) {
            throw ValidationException::withMessages(['image' => 'Orientação EXIF inválida.']);
        }

        return (int) $orientation;
    }

    private function applyOrientation(\GdImage &$image, int $orientation): void
    {
        $flip = match ($orientation) {
            2, 7 => IMG_FLIP_HORIZONTAL,
            4, 5 => IMG_FLIP_VERTICAL,
            default => null,
        };
        if ($flip !== null && ! imageflip($image, $flip)) {
            throw new RuntimeException('Não foi possível corrigir a orientação da imagem.');
        }

        $angle = match ($orientation) {
            3 => 180,
            5, 6, 7 => -90,
            8 => 90,
            default => null,
        };
        if ($angle !== null) {
            $rotated = imagerotate($image, $angle, 0);
            if ($rotated === false) {
                throw new RuntimeException('Não foi possível corrigir a orientação da imagem.');
            }
            $image = $rotated;
        }
    }

    private function writeStagingFile($disk, string $path, string $content): void
    {
        $directory = dirname($path);
        $directoryPath = $disk->path($directory);
        if (is_link($directoryPath) || is_link(dirname($directoryPath)) ||
            (! $disk->makeDirectory($directory) && ! is_dir($directoryPath)) || ! @chmod($directoryPath, 0700)) {
            throw new RuntimeException('Não foi possível preparar o staging privado.');
        }

        $stream = @fopen($disk->path($path), 'xb');
        if ($stream === false) {
            throw new RuntimeException('Não foi possível reservar o staging privado.');
        }
        $complete = false;
        try {
            try {
                $offset = 0;
                while ($offset < strlen($content)) {
                    $bytes = @fwrite($stream, substr($content, $offset));
                    if ($bytes === false || $bytes === 0) {
                        throw new RuntimeException('Não foi possível gravar o staging privado.');
                    }
                    $offset += $bytes;
                }
                if (! @fflush($stream)) {
                    throw new RuntimeException('Não foi possível finalizar o staging privado.');
                }
            } finally {
                if (! @fclose($stream)) {
                    throw new RuntimeException('Não foi possível fechar o staging privado.');
                }
            }

            if (! @chmod($disk->path($path), 0444) || (fileperms($disk->path($path)) & 0777) !== 0444) {
                throw new RuntimeException('Não foi possível tornar o staging somente leitura.');
            }
            $complete = true;
        } finally {
            $stagingFile = $disk->path($path);
            if (! $complete && ! is_link($stagingFile) && is_file($stagingFile) && ! @unlink($stagingFile)) {
                Log::warning('Staging incompleto não removido; reconciliação necessária.');
            }
        }
    }

    private function publish($private, $public, string $stagingPath, string $path, int $stagingInode, string $expectedHash): void
    {
        $directoryPath = $public->path('site-settings');
        if (is_link($directoryPath) || (! $public->makeDirectory('site-settings') && ! is_dir($directoryPath))) {
            throw new RuntimeException('Não foi possível preparar a publicação pública.');
        }

        $source = $private->path($stagingPath);
        if (is_link($source) || ! is_file($source) || (fileperms($source) & 0777) !== 0444 ||
            fileinode($source) !== $stagingInode) {
            throw new RuntimeException('Staging não está somente leitura para publicação.');
        }
        $sourceOwner = fileowner($source);
        $directoryOwner = fileowner(dirname($source));
        if ($sourceOwner === false || $directoryOwner === false || $sourceOwner !== $directoryOwner) {
            throw new RuntimeException('Staging não está somente leitura para publicação.');
        }

        $destination = $public->path($path);
        $previousUmask = umask(0777);
        try {
            $output = @fopen($destination, 'x+b');
        } finally {
            umask($previousUmask);
        }
        if ($output === false) {
            throw new RuntimeException('Não foi possível reservar a imagem sem sobrescrever.');
        }

        $outputStat = fstat($output);
        $destinationInode = is_array($outputStat) ? ($outputStat['ino'] ?? false) : false;
        $input = null;
        $complete = false;
        try {
            // Exclusive creation reserves the path; mode 000 keeps it unreadable until the full copy is closed.
            if ($destinationInode === false || (fileperms($destination) & 0777) !== 0) {
                throw new RuntimeException('Não foi possível manter a imagem inacessível durante a cópia.');
            }
            $input = @fopen($source, 'rb');
            if ($input === false) {
                $input = null;
                throw new RuntimeException('Não foi possível ler o staging privado.');
            }
            $expectedBytes = filesize($source);
            $copiedBytes = @stream_copy_to_stream($input, $output);
            if ($expectedBytes === false || $copiedBytes !== $expectedBytes || ! @fflush($output)) {
                throw new RuntimeException('Não foi possível copiar integralmente a imagem.');
            }
            if (! @rewind($output)) {
                throw new RuntimeException('Não foi possível verificar a imagem copiada.');
            }
            $hash = hash_init('sha256');
            $hashedBytes = hash_update_stream($hash, $output);
            if ($hashedBytes !== $expectedBytes || ! hash_equals($expectedHash, hash_final($hash))) {
                throw new RuntimeException('A cópia não corresponde à imagem sanitizada.');
            }
            if (! @fclose($input)) {
                $input = null;
                throw new RuntimeException('Não foi possível fechar o staging privado.');
            }
            $input = null;
            if (! @fclose($output)) {
                $output = null;
                throw new RuntimeException('Não foi possível finalizar a imagem publicada.');
            }
            $output = null;
            if (! @chmod($destination, 0644) || (fileperms($destination) & 0777) !== 0644) {
                throw new RuntimeException('Não foi possível publicar a imagem completa.');
            }
            $complete = true;
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            if (! $complete && ! is_link($destination) && is_file($destination) &&
                fileinode($destination) === $destinationInode && ! @unlink($destination)) {
                Log::warning('Imagem incompleta não removida; reconciliação necessária.');
            }
        }

    }
}
