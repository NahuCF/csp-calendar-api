<table>
    <thead>
        <tr>
            <th width="25">Facility</th>
            <th width="25">Name</th>
            <th width="30">Resource</th>
            <th width="20">Price</th>
            <th width="20">Date</th>
            <th width="10">Is Paid</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($reservationsByFacility as $facilityName => $reservations)
        <tr>
            <td colspan="6" style="font-weight: bold;">{{ $facilityName }}</td>
        </tr>
        @foreach ($reservations as $reservation)
            <tr>
                <td>
                <td style="text-align: left">{{ $reservation['name'] }}</td>
                <td>{{ $reservation['resource_name'] }}</td>
                <td>{{ $reservation['currency'] }} {{ $reservation['price']}}</td>
                <td>{{ $reservation['date'] }}</td>
                <td bgcolor="{{ $reservation['is_paid'] ? '#C6F4D6' : '#FFC6C6' }}" >{{ $reservation['is_paid'] ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th width="20" style="font-weight: bold;">Currency</th>
            <th width="20" style="font-weight: bold;">Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($reservationsByFacility as $facilityName => $reservations)
            @foreach ($reservations->groupBy('currency') as $currency => $reservationsByCurrency)
                <tr>
                    <td>{{ $currency }}</td>
                    <td>{{ round($reservationsByCurrency->sum('price'), 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>