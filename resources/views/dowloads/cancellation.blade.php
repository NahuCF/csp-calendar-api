<table>
    <thead>
        <tr>
            <th width="25">Facility</th>
            <th width="25">Name</th>
            <th width="30">Resource</th>
            <th width="20">Date</th>
            <th width="30">Reason</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($cancellationsByFacility as $facilityName => $reservations)
        <tr>
            <td colspan="6" style="font-weight: bold;">{{ $facilityName }}</td>
        </tr>
        @foreach ($reservations as $reservation)
            <tr>
                <td>
                <td style="text-align: left">{{ $reservation['name'] }}</td>
                <td>{{ $reservation['resource_name'] }}</td>
                <td>{{ $reservation['date'] }}</td>
                <td>{{ $reservation['reason'] }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
