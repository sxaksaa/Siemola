<x-siemola-layout title="Tambah Loker" active-menu="Data Loker" user-role="Admin" sidebar-note="Admin menambahkan identitas locker dan menghubungkannya dengan device ID.">
    <section class="siemola-form-card">
        <div class="siemola-form-intro">
            <h2 class="siemola-form-title">Tambah Data Loker</h2>
            <p class="siemola-form-description">Lengkapi kode, nama, dan device ID loker.</p>
        </div>

        <form method="POST" action="{{ route('lockers.store') }}">
            @csrf
            @include('lockers._form', ['submitLabel' => 'Simpan Data'])
        </form>
    </section>
</x-siemola-layout>
