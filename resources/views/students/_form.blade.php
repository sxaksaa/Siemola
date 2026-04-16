<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="siemola-label">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $student->name) }}" class="siemola-input" required>
        @error('name')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nim" class="siemola-label">NIM</label>
        <input id="nim" name="nim" type="text" value="{{ old('nim', $student->nim) }}" class="siemola-input" required>
        @error('nim')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="rfid_uid" class="siemola-label">UID RFID</label>
        <input id="rfid_uid" name="rfid_uid" type="text" value="{{ old('rfid_uid', $student->rfid_uid) }}" class="siemola-input" required>
        @error('rfid_uid')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="study_program" class="siemola-label">Program Studi</label>
        <select id="study_program" name="study_program" class="siemola-input" required>
            @foreach ([
                'Manajemen Perhotelan',
                'Keuangan dan Perbankan',
                'Administrasi Bisnis',
                'Desain Grafis',
                'Teknologi Informasi',
            ] as $studyProgram)
                <option value="{{ $studyProgram }}" @selected(old('study_program', $student->study_program) === $studyProgram)>{{ $studyProgram }}</option>
            @endforeach
        </select>
        @error('study_program')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="class_name" class="siemola-label">Kelas</label>
        <input id="class_name" name="class_name" type="text" value="{{ old('class_name', $student->class_name) }}" class="siemola-input" required>
        @error('class_name')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="siemola-label">No. HP</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $student->phone) }}" class="siemola-input">
        @error('phone')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="siemola-label">Status</label>
        <select id="status" name="status" class="siemola-input" required>
            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $student->status ?: 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="siemola-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex flex-col gap-3 sm:flex-row">
    <button type="submit" class="siemola-primary-button">{{ $submitLabel }}</button>
    <a href="{{ route('students.index') }}" class="siemola-secondary-button">Batal</a>
</div>
