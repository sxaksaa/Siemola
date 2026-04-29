<x-siemola-layout title="Edit Mahasiswa" active-menu="Data Mahasiswa" user-role="Admin" sidebar-note="Perubahan UID RFID atau status mahasiswa akan memengaruhi izin akses locker saat kartu ditap.">
    <section class="siemola-form-card">
        <div class="siemola-form-intro">
            <h2 class="siemola-form-title">Edit Data Mahasiswa</h2>
            <p class="siemola-form-description">Perbarui identitas mahasiswa dan pastikan UID RFID tetap sesuai dengan kartu fisiknya.</p>
        </div>

        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf
            @method('PUT')
            @include('students._form', ['submitLabel' => 'Update Data'])
        </form>
    </section>
</x-siemola-layout>
