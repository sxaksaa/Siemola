<x-siemola-layout title="Tambah Mahasiswa" active-menu="Data Mahasiswa" user-role="Admin" sidebar-note="Admin dapat menambahkan UID RFID mahasiswa agar perangkat locker bisa mengenali kartu yang ditap.">
    <section class="siemola-form-card">
        <div class="siemola-form-intro">
            <h2 class="siemola-form-title">Tambah Data Mahasiswa</h2>
            <p class="siemola-form-description">Lengkapi data identitas mahasiswa beserta UID RFID untuk akses smart locker.</p>
        </div>

        <form method="POST" action="{{ route('students.store') }}">
            @csrf
            @include('students._form', ['submitLabel' => 'Simpan Data'])
        </form>
    </section>
</x-siemola-layout>
