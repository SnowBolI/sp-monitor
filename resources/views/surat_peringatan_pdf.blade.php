<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
    body {
        font-family: sans-serif;
    }

    .header {
        display: flex;
        align-items: center; /* Align items vertically in the center */
        justify-content: center; /* Align the entire header content horizontally */
        position: relative; /* Allows absolute positioning for the title */
    }

    .header img {
        width: 100px;
        margin-right: 20px; /* Adds some space between the image and title */
    }

    .title {
        text-align: center; /* Center aligns both the title and date */
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .title h1 {
        margin: 0;
    }

    .title p {
        margin: 0;
        font-size: 12px; /* Adjust font size for the date */
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid black;
    }

    th, td {
        border: 0.5px solid black;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    @page {
        size: landscape;
    }
</style>

</head>
<body>
    <div class="header">
    <img src="{{ $logoSrc }}" alt="Logo Bank">
    <div class="title">
        <h1>{{ $title }}</h1>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>
    </div>
</div>

    <table>
        <thead>
            <tr>
                <th>Rekening</th>
                <th>Nama</th>
                <th>Progress</th>
                <th>Tingkat</th>
                <th>Dibuat</th>
                <th>Diserahkan</th>
                <th>Kembali</th>
                <th>Account Officer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suratPeringatans as $suratPeringatan)
                <tr>
                    <td>{{ $suratPeringatan->no }}</td>
                    <td>{{ $suratPeringatan->nasabah->nama }}</td>
                    <td>{{ $suratPeringatan->kategori }}</td>
                    <td>{{ $suratPeringatan->tingkat }}</td>
                    <td>{{ \Carbon\Carbon::parse($suratPeringatan->created_at)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($suratPeringatan->diserahkan)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($suratPeringatan->kembali)->format('d-m-Y') }}</td>
                    <td>{{ $suratPeringatan->accountOfficer->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
