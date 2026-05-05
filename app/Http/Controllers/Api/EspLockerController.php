<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Locker;
use App\Models\LockerAccess;
use App\Models\RfidCard;
use App\Models\Student;
use App\Services\BorrowingStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EspLockerController extends Controller
{
    private const ACCESS_AUTHORIZATION_WINDOW_MINUTES = 2;

    public function tap(Request $request): JsonResponse
    {
        if ($response = $this->rejectInvalidToken($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'uid' => ['required', 'string', 'max:100'],
            'device_id' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->invalidPayload($validator->errors()->first());
        }

        $validated = $validator->validated();

        app(BorrowingStatusService::class)->syncLateStatuses();

        $uid = $this->normalizeRfidUid($validated['uid']);
        $deviceId = $this->normalizeDeviceId($validated['device_id']);
        $rfidCard = $this->findActiveRfidCardByUid($uid);
        $student = $rfidCard?->student;

        if (! $rfidCard || ! $student) {
            return $this->denied('Kartu RFID tidak terdaftar atau tidak aktif.');
        }

        $result = DB::transaction(function () use ($deviceId, $rfidCard, $student) {
            $locker = $this->findLockerByDeviceId($deviceId, true);

            if (! $locker) {
                return [
                    'ok' => false,
                    'message' => 'Device locker tidak terdaftar.',
                ];
            }

            $activeBorrowing = $this->activeBorrowingForLocker($locker, true);

            if ($locker->switch_state === null) {
                return $this->tapFromBorrowingState($student, $rfidCard, $locker, $activeBorrowing);
            }

            return $this->authorizeLockerAccess($student, $rfidCard, $locker, $activeBorrowing);
        });

        if (! $result['ok']) {
            return $this->denied($result['message']);
        }

        return response()->json([
            'status' => 'success',
            'nama' => $student->name,
            'action' => $result['action'],
            'locker' => $result['locker']->code,
            'locker_status' => $result['locker']->status,
            'locker_status_code' => $this->arduinoStatusCode($result['locker']->status),
            'switch_state' => $result['locker']->switch_state,
            'switch_label' => $this->switchStateLabel($result['locker']->switch_state),
            'message' => $result['message'],
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        if ($response = $this->rejectInvalidToken($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'device_id' => ['required', 'string', 'max:100'],
            'locstatus' => ['nullable', 'integer', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return $this->invalidPayload($validator->errors()->first(), 2);
        }

        $validated = $validator->validated();

        app(BorrowingStatusService::class)->syncLateStatuses();

        $result = DB::transaction(function () use ($validated) {
            $locker = $this->findLockerByDeviceId($this->normalizeDeviceId($validated['device_id']), true);

            if (! $locker) {
                return null;
            }

            $transitionMessage = null;

            if (array_key_exists('locstatus', $validated) && $validated['locstatus'] !== null) {
                $transitionMessage = $this->syncLockerSwitchState($locker, (int) $validated['locstatus']);
                $locker = $locker->fresh();
            }

            $activeBorrowing = $this->activeBorrowingForLocker($locker);

            return [
                'locker' => $locker,
                'active_borrowing' => $activeBorrowing,
                'message' => $transitionMessage,
            ];
        });

        if (! $result) {
            return response()->json([
                'status' => 2,
                'locker_status' => 'unknown',
                'message' => 'Device locker tidak terdaftar.',
            ], 404);
        }

        $locker = $result['locker'];
        $activeBorrowing = $result['active_borrowing'];

        return response()->json([
            'status' => $this->arduinoStatusCode($locker->status),
            'locker_status' => $locker->status,
            'locker' => $locker->code,
            'locstatus' => $locker->switch_state,
            'switch_state' => $locker->switch_state,
            'switch_label' => $this->switchStateLabel($locker->switch_state),
            'has_active_borrowing' => $activeBorrowing !== null,
            'active_borrower' => $activeBorrowing?->student?->name,
            'message' => $result['message'] ?? $this->lockerStatusMessage($locker),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        if ($response = $this->rejectInvalidToken($request)) {
            return $response;
        }

        $accesses = LockerAccess::query()
            ->with(['student', 'rfidCard', 'locker'])
            ->latest('accessed_at')
            ->get();

        return response()->json($accesses->map(function (LockerAccess $access): array {
            return [
                'user' => $access->student?->name,
                'nim' => $access->student?->nim,
                'kelas' => $access->student?->class_name,
                'prodi' => $access->student?->study_program,
                'locker' => $access->locker?->code,
                'accessed_at' => $access->accessed_at?->toDateTimeString(),
                'rfid_card_uid' => $access->rfidCard?->uid,
            ];
        }));
    }

    private function rejectInvalidToken(Request $request): ?JsonResponse
    {
        $configuredToken = trim((string) config('services.esp.token', ''));

        if ($configuredToken === '') {
            return null;
        }

        $givenToken = (string) $request->header('X-ESP-TOKEN', '');

        if ($givenToken === '') {
            $authorization = (string) $request->header('Authorization', '');

            if (str_starts_with($authorization, 'Bearer ')) {
                $givenToken = substr($authorization, 7);
            }
        }

        if (hash_equals($configuredToken, trim($givenToken))) {
            return null;
        }

        return response()->json([
            'status' => 'denied',
            'nama' => '',
            'message' => 'Token ESP tidak valid.',
        ], 401);
    }

    private function findActiveRfidCardByUid(string $uid): ?RfidCard
    {
        return RfidCard::query()
            ->with('student')
            ->get()
            ->first(function (RfidCard $rfidCard) use ($uid): bool {
                return $rfidCard->student?->status === 'active'
                    && $this->normalizeRfidUid($rfidCard->uid) === $uid;
            });
    }

    private function recordLockerAccess(Student $student, RfidCard $rfidCard, Locker $locker): void
    {
        LockerAccess::query()->create([
            'user_id' => $student->id,
            'rfid_card_id' => $rfidCard->id,
            'locker_id' => $locker->id,
            'accessed_at' => now(),
        ]);
    }

    private function activeBorrowingForLocker(Locker $locker, bool $lock = false): ?Borrowing
    {
        $query = Borrowing::query()
            ->with('student')
            ->where('locker_id', $locker->id)
            ->whereNull('returned_at')
            ->latest('borrowed_at');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function tapFromBorrowingState(
        Student $student,
        RfidCard $rfidCard,
        Locker $locker,
        ?Borrowing $activeBorrowing
    ): array {
        return $activeBorrowing
            ? $this->returnLocker($student, $rfidCard, $locker, $activeBorrowing)
            : $this->borrowLocker($student, $rfidCard, $locker, $activeBorrowing);
    }

    private function authorizeLockerAccess(
        Student $student,
        RfidCard $rfidCard,
        Locker $locker,
        ?Borrowing $activeBorrowing
    ): array {
        if ($activeBorrowing) {
            if ((int) $activeBorrowing->student_id !== (int) $student->id) {
                return [
                    'ok' => false,
                    'message' => 'Loker sedang dipakai mahasiswa lain.',
                ];
            }

            if ((int) $locker->switch_state === 0) {
                return $this->returnLocker($student, $rfidCard, $locker, $activeBorrowing);
            }

            $this->recordAuthorizedAccess($student, $rfidCard, $locker);

            return [
                'ok' => true,
                'action' => 'return_authorized',
                'locker' => $locker->fresh(),
                'message' => 'Akses pengembalian diberikan. Masukkan barang untuk menyelesaikan pengembalian.',
            ];
        }

        if ((int) $locker->switch_state !== 0) {
            return [
                'ok' => false,
                'message' => 'Loker kosong, tidak ada barang yang bisa dipinjam.',
            ];
        }

        $this->recordAuthorizedAccess($student, $rfidCard, $locker);

        return [
            'ok' => true,
            'action' => 'borrow_authorized',
            'locker' => $locker->fresh(),
            'message' => 'Akses loker diberikan. Peminjaman dicatat setelah barang diambil.',
        ];
    }

    private function borrowLocker(
        Student $student,
        RfidCard $rfidCard,
        Locker $locker,
        ?Borrowing $activeBorrowing
    ): array {
        if ($activeBorrowing) {
            return [
                'ok' => false,
                'message' => (int) $activeBorrowing->student_id === (int) $student->id
                    ? 'Mahasiswa ini masih tercatat meminjam loker.'
                    : 'Loker sedang dipakai mahasiswa lain.',
            ];
        }

        $this->recordLockerAccess($student, $rfidCard, $locker);

        Borrowing::query()->create([
            'student_id' => $student->id,
            'locker_id' => $locker->id,
            'borrowed_rfid_uid' => $rfidCard->uid,
            'borrowed_at' => now(),
            'due_at' => $this->nextDueAt(),
            'status' => 'borrowed',
        ]);

        $locker->update([
            'last_ping_at' => now(),
        ]);

        $student->update(['last_tapped_at' => now()]);

        return [
            'ok' => true,
            'action' => 'borrowed',
            'locker' => $locker->fresh(),
            'message' => 'Peminjaman berhasil.',
        ];
    }

    private function returnLocker(
        Student $student,
        RfidCard $rfidCard,
        Locker $locker,
        ?Borrowing $activeBorrowing
    ): array {
        if (! $activeBorrowing) {
            return [
                'ok' => false,
                'message' => 'Tidak ada peminjaman aktif untuk dikembalikan.',
            ];
        }

        if ((int) $activeBorrowing->student_id !== (int) $student->id) {
            return [
                'ok' => false,
                'message' => 'Loker sedang dipakai mahasiswa lain.',
            ];
        }

        $this->recordLockerAccess($student, $rfidCard, $locker);

        $activeBorrowing->update([
            'returned_at' => now(),
            'status' => 'returned',
        ]);

        $locker->update([
            'last_ping_at' => now(),
        ]);

        $student->update(['last_tapped_at' => now()]);

        return [
            'ok' => true,
            'action' => 'returned',
            'locker' => $locker->fresh(),
            'message' => 'Pengembalian berhasil.',
        ];
    }

    private function syncLockerSwitchState(Locker $locker, int $switchState): ?string
    {
        $previousSwitchState = $locker->switch_state;
        $activeBorrowing = $this->activeBorrowingForLocker($locker, true);
        $message = null;

        if ($switchState === 1 && $previousSwitchState !== 1 && ! $activeBorrowing) {
            $latestAccess = $this->latestAuthorizedAccessForLocker($locker);

            if ($latestAccess?->student && $latestAccess->rfidCard) {
                $activeBorrowing = Borrowing::query()->create([
                    'student_id' => $latestAccess->student->id,
                    'locker_id' => $locker->id,
                    'borrowed_rfid_uid' => $latestAccess->rfidCard->uid,
                    'borrowed_at' => now(),
                    'due_at' => $this->nextDueAt(),
                    'status' => 'borrowed',
                ]);

                $message = 'Peminjaman berhasil dicatat setelah barang diambil.';
            } else {
                $message = 'Barang terdeteksi diambil, tapi belum ada tap RFID valid.';
            }
        }

        if ($switchState === 0 && $previousSwitchState !== 0 && $activeBorrowing) {
            $activeBorrowing->update([
                'returned_at' => now(),
                'status' => 'returned',
            ]);

            $activeBorrowing = null;
            $message = 'Pengembalian berhasil dicatat setelah barang masuk.';
        }

        $locker->update([
            'last_ping_at' => now(),
            'switch_state' => $switchState,
            'switch_reported_at' => now(),
            'status' => $this->statusFromSwitchState($switchState, $activeBorrowing),
        ]);

        return $message;
    }

    private function latestAuthorizedAccessForLocker(Locker $locker): ?LockerAccess
    {
        return LockerAccess::query()
            ->with(['student', 'rfidCard'])
            ->where('locker_id', $locker->id)
            ->where('accessed_at', '>=', now()->subMinutes(self::ACCESS_AUTHORIZATION_WINDOW_MINUTES))
            ->latest('accessed_at')
            ->first();
    }

    private function findLockerByDeviceId(string $deviceId, bool $lock = false): ?Locker
    {
        $query = Locker::query()->orderByRaw('LENGTH(code), code');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query
            ->get()
            ->first(fn (Locker $locker): bool => $this->normalizeDeviceId((string) $locker->device_id) === $deviceId);
    }

    private function normalizeRfidUid(string $uid): string
    {
        $tokens = preg_split('/[^A-Fa-f0-9]+/', trim($uid), -1, PREG_SPLIT_NO_EMPTY);

        if (count($tokens) === 1 && strlen($tokens[0]) > 2 && strlen($tokens[0]) % 2 === 0) {
            $tokens = str_split($tokens[0], 2);
        }

        $tokens = array_map(function (string $token): string {
            return strtoupper(ltrim($token, '0')) ?: '0';
        }, $tokens ?: []);

        return implode(' ', $tokens);
    }

    private function normalizeDeviceId(string $deviceId): string
    {
        return strtoupper(trim($deviceId));
    }

    private function nextDueAt()
    {
        $timezone = config('app.display_timezone', 'Asia/Jakarta');
        $now = now($timezone);
        $dueAt = $now->copy()->setTime(17, 0);

        if ($dueAt->lessThanOrEqualTo($now)) {
            $dueAt->addDay();
        }

        return $dueAt->utc();
    }

    private function arduinoStatusCode(string $status): int
    {
        return $status === 'available' ? 0 : 1;
    }

    private function recordAuthorizedAccess(Student $student, RfidCard $rfidCard, Locker $locker): void
    {
        $this->recordLockerAccess($student, $rfidCard, $locker);

        $locker->update([
            'last_ping_at' => now(),
        ]);

        $student->update(['last_tapped_at' => now()]);
    }

    private function statusFromSwitchState(int $switchState, ?Borrowing $activeBorrowing = null): string
    {
        if ($switchState === 0) {
            return 'available';
        }

        return $activeBorrowing?->status === 'late' ? 'late' : 'borrowed';
    }

    private function lockerStatusMessage(Locker $locker): string
    {
        return match ($locker->status) {
            'borrowed' => 'Loker sedang dipinjam.',
            'late' => 'Peminjaman loker sudah telat.',
            default => 'Loker tersedia.',
        };
    }

    private function switchStateLabel(?int $switchState): string
    {
        return match ($switchState) {
            0 => 'Ada barang',
            1 => 'Kosong',
            default => 'Belum sinkron',
        };
    }

    private function denied(string $message): JsonResponse
    {
        return response()->json([
            'status' => 'denied',
            'nama' => '',
            'message' => $message,
        ], 403);
    }

    private function invalidPayload(string $message, string|int $status = 'denied'): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'nama' => '',
            'message' => $message,
        ], 422);
    }
}
