@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Tracking Order #{{ $order->id }}</h1>

    @if($status)
        <table class="w-full border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2">Status</th>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($status as $s)
                <tr>
                    <td class="p-2">{{ $s['status'] ?? '' }}</td>
                    <td class="p-2">{{ $s['date'] ?? '' }}</td>
                    <td class="p-2">{{ $s['desc'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Status pengiriman belum tersedia.</p>
    @endif
</div>
@endsection
