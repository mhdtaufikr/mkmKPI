<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Section Name</th>
            <th>Sub Section</th>
            <th>Shop Name</th>
            <th>Production Model Name</th>
            <th>Detail PIC</th>
            <th>Planning Manpower</th>
            <th>Actual Manpower</th>
            <th>Planning Production</th>
            <th>Actual Production</th>
            <th>Balance</th>
            <th>Downtime Cause Category</th>
            <th>Problem</th>
            <th>Action</th>
            <th>Time From</th>
            <th>Time To</th>
            <th>Not Good Model Name</th>
            <th>Quantity</th>
            <th>Repair</th>
            <th>Reject</th>
            <th>Total</th>
            <th>Not Good Remark</th>
            <th>Created At</th>
            <th>Updated At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($checksheetData as $data)
            <tr>
                <td>{{ $data->date }}</td>
                <td>{{ $data->shift }}</td>
                <td>{{ $data->section_name }}</td>
                <td>{{ $data->sub_section }}</td>
                <td>{{ $data->shop_name }}</td>
                <td>{{ $data->production_model_name }}</td>
                <td>{{ $data->detail_pic }}</td>
                <td>{{ $data->planning_manpower }}</td>
                <td>{{ $data->actual_manpower }}</td>
                <td>{{ $data->planning_production }}</td>
                <td>{{ $data->actual_production }}</td>
                <td>{{ $data->balance }}</td>
                <td>{{ $data->downtime_cause_category }}</td>
                <td>{{ $data->problem }}</td>
                <td>{{ $data->action }}</td>
                <td>{{ $data->time_from }}</td>
                <td>{{ $data->time_to }}</td>
                <td>{{ $data->not_good_model_name }}</td>
                <td>{{ $data->quantity }}</td>
                <td>{{ $data->repair }}</td>
                <td>{{ $data->reject }}</td>
                <td>{{ $data->total }}</td>
                <td>{{ $data->not_good_remark }}</td>
                <td>{{ $data->created_at }}</td>
                <td>{{ $data->updated_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
