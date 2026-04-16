<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="siemola-label">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $staff->name) }}" class="siemola-input" required>
        @error('name')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nik_nip" class="siemola-label">NIK/NIP</label>
        <input id="nik_nip" name="nik_nip" type="text" value="{{ old('nik_nip', $staff->nik_nip) }}" class="siemola-input" required>
        @error('nik_nip')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="siemola-label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $staff->email) }}" class="siemola-input" required>
        @error('email')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="siemola-label">No. HP</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $staff->phone) }}" class="siemola-input">
        @error('phone')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="siemola-label">Status</label>
        <select id="status" name="status" class="siemola-input" required>
            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $staff->status ?: 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="siemola-label">{{ $isEdit ? 'Password Baru' : 'Password' }}</label>
        <input id="password" name="password" type="password" class="siemola-input" {{ $isEdit ? '' : 'required' }}>
        @error('password')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="siemola-label">Konfirmasi Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="siemola-input" {{ $isEdit ? '' : 'required' }}>
    </div>
</div>

@if ($isEdit)
    <p class="mt-4 text-sm text-slate-400">Kosongkan password jika tidak ingin mengubah password staff.</p>
@endif

<div class="mt-8 flex flex-col gap-3 sm:flex-row">
    <button type="submit" class="siemola-primary-button">{{ $submitLabel }}</button>
    <a href="{{ route('staff.index') }}" class="siemola-secondary-button">Batal</a>
</div>
