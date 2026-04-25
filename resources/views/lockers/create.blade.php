<x-siemola-layout title="Tambah Loker" active-menu="Data Loker" user-role="Admin" sidebar-note="Admin menambahkan identitas locker dan menghubungkannya dengan device ID.">
    <section class="siemola-form-card">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-slate-950">Tambah Data Loker</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">Lengkapi kode, nama, device ID, dan status loker.</p>
        </div>

        <form method="POST" action="{{ route('lockers.store') }}">
            @csrf
            @include('lockers._form', ['submitLabel' => 'Simpan Data'])
        </form>
    </section>
</x-siemola-layout>
