@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Log Audit Sistem</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar rekam log nasional untuk mendeteksi perubahan data dan aktivitas penting.</p>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Aktor (Pengguna)</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Model / ID</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">IP / User Agent</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if($logs->isEmpty())
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Belum ada log aktivitas tercatat di sistem.
                            </td>
                        </tr>
                    @else
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/40 transition-colors duration-150">
                                <td class="px-6 py-4 text-xs text-slate-500 font-mono">
                                    {{ $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->user)
                                        <div class="font-bold text-slate-800 text-xs">{{ $log->user->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-slate-400 text-xs">Sistem / Pengunjung</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                        @if(str_contains($log->action, 'CREATE')) bg-emerald-50 text-emerald-700 border border-emerald-100
                                        @elseif(str_contains($log->action, 'UPDATE')) bg-blue-50 text-blue-700 border border-blue-100
                                        @elseif(str_contains($log->action, 'DELETE') || str_contains($log->action, 'ARCHIVE')) bg-rose-50 text-rose-700 border border-rose-100
                                        @else bg-slate-100 text-slate-700 border border-slate-200/50
                                        @endif">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600">
                                    @if($log->model_type)
                                        <div class="font-semibold text-slate-700">{{ class_basename($log->model_type) }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $log->model_id }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-[11px] text-slate-500 font-mono">
                                    <div>IP: {{ $log->ip_address }}</div>
                                    <div class="truncate max-w-[180px] mt-0.5" title="{{ $log->user_agent }}">{{ $log->user_agent }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->before || $log->after)
                                        <button onclick="toggleDetails('{{ $log->id }}')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer">
                                            Lihat JSON
                                        </button>
                                        <div id="details-{{ $log->id }}" class="hidden mt-3 p-3 bg-slate-900 text-emerald-400 rounded-xl font-mono text-[10px] max-w-sm overflow-x-auto leading-relaxed shadow-inner">
                                            @if($log->before)
                                                <div class="mb-2">
                                                    <span class="text-slate-400 font-bold block mb-1 uppercase tracking-wide text-[9px]">- SEBELUM:</span>
                                                    <pre class="overflow-x-auto">{{ json_encode($log->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif
                                            @if($log->after)
                                                <div>
                                                    <span class="text-slate-400 font-bold block mb-1 uppercase tracking-wide text-[9px]">+ SESUDAH:</span>
                                                    <pre class="overflow-x-auto">{{ json_encode($log->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleDetails(id) {
        const el = document.getElementById('details-' + id);
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
</script>
@endsection
