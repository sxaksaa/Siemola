<x-siemola-layout title="Tambah Staff" active-menu="Data Staf" user-role="Admin" sidebar-note="Admin menambahkan akun staff yang bertugas memonitor dashboard operasional.">
    <section class="siemola-form-card">
        <div class="siemola-form-intro">
            <h2 class="siemola-form-title">Tambah Data Staff</h2>
            <p class="siemola-form-description">Lengkapi identitas staff dan set password awal untuk akun login mereka.</p>
        </div>

        <form method="POST" action="{{ route('staff.store') }}">
            @csrf
            @include('staff._form', ['submitLabel' => 'Simpan Data', 'isEdit' => false])
        </form>
    </section>
</x-siemola-layout>
