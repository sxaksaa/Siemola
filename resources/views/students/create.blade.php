<x-siemola-layout title="Tambah Mahasiswa" active-menu="Data Mahasiswa" user-role="Admin" sidebar-note="Admin dapat menambahkan UID RFID mahasiswa agar perangkat locker bisa mengenali kartu yang ditap.">
    <section class="siemola-form-card">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-slate-950">Tambah Data Mahasiswa</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">Lengkapi data identitas mahasiswa beserta UID RFID untuk akses smart locker.</p>
        </div>

        <form method="POST" action="{{ route('students.store') }}">
            @csrf
            @include('students._form', ['submitLabel' => 'Simpan Data'])
        </form>
    </section>
</x-siemola-layout>
