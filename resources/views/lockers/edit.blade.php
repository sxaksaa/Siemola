<x-siemola-layout title="Edit Loker" active-menu="Data Loker" user-role="Admin" sidebar-note="Admin dapat mengubah status, lokasi, dan device ID locker agar sinkron dengan kondisi perangkat nyata.">
    <section class="siemola-form-card">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-slate-950">Edit Data Loker</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">Perbarui detail locker sesuai kondisi operasional terbaru.</p>
        </div>

        <form method="POST" action="{{ route('lockers.update', $locker) }}">
            @csrf
            @method('PUT')
            @include('lockers._form', ['submitLabel' => 'Update Data'])
        </form>
    </section>
</x-siemola-layout>
