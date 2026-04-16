<x-siemola-layout title="Tambah Staff" active-menu="Data Staf" user-role="Admin" sidebar-note="Admin menambahkan akun staff yang bertugas memonitor dashboard dan mengelola data mahasiswa.">
    <section class="siemola-form-card">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-slate-950">Tambah Data Staff</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">Lengkapi identitas staff dan set password awal untuk akun login mereka.</p>
        </div>

        <form method="POST" action="{{ route('staff.store') }}">
            @csrf
            @include('staff._form', ['submitLabel' => 'Simpan Data', 'isEdit' => false])
        </form>
    </section>
</x-siemola-layout>
