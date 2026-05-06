<?php

namespace Tests\Feature;

use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\User;
use Tests\TestCase;

class DashboardAndResidentTest extends TestCase
{
    public function test_warga_dashboard_shows_personal_context(): void
    {
        $resident = Resident::factory()->create();
        LetterRequest::factory()->for($resident)->count(3)->create();
        $user = User::factory()->linkedToResident($resident)->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertViewHas('isWarga', true)
            ->assertViewHas('residentProfile', fn ($profile) => $profile->id === $resident->id);
    }

    public function test_rt_dashboard_and_rt_overview_are_limited_to_managed_rt(): void
    {
        Resident::factory()->rt('001')->wargaAsli()->count(2)->create();
        Resident::factory()->rt('002')->pendatang()->create();

        $user = User::factory()->rt('001')->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertViewHas('isRt', true)
            ->assertViewHas('selectedRt', '001');

        $this->actingAs($user)
            ->get('/warga/rt-overview')
            ->assertOk()
            ->assertViewHas('isRt', true)
            ->assertViewHas('managedRt', '001');
    }

    public function test_rt_can_view_own_rt_residents_but_not_other_rts(): void
    {
        Resident::factory()->rt('001')->count(2)->create();
        Resident::factory()->rt('002')->create();

        $user = User::factory()->rt('001')->create();

        $this->actingAs($user)
            ->get('/warga/001')
            ->assertOk()
            ->assertViewIs('residents.rt-residents');

        $this->actingAs($user)
            ->get('/warga/002')
            ->assertForbidden();
    }
}