@extends('layouts.master')

@section('content')

<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
            </div>
        </div>
    </header>
    <!-- Main page content-->
    <div class="container-fluid px-4 mt-n10">
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
            </section>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <form action="{{ url('/checksheet/detail/store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">Checksheet Form: {{ $item->document_no }}</h3>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                    <div class="card-body">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                                            @foreach($formatted_data as $key => $data)
                                                @if ($loop->first || $data['shop_name'] !== $formatted_data[$key - 1]['shop_name'])
                                                    <li class="nav-item">
                                                        <a style="color: black;" class="nav-link {{ $loop->first ? 'active' : '' }}" id="nav-{{$key}}-tab" data-bs-toggle="tab" href="#nav-{{$key}}" role="tab" aria-controls="nav-{{$key}}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $data['shop_name'] }}</a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            <input type="text" name="id" value="{{ $id }}" hidden>
                                            @foreach($formatted_data as $key => $data)
                                                @if ($loop->first || $data['shop_name'] !== $formatted_data[$key - 1]['shop_name'])
                                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="nav-{{$key}}" role="tabpanel" aria-labelledby="nav-{{$key}}-tab">
                                                        <input type="hidden" name="shop[]" value="{{ $data['shop_name'] }}">
                                                        <div class="form-group mt-4">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <label for="man_power_planning">Man Power Planning</label>
                                                                    <input type="number" name="man_power_planning[{{ $data['shop_name'] }}][]" class="form-control form-control-sm" style="width: 100px;" value="0" min="0">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="man_power_actual">Man Power Actual</label>
                                                                    <input type="number" name="man_power_actual[{{ $data['shop_name'] }}][]" class="form-control form-control-sm" style="width: 100px;" value="0" min="0">
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <label for="pic">PIC</label>
                                                                    <input type="text" name="pic[{{ $data['shop_name'] }}][]" class="form-control form-control-sm">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="mb-4 mt-4">
                                                                    <table class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width: 120px;">Model</th>
                                                                                <th style="width: 280px;">Production</th>
                                                                                <th colspan="3">Downtime</th>
                                                                                <th>NG</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($formatted_data as $model)
                                                                                @if ($model['shop_name'] === $data['shop_name'])
                                                                                    <tr>
                                                                                        <input type="hidden" name="model[]" value="{{ $model['model_name'] }}">
                                                                                        <input type="hidden" name="shopAll[]" value="{{ $model['shop_name'] }}">
                                                                                        <td class="text-center">{{ $model['model_name'] }}
                                                                                            <input type="file" name="picture_ng[{{ $model['model_name'] }}][]" class="form-control form-control-sm">
                                                                                        </td>
                                                                                        <td>
                                                                                            <div style="width: 270px;" class="row">
                                                                                                <div class="col-md-4">
                                                                                                    <label>Planning</label>
                                                                                                    <input type="number" name="production_planning[{{ $model['model_name'] }}][]" class="form-control form-control-sm production-planning" style="width: 80px;" value="0" min="0">
                                                                                                </div>
                                                                                                <div class="col-md-4">
                                                                                                    <label>Actual</label>
                                                                                                    <input type="number" name="production_actual[{{ $model['model_name'] }}][]" class="form-control form-control-sm production-actual" style="width: 80px;" value="0" min="0">
                                                                                                </div>
                                                                                                <div class="col-md-4">
                                                                                                    <label>Different</label>
                                                                                                    <input type="number" name="production_different[{{ $model['model_name'] }}][]" class="form-control form-control-sm production-different" style="width: 80px;" readonly value="0">
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td colspan="3">
                                                                                            <div class="downtime-container">
                                                                                                <div class="row">
                                                                                                    <div class="col-md-6">
                                                                                                        <label>Downtime</label><br>
                                                                                                        <select name="downtime_category[{{ $model['model_name'] }}][]" class="form-control form-control-sm " >
                                                                                                            <option value="">Select Downtime</option>
                                                                                                            @foreach($downtimeCategory as $category)
                                                                                                                <option value="{{ $category->id }}">{{ $category->category }}</option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                        <div class="row">
                                                                                                            <div class="col-md-6">
                                                                                                                <label>From</label>
                                                                                                                <input type="time" name="time_from[{{ $model['model_name'] }}][]" class="form-control form-control-sm">
                                                                                                            </div>
                                                                                                            <div class="col-md-6">
                                                                                                                <label>Until</label>
                                                                                                                <input type="time" name="time_until[{{ $model['model_name'] }}][]" class="form-control form-control-sm">
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-6">
                                                                                                        <div class="row">
                                                                                                            <div class="col-md-5">
                                                                                                                <label>Cause</label>
                                                                                                                <textarea name="cause[{{ $model['model_name'] }}][]" class="form-control form-control-sm" rows="3"></textarea>
                                                                                                            </div>
                                                                                                            <div class="col-md-5">
                                                                                                                <label>Action</label>
                                                                                                                <textarea name="action[{{ $model['model_name'] }}][]" class="form-control form-control-sm" rows="3"></textarea>
                                                                                                            </div>
                                                                                                            <div class="col-md-2">
                                                                                                                <br>
                                                                                                                <button type="button" class="btn btn-sm btn-primary add-problem-row mt-2" data-model="{{ $model['model_name'] }}">+</button> <br>

                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td>
                                                                                            <label>Repair</label>
                                                                                            <input type="number" name="repair[{{ $model['model_name'] }}][]" class="form-control form-control-sm production-planning" style="width: 80px;" value="0" min="0">
                                                                                            <label>Reject</label>
                                                                                            <input type="number" name="reject[{{ $model['model_name'] }}][]" class="form-control form-control-sm production-planning" style="width: 80px;" value="0" min="0">
                                                                                        </td>
                                                                                    </tr>
                                                                                @endif
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Function to add new problem row
        $(document).on('click', '.add-problem-row', function() {
            var model = $(this).data('model');
            var newRow = `
                <div class="row mt-3 problem-row">
                    <div class="col-md-6">
                        <label>Downtime</label><br>
                        <select name="downtime_category[${model}][]" class="form-control form-control-sm">
                            <option value="">Select Downtime</option>
                            @foreach($downtimeCategory as $category)
                                <option value="{{ $category->id }}">{{ $category->category }}</option>
                            @endforeach
                        </select>
                        <div class="row">
                            <div class="col-md-6">
                                <label>From</label>
                                <input type="time" name="time_from[${model}][]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label>Until</label>
                                <input type="time" name="time_until[${model}][]" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-5">
                                <label>Cause</label>
                                <textarea name="cause[${model}][]" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <div class="col-md-5">
                                <label>Action</label>
                                <textarea name="action[${model}][]" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <div class="col-md-2">
                                <br>

                                <button type="button" class="btn btn-sm btn-danger remove-problem-row mt-2">-</button>
                            </div>
                        </div>
                    </div>
                </div>`;
            $(this).closest('.downtime-container').append(newRow);
        });

        // Function to remove problem row
        $(document).on('click', '.remove-problem-row', function() {
            $(this).closest('.problem-row').remove();
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('.chosen-select').chosen({width:"600px"});

        // Add change event listener to the select dropdown
        $('.chosen-select').change(function() {
            // Iterate over each selected option
            $(this).find('option:selected').each(function() {
                // Check if the value is null
                if ($(this).val() === null) {
                    // Set the value to 0
                    $(this).val('0');
                }
            });
        });

        // Submit the form
        $('form').submit(function() {
            // Trigger change event on the select dropdown to ensure all selected options are processed
            $('.chosen-select').trigger('change');
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Select input fields within each table row
        $('.production-planning').on('input', function() {
            calculateDifferent($(this).closest('tr'));
        });

        $('.production-actual').on('input', function() {
            calculateDifferent($(this).closest('tr'));
        });

        function calculateDifferent(row) {
            var planningInput = row.find('.production-planning');
            var actualInput = row.find('.production-actual');
            var differentInput = row.find('.production-different');

            var planningValue = parseFloat(planningInput.val());
            var actualValue = parseFloat(actualInput.val());

            if (!isNaN(planningValue) && !isNaN(actualValue)) {
                var difference = actualValue - planningValue;
                differentInput.val(difference);
            }
        }
    });
</script>
<!-- For Datatables -->
<script>
    $(document).ready(function() {
        var table = $("#tableUser").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        });
    });
</script>
@endsection
