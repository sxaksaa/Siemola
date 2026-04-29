<x-siemola-layout title="Edit Loker" active-menu="Data Loker" user-role="Admin" sidebar-note="Admin dapat mengubah device ID dan status locker agar sinkron dengan kondisi perangkat nyata.">
    <section class="siemola-form-card">
        <div class="siemola-form-intro">
            <h2 class="siemola-form-title">Edit Data Loker</h2>
            <p class="siemola-form-description">Perbarui detail locker sesuai kondisi operasional terbaru.</p>
        </div>

        <form method="POST" action="{{ route('lockers.update', $locker) }}">
            @csrf
            @method('PUT')
            @include('lockers._form', ['submitLabel' => 'Update Data'])
        </form>
    </section>
</x-siemola-layout>
