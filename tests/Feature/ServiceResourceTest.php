<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
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
        $user->email = 'service-test@example.invalid';
        $user->password = 'synthetic-test-password';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    public function test_create_edit_and_list(): void
    {
        $data = ['title' => ' Serviço ', 'slug' => 'servico', 'locale' => 'pt_BR', 'status' => 'draft', 'sort_order' => 0, 'is_featured' => false, 'features' => [['title' => ' Item ', 'text' => ' Descrição ']]];
        Livewire::test(CreateService::class)->fillForm($data)->call('create')->assertHasNoFormErrors();
        $service = Service::query()->where('slug', 'servico')->firstOrFail();
        $this->assertSame('Serviço', $service->title);
        $this->assertSame('Item', $service->features[0]['title']);
        $service->cover_image = 'existing.png';
        $service->save();
        Livewire::test(EditService::class, ['record' => $service->id])->fillForm(array_replace($data, ['title' => 'Alterado']))->call('save')->assertHasNoFormErrors();
        $this->assertSame('Alterado', $service->fresh()->title);
        $this->assertSame('existing.png', $service->fresh()->cover_image);
        Livewire::test(ListServices::class)->assertCanSeeTableRecords([$service]);
    }

    public function test_duplicate_slug_and_unknown_fields_are_rejected(): void
    {
        $service = new Service;
        $service->title = 'Original';
        $service->slug = 'original';
        $service->locale = 'pt_BR';
        $service->save();
        $data = ['title' => 'Teste', 'slug' => 'original', 'locale' => 'pt_BR', 'status' => 'draft', 'sort_order' => 0, 'is_featured' => false, 'features' => []];
        Livewire::test(CreateService::class)->fillForm($data)->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreateService::class)->fillForm(array_replace($data, ['locale' => 'en']))->call('create')->assertHasNoFormErrors();
        $second = Service::query()->where('locale', 'en')->firstOrFail();
        Livewire::test(EditService::class, ['record' => $second->id])->fillForm($data)->call('save')->assertHasFormErrors(['slug']);
        $this->assertSame('en', $second->fresh()->locale);
        Livewire::test(EditService::class, ['record' => $service->id])->fillForm($data)->set('data.features', ['bad' => ['title' => 'Test', 'icon' => 'x']])->call('save')->assertHasFormErrors(['features']);
        $this->assertNull($service->fresh()->features);
        Livewire::test(EditService::class, ['record' => $service->id])->fillForm($data)->set('data.body', '<script>bad</script>')->call('save')->assertHasFormErrors(['body']);
        $this->assertNull($service->fresh()->body);
        Livewire::test(EditService::class, ['record' => $service->id])->fillForm($data)->set('data.canonical_url', '/%2fexample.invalid')->call('save')->assertHasFormErrors(['canonical_url']);
        $this->assertNull($service->fresh()->canonical_url);
    }

    public function test_role_revoked_after_mount_cannot_create(): void
    {
        $component = Livewire::test(CreateService::class)->fillForm(['title' => 'No', 'slug' => 'no', 'locale' => 'pt_BR', 'status' => 'draft', 'sort_order' => 0, 'is_featured' => false, 'features' => []]);
        auth()->user()->role = null;
        auth()->user()->save();
        $component->call('create')->assertForbidden();
        $this->assertSame(0, Service::query()->count());
    }

    public function test_guests_and_users_without_role_are_denied(): void
    {
        auth()->user()->role = null;
        auth()->user()->save();
        Livewire::test(CreateService::class)->assertForbidden();
        auth()->logout();
        Livewire::test(CreateService::class)->assertForbidden();
    }

    public function test_minimal_create_and_invalid_features_update(): void
    {
        Livewire::test(CreateService::class)->fillForm(['title' => 'Minimal', 'slug' => 'minimal'])->call('create')->assertHasNoFormErrors();
        $service = Service::query()->where('slug', 'minimal')->firstOrFail();
        $create = Livewire::test(CreateService::class);
        $create->set('data.features', 'invalid scalar')->assertHasFormErrors(['features']);
        $this->assertIsArray($create->get('data.features'));
        $service->features = [['title' => 'Preservado', 'text' => null]];
        $service->save();
        $edit = Livewire::test(EditService::class, ['record' => $service->id]);
        $previous = $edit->get('data.features');
        $edit->set('data.features', 'invalid scalar')->assertHasFormErrors(['features']);
        $this->assertSame($previous, $edit->get('data.features'));
        $this->assertSame('Preservado', $service->fresh()->features[0]['title']);
        $empty = Livewire::test(CreateService::class);
        $empty->set('data.features', null)->assertHasNoFormErrors();
        $this->assertSame([], $empty->get('data.features'));
        $this->assertSame(1, Service::query()->count());
    }

    public function test_metadata_limits_and_plain_text_are_validated(): void
    {
        Livewire::test(CreateService::class)->fillForm(['title' => 'Serviço', 'slug' => 'servico-meta', 'meta_title' => str_repeat('a', 71)])->call('create')->assertHasFormErrors(['meta_title']);
        Livewire::test(CreateService::class)->fillForm(['title' => 'Serviço', 'slug' => 'servico-meta', 'meta_description' => str_repeat('a', 201)])->call('create')->assertHasFormErrors(['meta_description']);
        Livewire::test(CreateService::class)->fillForm(['title' => 'Serviço', 'slug' => 'servico-meta', 'meta_description' => "linha\nquebrada"])->call('create')->assertHasFormErrors(['meta_description']);
    }

    public function test_rejected_update_blocks_subsequent_save_until_corrected(): void
    {
        $create = Livewire::test(CreateService::class)->fillForm(['title' => 'New', 'slug' => 'new']);
        $create->set('data.features', 'invalid')->assertHasFormErrors(['features']);
        $create->call('create')->assertHasFormErrors(['features']);
        $this->assertSame(0, Service::query()->count());
        $create->set('data.features', [])->call('create')->assertHasNoFormErrors();
        $service = Service::query()->where('slug', 'new')->firstOrFail();
        $edit = Livewire::test(EditService::class, ['record' => $service->id])->set('data.title', 'Changed');
        $edit->set('data.features', 'invalid')->assertHasFormErrors(['features']);
        $edit->call('save')->assertHasFormErrors(['features']);
        $this->assertSame('New', $service->fresh()->title);
        $edit->set('data.features', [])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Changed', $service->fresh()->title);
    }

    public function test_rejection_marker_is_locked(): void
    {
        $component = Livewire::test(CreateService::class)->set('data.features', 'invalid');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('featuresRejected', false);
    }

    public function test_edit_rejection_marker_is_locked(): void
    {
        $service = new Service;
        $service->title = 'Test';
        $service->slug = 'test';
        $service->save();
        $component = Livewire::test(EditService::class, ['record' => $service->id])->set('data.features', 'invalid');
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('featuresRejected', false);
    }

    public function test_nested_edits_and_repeater_actions_recover_only_when_valid(): void
    {
        $create = Livewire::test(CreateService::class)->fillForm(['title' => 'New', 'slug' => 'new', 'features' => [['title' => 'Old', 'text' => null]]]);
        $id = array_key_first($create->get('data.features'));
        $create->set('data.features', 'bad')->assertHasFormErrors(['features']);
        $create->set("data.features.$id.title", '')->call('create')->assertHasFormErrors(['features']);
        $this->assertSame(0, Service::query()->count());
        $create->set("data.features.$id.title", 'Valid')->set("data.features.$id.text", 'Description')->call('create')->assertHasNoFormErrors();
        $service = Service::query()->where('slug', 'new')->firstOrFail();
        $this->assertSame('Valid', $service->features[0]['title']);

        $edit = Livewire::test(EditService::class, ['record' => $service->id]);
        $old = array_key_first($edit->get('data.features'));
        $edit->set('data.features', 'bad')->assertHasFormErrors(['features']);
        $edit->callFormComponentAction('features', 'add');
        $edit->call('save')->assertHasFormErrors(['features']);
        $this->assertSame('Valid', $service->fresh()->features[0]['title']);
        $ids = array_keys($edit->get('data.features'));
        $new = end($ids);
        $edit->set("data.features.$new.title", 'Second');
        $edit->callFormComponentAction('features', 'delete', arguments: ['item' => $old]);
        $edit->call('save')->assertHasNoFormErrors();
        $this->assertSame('Second', $service->fresh()->features[0]['title']);
    }

    public function test_create_actions_and_edit_nested_fields_recover(): void
    {
        $create = Livewire::test(CreateService::class)->fillForm(['title' => 'Action', 'slug' => 'action', 'features' => [['title' => 'Old', 'text' => null]]]);
        $old = array_key_first($create->get('data.features'));
        $create->set('data.features', 'bad')->callFormComponentAction('features', 'add');
        $create->call('create')->assertHasFormErrors(['features']);
        $ids = array_keys($create->get('data.features'));
        $new = end($ids);
        $create->set("data.features.$new.title", 'New');
        $create->callFormComponentAction('features', 'delete', arguments: ['item' => $old]);
        $create->call('create')->assertHasNoFormErrors();
        $service = Service::query()->where('slug', 'action')->firstOrFail();
        $this->assertSame('New', $service->features[0]['title']);

        $edit = Livewire::test(EditService::class, ['record' => $service->id]);
        $id = array_key_first($edit->get('data.features'));
        $edit->set('data.features', 'bad')->set("data.features.$id.title", '');
        $edit->call('save')->assertHasFormErrors(['features']);
        $edit->set("data.features.$id.title", 'Changed')->set("data.features.$id.text", 'Text');
        $edit->call('save')->assertHasNoFormErrors();
        $this->assertSame('Changed', $service->fresh()->features[0]['title']);
    }
}
