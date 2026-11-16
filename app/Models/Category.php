<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

#[Fillable(['name', 'slug', 'description', 'type'])]
class Category extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (! in_array($category->type, ['post', 'project'], true)) {
                throw ValidationException::withMessages(['type' => 'Tipo de categoria inválido.']);
            }

            if ($category->exists && $category->isDirty('type')) {
                throw ValidationException::withMessages(['type' => 'O tipo da categoria não pode ser alterado.']);
            }
        });
    }

    public static function validateFor(?int $id, string $type, ?string $connection): void
    {
        if ($id !== null && ! (new self)->setConnection($connection)->newQuery()->whereKey($id)->where('type', $type)->exists()) {
            throw ValidationException::withMessages(['category_id' => 'Categoria inexistente ou incompatível.']);
        }
    }
}
