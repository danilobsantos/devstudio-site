<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class EditorialImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // shortcut: limite conservador provisório de 4 MP, revisar com medição de GD no host.
        if (! $value instanceof UploadedFile || ! $value->isValid() || $value->getSize() > 5 * 1024 * 1024 ||
            ! preg_match('/^[^\.\/\\\\]+\.(jpg|jpeg|png|webp)$/i', $value->getClientOriginalName(), $matches)) {
            $fail('Imagem inválida.');

            return;
        }

        $mime = match (strtolower($matches[1])) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        };
        $image = @getimagesize($value->getRealPath());
        if ($value->getMimeType() !== $mime || ! $image || $image['mime'] !== $mime ||
            $image[0] < 1 || $image[1] < 1 || $image[0] > 2048 || $image[1] > 2048 || $image[0] * $image[1] > 4000000) {
            $fail('Imagem inválida.');
        }
    }
}
