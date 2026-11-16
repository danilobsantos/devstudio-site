<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class LeadResourceTest extends TestCase
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
        $user->email = 'admin@example.invalid';
        $user->password = 'synthetic';
        $user->role = UserRole::Admin;
        $user->save();
        $this->actingAs($user);
    }

    private function lead(): Lead
    {
        $lead = new Lead;
        $lead->name = 'Synthetic';
        $lead->email = 'lead@example.invalid';
        $lead->message = 'Synthetic';
        $lead->solution_type = 'other';
        $lead->privacy_accepted_at = now();
        $lead->save();

        return $lead;
    }

    public function test_admin_lists_and_updates_only_status_and_notes(): void
    {
        $lead = $this->lead();
        Livewire::test(ListLeads::class)->assertCanSeeTableRecords([$lead]);
        Livewire::test(EditLead::class, ['record' => $lead->id])->fillForm(['status' => 'qualified', 'internal_notes' => ' Note '])->call('save')->assertHasNoFormErrors();
        $this->assertSame(LeadStatus::Qualified, $lead->fresh()->status);
        $this->assertSame('Note', $lead->fresh()->internal_notes);
        Livewire::test(EditLead::class, ['record' => $lead->id])->set('data.email', 'changed@example.invalid')->call('save')->assertHasFormErrors(['status']);
        $this->assertSame('lead@example.invalid', $lead->fresh()->email);
        Livewire::test(EditLead::class, ['record' => $lead->id])->set('data.status', 'invalid')->call('save')->assertHasFormErrors(['status']);
        $this->assertSame(LeadStatus::Qualified, $lead->fresh()->status);
        $this->assertFalse(Gate::forUser(auth()->user())->allows('delete', $lead));
        $this->assertFalse(Gate::forUser(auth()->user())->allows('create', Lead::class));
    }

    public function test_editor_guest_and_revocation_are_denied(): void
    {
        $lead = $this->lead();
        $edit = Livewire::test(EditLead::class, ['record' => $lead->id])->set('data.internal_notes', 'Denied');
        auth()->user()->role = UserRole::Editor;
        auth()->user()->save();
        $edit->call('save')->assertForbidden();
        Livewire::test(ListLeads::class)->assertForbidden();
        $this->assertNull($lead->fresh()->internal_notes);
        auth()->logout();
        Livewire::test(ListLeads::class)->assertForbidden();
    }
}
