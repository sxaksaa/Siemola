<x-siemola-layout title="Edit Mahasiswa" active-menu="Data Mahasiswa" user-role="Staff" sidebar-note="Perubahan UID RFID atau status mahasiswa akan memengaruhi izin akses locker saat kartu ditap.">
    <section class="siemola-form-card">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-slate-950">Edit Data Mahasiswa</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">Perbarui identitas mahasiswa dan pastikan UID RFID tetap sesuai dengan kartu fisiknya.</p>
        </div>

        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf
            @method('PUT')
            @include('students._form', ['submitLabel' => 'Update Data'])
        </form>
    </section>
</x-siemola-layout>
