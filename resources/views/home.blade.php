<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Home</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
            </style>
        @endif
    </head>
    <body>
        <x-navbar />

        <div style="padding-top: 10px;">
            <input type="text" class="searchMain" placeholder="Cari...">
            <table width="95%" class="name">
                <tbody>
                    @foreach ($rekapGaji as $data)
                    <tr>
                        <td>{{ $data->nama }}</td>
                        <td>{{ $data->absen }}</td>
                        <td>{{ $data->absen_2 }}</td>
                        <td>Rp {{ number_format($data->gaji,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->absen * $data->gaji,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->bonbonan,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->bon,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->bon_lain,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->bonus,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->hasil_akhir,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </body>
</html>
