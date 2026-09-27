<table>
    <thead>
        <tr>
            <th style="background-color: #8EA9DB; font-weight: bold; text-align: center; border: 1px solid #000000;">LANTAI</th>
            @foreach($dateColumns as $dateKey => $label)
                <th style="background-color: #FFF200; font-weight: bold; text-align: center; border: 1px solid #000000;">{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($floors as $floorName)
            <tr>
                <td style="background-color: #BDD7EE; font-weight: bold; text-align: center; border: 1px solid #000000;">{{ $floorName }}</td>
                @foreach($dateColumns as $dateKey => $label)
                    <td style="text-align: center; border: 1px solid #000000;">
                        {{ $matrix[$floorName][$dateKey] ?? '-' }}
                    </td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td style="background-color: #8EA9DB; font-weight: bold; text-align: center; border: 1px solid #000000;">TOTAL</td>
            @foreach($dateColumns as $dateKey => $label)
                <td style="background-color: #E2E8F0; font-weight: bold; text-align: center; border: 1px solid #000000;">
                    {{ $dailyTotals[$dateKey] ?? '-' }}
                </td>
            @endforeach
        </tr>
    </tbody>
</table>
