<table>
    <thead>
        <tr>
            <th width="25">Transactions</th>
            <th width="35">Avg Transactions USD</th>
            <th width="30">Avg Transactions CAD</th>
            <th width="20">Total Revenue CAD</th>
            <th width="20">Total Revenue USD</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>{{ $salesReport['transactions'] }}</td>
            <td>{{ $salesReport['avg_transaction_price_usd'] }}</td>            
            <td>{{ $salesReport['avg_transaction_price_cad'] }}</td>
            <td>{{ $salesReport['total_revenue_cad'] }}</td>
            <td>{{ $salesReport['total_revenue_usd'] }}</td>
        </tr> 
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th width="25">Top Resources</th>
            <th width="35">Reservations</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($salesReport['top_resources'] as $resource)
            <tr>
                <td>{{ $resource['name'] }}</td>
                <td>{{ $resource['events'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
