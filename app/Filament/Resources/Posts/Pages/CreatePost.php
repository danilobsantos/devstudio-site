<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePost extends CreateRecord
{
    use HandlesTags;

    protected static string $resource = PostResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureTagsValid();
        PostResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PostResource::validatedData($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $post = new Post;
        $post->fill($data);
        $post->author_id = auth()->id();
        $post->save();

        return $post;
    }
}
