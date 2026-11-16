<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = new User;
        $user->name = 'Synthetic';
        $user->email = 'product@example.invalid';
        $user->password = 'synthetic-test-password';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    public function test_create_edit_list_and_preserve_media(): void
    {
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Product', 'slug' => 'product'])->call('create')->assertHasNoFormErrors();
        $product = Product::query()->where('slug', 'product')->firstOrFail();
        $product->logo_path = 'existing.png';
        $product->save();
        Livewire::test(EditProduct::class, ['record' => $product->id])->fillForm(['name' => 'Updated', 'slug' => 'product', 'features' => [['title' => 'Feature', 'text' => null]], 'audiences' => [['title' => 'Audience', 'text' => null]]])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Updated', $product->fresh()->name);
        $this->assertSame('existing.png', $product->fresh()->logo_path);
        Livewire::test(ListProducts::class)->assertCanSeeTableRecords([$product]);
    }

    public function test_invalid_cta_slug_and_collection_are_rejected(): void
    {
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Test', 'slug' => 'test', 'cta_label' => 'Open'])->call('create')->assertHasFormErrors(['cta_url']);
        $this->assertSame(0, Product::query()->count());
        $component = Livewire::test(CreateProduct::class)->fillForm(['name' => 'Test', 'slug' => 'test']);
        $component->set('data.features', 'bad')->assertHasFormErrors(['features']);
        $component->call('create')->assertHasFormErrors(['features']);
        $component->set('data.features', [])->call('create')->assertHasNoFormErrors();
        $this->assertSame(1, Product::query()->count());
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Duplicate', 'slug' => 'test'])->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Other', 'slug' => 'test', 'locale' => 'en'])->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Bad', 'slug' => 'bad', 'external_url' => '/%2fexample.invalid'])->call('create')->assertHasFormErrors(['external_url']);
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Bad', 'slug' => 'bad'])->set('data.audiences', ['x' => ['title' => 'Test', 'icon' => 'x']])->call('create')->assertHasFormErrors(['audiences']);
        Livewire::test(CreateProduct::class)->fillForm(['name' => '<script>', 'slug' => 'bad'])->call('create')->assertHasFormErrors(['name']);
    }

    public function test_metadata_limits_and_plain_text_are_validated(): void
    {
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Produto', 'slug' => 'produto-meta', 'meta_title' => str_repeat('a', 71)])->call('create')->assertHasFormErrors(['meta_title']);
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Produto', 'slug' => 'produto-meta', 'meta_description' => str_repeat('a', 201)])->call('create')->assertHasFormErrors(['meta_description']);
        Livewire::test(CreateProduct::class)->fillForm(['name' => 'Produto', 'slug' => 'produto-meta', 'meta_title' => '<b>HTML</b>'])->call('create')->assertHasFormErrors(['meta_title']);
    }

    public function test_delete_and_permissions(): void
    {
        $product = new Product;
        $product->name = 'Test';
        $product->slug = 'delete';
        $product->save();
        Livewire::test(EditProduct::class, ['record' => $product->id])->callAction(DeleteAction::class)->assertHasNoActionErrors();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $component = Livewire::test(CreateProduct::class)->fillForm(['name' => 'Denied', 'slug' => 'denied']);
        auth()->user()->role = null;
        auth()->user()->save();
        $component->call('create')->assertForbidden();
        Livewire::test(CreateProduct::class)->assertForbidden();
        auth()->logout();
        Livewire::test(CreateProduct::class)->assertForbidden();
    }

    public function test_repeaters_recover_via_real_actions(): void
    {
        $component = Livewire::test(CreateProduct::class)->fillForm(['name' => 'Test', 'slug' => 'repeat']);
        foreach (['features', 'audiences'] as $field) {
            $component->set('data.'.$field, 'bad')->assertHasFormErrors([$field]);
            $component->call('create')->assertHasFormErrors([$field]);
            $component->callFormComponentAction($field, 'add');
            $component->call('create')->assertHasFormErrors([$field]);
            $id = array_key_first($component->get('data.'.$field));
            $component->set("data.$field.$id.title", 'Valid');
            $component->callFormComponentAction($field, 'add');
            $ids = array_keys($component->get('data.'.$field));
            $new = end($ids);
            $component->callFormComponentAction($field, 'delete', arguments: ['item' => $new]);
        }
        $component->call('create')->assertHasNoFormErrors();
        $product = Product::query()->where('slug', 'repeat')->firstOrFail();
        $this->assertSame('Valid', $product->features[0]['title']);
        $this->assertSame('Valid', $product->audiences[0]['title']);
        $edit = Livewire::test(EditProduct::class, ['record' => $product->id])->set('data.name', 'Changed');
        $edit->set('data.audiences', 'bad')->call('save')->assertHasFormErrors(['audiences']);
        $this->assertSame('Test', $product->fresh()->name);
        $item = array_key_first($edit->get('data.audiences'));
        $edit->set("data.audiences.$item.title", 'Revised')->call('save')->assertHasNoFormErrors();
        $this->assertSame('Revised', $product->fresh()->audiences[0]['title']);
    }
}
