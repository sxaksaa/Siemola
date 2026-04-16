<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="code" class="siemola-label">Kode Loker</label>
        <input id="code" name="code" type="text" value="{{ old('code', $locker->code) }}" class="siemola-input" required>
        @error('code')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="siemola-label">Nama Loker</label>
        <input id="name" name="name" type="text" value="{{ old('name', $locker->name) }}" class="siemola-input" required>
        @error('name')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="location" class="siemola-label">Lokasi</label>
        <input id="location" name="location" type="text" value="{{ old('location', $locker->location) }}" class="siemola-input">
        @error('location')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="device_id" class="siemola-label">Device ID</label>
        <input id="device_id" name="device_id" type="text" value="{{ old('device_id', $locker->device_id) }}" class="siemola-input">
        @error('device_id')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="siemola-label">Status</label>
        <select id="status" name="status" class="siemola-input" required>
            @foreach ([
                'available' => 'Available',
                'borrowed' => 'Borrowed',
                'late' => 'Late',
                'maintenance' => 'Maintenance',
                'offline' => 'Offline',
            ] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $locker->status ?: 'available') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="last_ping_at" class="siemola-label">Last Ping</label>
        <input id="last_ping_at" name="last_ping_at" type="datetime-local" value="{{ old('last_ping_at', $locker->last_ping_at?->format('Y-m-d\\TH:i')) }}" class="siemola-input">
        @error('last_ping_at')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex flex-col gap-3 sm:flex-row">
    <button type="submit" class="siemola-primary-button">{{ $submitLabel }}</button>
    <a href="{{ route('lockers.index') }}" class="siemola-secondary-button">Batal</a>
</div>
