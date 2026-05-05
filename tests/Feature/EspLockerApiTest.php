<?php

namespace Tests\Feature;

use App\Models\Borrowing;
use App\Models\Locker;
use App\Models\RfidCard;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspLockerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tap_without_taking_item_does_not_create_borrowing_and_can_tap_again(): void
    {
        $setup = $this->createLockerSetup();
        $payload = [
            'uid' => $setup['rfidCard']->uid,
            'device_id' => $setup['locker']->device_id,
        ];

        $this->postJson('/api/tab', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('action', 'borrow_authorized');

        $this->assertDatabaseCount('borrowings', 0);
        $this->assertDatabaseCount('locker_accesses', 1);

        $this->postJson('/api/tab', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('action', 'borrow_authorized');

        $this->assertDatabaseCount('borrowings', 0);
        $this->assertDatabaseCount('locker_accesses', 2);
    }

    public function test_borrowing_starts_after_item_is_taken_and_returns_after_item_is_put_back(): void
    {
        $setup = $this->createLockerSetup();
        $tapPayload = [
            'uid' => $setup['rfidCard']->uid,
            'device_id' => $setup['locker']->device_id,
        ];
        $statusPayload = ['device_id' => $setup['locker']->device_id];

        $this->postJson('/api/tab', $tapPayload)
            ->assertOk()
            ->assertJsonPath('action', 'borrow_authorized');

        $this->postJson('/api/getStatus', $statusPayload + ['locstatus' => 1])
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('locker_status', 'borrowed')
            ->assertJsonPath('has_active_borrowing', true)
            ->assertJsonPath('active_borrower', $setup['student']->name);

        $borrowing = Borrowing::query()->firstOrFail();

        $this->assertSame($setup['student']->id, $borrowing->student_id);
        $this->assertNull($borrowing->returned_at);

        $this->postJson('/api/tab', $tapPayload)
            ->assertOk()
            ->assertJsonPath('action', 'return_authorized');

        $this->postJson('/api/getStatus', $statusPayload + ['locstatus' => 0])
            ->assertOk()
            ->assertJsonPath('status', 0)
            ->assertJsonPath('locker_status', 'available')
            ->assertJsonPath('has_active_borrowing', false);

        $this->assertNotNull($borrowing->fresh()->returned_at);
        $this->assertSame('returned', $borrowing->fresh()->status);
    }

    /**
     * @return array{student: Student, rfidCard: RfidCard, locker: Locker}
     */
    private function createLockerSetup(): array
    {
        $student = Student::query()->create([
            'name' => 'Aksa Mahasiswa',
            'nim' => '2026042901',
            'rfid_uid' => '37 DB 7E 5',
            'study_program' => 'Teknik Informatika',
            'class_name' => 'TI-1A',
            'status' => 'active',
        ]);

        $rfidCard = RfidCard::query()->create([
            'uid' => '37 DB 7E 5',
            'user_id' => $student->id,
        ]);

        $locker = Locker::query()->create([
            'code' => 'L1',
            'name' => 'Loker 1',
            'device_id' => '14:08:08:A6:69:34',
            'status' => 'available',
            'switch_state' => 0,
        ]);

        return [
            'student' => $student,
            'rfidCard' => $rfidCard,
            'locker' => $locker,
        ];
    }
}
