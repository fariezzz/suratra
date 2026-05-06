<?php

namespace Tests\Unit;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\LetterArchive;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\User;
use Tests\TestCase;

class ModelTest extends TestCase
{
    public function test_user_role_helpers_and_resident_relation_work(): void
    {
        $resident = Resident::factory()->create();
        $user = User::factory()->linkedToResident($resident)->create([
            'role' => UserRole::WARGA->value,
        ]);

        $this->assertTrue($user->isWarga());
        $this->assertFalse($user->isRt());
        $this->assertSame($resident->id, $user->resident->id);
    }

    public function test_user_can_access_rt_logic_matches_expected_branching(): void
    {
        $rtUser = User::factory()->rt('001')->create();
        $rwUser = User::factory()->rw()->create();

        $this->assertTrue($rtUser->canAccessRt('001'));
        $this->assertFalse($rtUser->canAccessRt('002'));
        $this->assertTrue($rwUser->canAccessRt('999'));
    }

    public function test_resident_letter_request_and_archive_relations_work(): void
    {
        $resident = Resident::factory()->create();
        $letterRequest = LetterRequest::factory()->for($resident)->create([
            'letter_type' => LetterType::SKCK->value,
            'status' => LetterRequestStatus::COMPLETED->value,
        ]);
        $archive = LetterArchive::factory()->create([
            'resident_id' => $resident->id,
            'letter_request_id' => $letterRequest->id,
            'letter_type' => LetterType::SKCK->value,
            'request_status' => LetterRequestStatus::COMPLETED->value,
        ]);

        $this->assertSame($resident->id, $letterRequest->resident->id);
        $this->assertSame('Surat Pengantar SKCK', $letterRequest->letter_type_label);
        $this->assertSame('Selesai', $letterRequest->status_label);

        $this->assertSame($resident->id, $archive->resident->id);
        $this->assertSame('Surat Pengantar SKCK', $archive->letter_type_label);
        $this->assertTrue($letterRequest->documents !== []);
    }
}