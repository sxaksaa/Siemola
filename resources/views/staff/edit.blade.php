<x-siemola-layout title="Edit Staff" active-menu="Data Staf" user-role="Admin" sidebar-note="Admin dapat memperbarui identitas, status, dan password akun staff dari halaman ini.">
    <section class="siemola-form-card">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-slate-950">Edit Data Staff</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">Perbarui detail staff yang bertugas mengelola operasional smart locker.</p>
        </div>

        <form method="POST" action="{{ route('staff.update', $staff) }}">
            @csrf
            @method('PUT')
            @include('staff._form', ['submitLabel' => 'Update Data', 'isEdit' => true])
        </form>
    </section>
</x-siemola-layout>
