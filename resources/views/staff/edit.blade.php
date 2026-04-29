<x-siemola-layout title="Edit Staff" active-menu="Data Staf" user-role="Admin" sidebar-note="Admin dapat memperbarui identitas, status, dan password akun staff dari halaman ini.">
    <section class="siemola-form-card">
        <div class="siemola-form-intro">
            <h2 class="siemola-form-title">Edit Data Staff</h2>
            <p class="siemola-form-description">Perbarui detail staff yang bertugas mengelola operasional smart locker.</p>
        </div>

        <form method="POST" action="{{ route('staff.update', $staff) }}">
            @csrf
            @method('PUT')
            @include('staff._form', ['submitLabel' => 'Update Data', 'isEdit' => true])
        </form>
    </section>
</x-siemola-layout>
