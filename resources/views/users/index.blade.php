@extends('layout.app')

@section('title', 'Data Users - SaMaya')
@section('header_title', 'Kelola Data Users / Siswa')

@section('content')
<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    editUser: { id: '', nis: '', name: '', email: '', no_hp: '', kelas: '', jurusan: '', role: 'siswa' }
}" class="space-y-6">

    <!-- Top Action & Search/Filter Toolbar -->
    <div class="clean-card p-6 rounded-3xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Daftar Data Users / Siswa</h3>
                <p class="text-xs text-slate-500">Kelola informasi siswa, filter per kelas, jurusan, dan export data.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('users.export', request()->query()) }}" class="py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-file-excel text-sm text-emerald-600"></i>
                    <span>Unduh Excel</span>
                </a>

                <button @click="showCreateModal = true" class="py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-600/25 transition-all">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Tambah User Baru</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <form id="users-search-form" method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative lg:col-span-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="search-input-users" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan Nama / NIS..."
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    autocomplete="off">
            </div>

            <div>
                <select name="kelas" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="jurusan" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>

                @if(request()->anyFilled(['search', 'kelas', 'jurusan']))
                    <a href="{{ route('users.index') }}" title="Reset Filter" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div id="users-table-container" class="clean-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs border-collapse table-fixed">
                <colgroup>
                    <col style="width: 14%;">
                    <col style="width: 22%;">
                    <col style="width: 22%;">
                    <col style="width: 16%;">
                    <col style="width: 10%;">
                    <col style="width: 8%;">
                    <col style="width: 8%;">
                </colgroup>
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-6 align-middle text-left">NIS</th>
                        <th class="py-3.5 px-6 align-middle text-left">Nama Lengkap</th>
                        <th class="py-3.5 px-6 align-middle text-left">Email</th>
                        <th class="py-3.5 px-6 align-middle text-left">No. WhatsApp</th>
                        <th class="py-3.5 px-6 align-middle text-left">Kelas</th>
                        <th class="py-3.5 px-6 align-middle text-left">Jurusan</th>
                        <th class="py-3.5 px-6 align-middle text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-4 px-6 align-middle font-mono font-bold text-blue-700 min-w-0 truncate">
                                {{ $user->nis ?? '-' }}
                            </td>
                            <td class="py-4 px-6 align-middle font-bold text-slate-900 min-w-0 truncate" title="{{ $user->name }}">
                                {{ $user->name }}
                            </td>
                            <td class="py-4 px-6 align-middle text-slate-600 font-medium min-w-0 truncate" title="{{ $user->email }}">
                                {{ $user->email }}
                            </td>
                            <td class="py-4 px-6 align-middle min-w-0 truncate font-mono text-slate-700 font-semibold">
                                @if($user->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $user->no_hp) }}" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                        <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                        <span>{{ $user->no_hp }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 align-middle text-left whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg {{ $user->role === 'guru' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }} text-xs font-bold inline-block">
                                    {{ $user->kelas ?? '-' }} {{ $user->role === 'guru' ? '(Guru)' : '' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 align-middle text-left text-slate-700 font-semibold whitespace-nowrap">
                                {{ $user->jurusan ?? '-' }}
                            </td>
                            <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="
                                        editUser = {
                                            id: '{{ $user->id }}',
                                            nis: '{{ $user->nis }}',
                                            name: '{{ $user->name }}',
                                            email: '{{ $user->email }}',
                                            no_hp: '{{ $user->no_hp }}',
                                            kelas: '{{ $user->kelas }}',
                                            jurusan: '{{ $user->jurusan }}',
                                            role: '{{ $user->role ?? 'siswa' }}'
                                        };
                                        showEditModal = true;
                                    " class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    @if($user->role !== 'admin')
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                                Tidak ada data user yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-white flex items-center justify-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Datalist untuk Saran/Sugest Kelas (RPL 11 kelas, DKV 3 kelas per angkatan X, XI, XII, XIII) -->
    <datalist id="kelas-list">
        <!-- Angkatan X -->
        <option value="X RPL 1">
        <option value="X RPL 2">
        <option value="X RPL 3">
        <option value="X RPL 4">
        <option value="X RPL 5">
        <option value="X RPL 6">
        <option value="X RPL 7">
        <option value="X RPL 8">
        <option value="X RPL 9">
        <option value="X RPL 10">
        <option value="X RPL 11">
        <option value="X DKV 1">
        <option value="X DKV 2">
        <option value="X DKV 3">

        <!-- Angkatan XI -->
        <option value="XI RPL 1">
        <option value="XI RPL 2">
        <option value="XI RPL 3">
        <option value="XI RPL 4">
        <option value="XI RPL 5">
        <option value="XI RPL 6">
        <option value="XI RPL 7">
        <option value="XI RPL 8">
        <option value="XI RPL 9">
        <option value="XI RPL 10">
        <option value="XI RPL 11">
        <option value="XI DKV 1">
        <option value="XI DKV 2">
        <option value="XI DKV 3">

        <!-- Angkatan XII -->
        <option value="XII RPL 1">
        <option value="XII RPL 2">
        <option value="XII RPL 3">
        <option value="XII RPL 4">
        <option value="XII RPL 5">
        <option value="XII RPL 6">
        <option value="XII RPL 7">
        <option value="XII RPL 8">
        <option value="XII RPL 9">
        <option value="XII RPL 10">
        <option value="XII RPL 11">
        <option value="XII DKV 1">
        <option value="XII DKV 2">
        <option value="XII DKV 3">

        <!-- Angkatan XIII -->
        <option value="XIII RPL 1">
        <option value="XIII RPL 2">
        <option value="XIII RPL 3">
        <option value="XIII RPL 4">
        <option value="XIII RPL 5">
        <option value="XIII RPL 6">
        <option value="XIII RPL 7">
        <option value="XIII RPL 8">
        <option value="XIII RPL 9">
        <option value="XIII RPL 10">
        <option value="XIII RPL 11">
        <option value="XIII DKV 1">
        <option value="XIII DKV 2">
        <option value="XIII DKV 3">
    </datalist>

    <!-- Modal Tambah User -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base">Tambah User Baru</h3>
                <button @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIS</label>
                        <input type="text" name="nis" required placeholder="Contoh: 2024001" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama Siswa" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" required placeholder="siswa@sekolah.sch.id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                        <input type="text" name="no_hp" placeholder="Contoh: 08123456789" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jurusan</label>
                        <select name="jurusan" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="RPL">RPL</option>
                            <option value="DKV">DKV</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role / Peran</label>
                        <select name="role" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru (Wali Kelas)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelas (Diampu / Siswa)</label>
                        <input type="text" name="kelas" list="kelas-list" placeholder="Contoh: XI RPL 1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500" autocomplete="off">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20">
                        Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base">Edit Data User</h3>
                <button @click="showEditModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'/users/' + editUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIS</label>
                        <input type="text" name="nis" x-model="editUser.nis" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" x-model="editUser.name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" x-model="editUser.email" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                        <input type="text" name="no_hp" x-model="editUser.no_hp" placeholder="Contoh: 08123456789" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role / Peran</label>
                        <select name="role" x-model="editUser.role" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru (Wali Kelas)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelas</label>
                        <input type="text" name="kelas" list="kelas-list" x-model="editUser.kelas" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jurusan</label>
                        <select name="jurusan" x-model="editUser.jurusan" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="RPL">RPL</option>
                            <option value="DKV">DKV</option>
                        </select>
                    </div>
                </div>

                <!-- Field Reset Password Baru -->
                <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-2xl space-y-1.5">
                    <label class="block text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-key text-amber-600"></i> Reset Password Siswa (Opsional)
                    </label>
                    <input type="password" name="password" placeholder="Ketik password baru untuk mereset..." class="w-full px-4 py-2 bg-white border border-amber-300 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-amber-500">
                    <p class="text-[11px] text-amber-700">Kosongkan kolom ini jika tidak ingin mengubah password user/siswa.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20">
                        Perbarui User
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
(function () {
    const input  = document.getElementById('search-input-users');
    const form   = document.getElementById('users-search-form');
    const target = document.getElementById('users-table-container');
    if (!input || !form || !target) return;

    let timer;
    let controller; // AbortController to cancel in-flight requests

    // Show a subtle loading state on the table
    function setLoading(on) {
        target.style.opacity = on ? '0.5' : '1';
        target.style.pointerEvents = on ? 'none' : '';
        target.style.transition = 'opacity 0.15s';
    }

    async function doSearch() {
        if (controller) controller.abort();
        controller = new AbortController();

        const params = new URLSearchParams(new FormData(form));
        const url    = form.action + '?' + params.toString();

        setLoading(true);
        try {
            const res  = await fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await res.text();

            // Parse response & extract just the table container
            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const next = doc.getElementById('users-table-container');
            if (next) {
                target.innerHTML = next.innerHTML;
            }

            // Update URL without page reload
            history.pushState(null, '', url);
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        } finally {
            setLoading(false);
            input.focus();
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(doSearch, 380);
    });
})();
</script>
@endsection
