<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Residents Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Keep font small to fit data */
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f4f4f5;
            font-weight: bold;
        }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <h2>Barangay Residents Record</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name (Last, First, Middle)</th>
                <th>Birthdate</th>
                <th>Sex</th>
                <th>Status</th>
                <th>Phone</th>
                <th>Sectors</th>
            </tr>
        </thead>
        <tbody>
            @foreach($residents as $resident)
                <tr>
                    <td class="text-center">{{ $resident->id }}</td>
                    <td>{{ $resident->lname }}, {{ $resident->fname }} {{ $resident->mname }} {{ $resident->suffix }}</td>
                    <td>{{ $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ ucfirst($resident->sex) }}</td>
                    <td>{{ ucfirst($resident->status) }}</td>
                    <td>{{ $resident->phone_number ?? 'N/A' }}</td>
                    <td>
                        @php
                            $sectors = [];
                            if($resident->senior_citizen) $sectors[] = 'Senior';
                            if($resident->is_pwd) $sectors[] = 'PWD';
                            if($resident->solo_parent) $sectors[] = 'Solo Parent';
                            if($resident->voter) $sectors[] = 'Voter';
                        @endphp
                        {{ implode(', ', $sectors) ?: 'None' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>