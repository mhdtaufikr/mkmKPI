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
                                        <h3 class="card-title">Checksheet Form: {{ $header->document_no }}</h3>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                    <div class="card-body">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                                            @foreach($formattedData as $key => $data)
                                                <li class="nav-item">
                                                    <a style="color: black;" class="nav-link {{ $loop->first ? 'active' : '' }}" id="nav-{{$key}}-tab" data-bs-toggle="tab" href="#nav-{{$key}}" role="tab" aria-controls="nav-{{$key}}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $data['shop_name'] }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            <input readonly type="text" name="id" value="{{ $id }}" hidden>
                                            @foreach($formattedData as $key => $data)
                                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="nav-{{$key}}" role="tabpanel" aria-labelledby="nav-{{$key}}-tab">
                                                    <input readonly type="hidden" name="shop[]" value="{{ $data['shop_name'] }}">
                                                    <div class="form-group mt-4">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label for="man_power_planning">Man Power Planning</label>
                                                                <input readonly type="number" name="man_power_planning[{{ $data['shop_name'] }}][]" class="form-control form-control-sm" style="width: 100px;" value="{{ $data['planning_manpower'] }}" min="0">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label for="man_power_actual">Man Power Actual</label>
                                                                <input readonly type="number" name="man_power_actual[{{ $data['shop_name'] }}][]" class="form-control form-control-sm" style="width: 100px;" value="{{ $data['actual_manpower'] }}" min="0">
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label for="pic">PIC</label>
                                                                <input readonly type="text" name="pic[{{ $data['shop_name'] }}][]" class="form-control form-control-sm" value="{{ $data['pic'] }}">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-4 mt-4">
                                                                <table class="table table-bordered table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="width: 120px;" >Model</th>
                                                                            <th style="width: 280px;" >Production</th>
                                                                            <th colspan="3">Downtime</th>
                                                                            <th>NG</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($data['models'] as $model)
                                                                            <tr>
                                                                                <input readonly type="hidden" name="model[]" value="{{ $model['model_id'] }}">
                                                                                <input readonly type="hidden" name="shopAll[]" value="{{ $data['shop_name'] }}">
                                                                                <td class="text-center">{{ DB::table('mst_models')->where('id', $model['model_id'])->value('model_name') }}
                                                                                    <input readonly type="file" name="picture_ng[{{ $model['model_id'] }}][]"  class="form-control form-control-sm">
                                                                                </td>
                                                                                <td>
                                                                                    <div style="width: 270px;" class="row">
                                                                                        <div class="col-md-4">
                                                                                            <label>Planning</label>
                                                                                            <input readonly readonly type="number" name="production_planning[{{ $model['model_id'] }}][]" class="form-control form-control-sm production-planning" style="width: 80px;" value="{{ $model['planning_production'] }}" min="0">
                                                                                        </div>
                                                                                        <div class="col-md-4">
                                                                                            <label>Actual</label>
                                                                                            <input readonly type="number" name="production_actual[{{ $model['model_id'] }}][]" class="form-control form-control-sm production-actual" style="width: 80px;" value="{{ $model['actual_production'] }}" min="0">
                                                                                        </div>
                                                                                        <div class="col-md-4">
                                                                                            <label>Different</label>
                                                                                            <input readonly type="number" name="production_different[{{ $model['model_id'] }}][]" class="form-control form-control-sm production-different" style="width: 80px;" readonly value="{{ $model['balance'] }}">
                                                                                        </div>
                                                                                    </div>
                                                                                </td>

                                                                                <td colspan="3">

                                                                                    @if ($model['downtimes']->isEmpty())
                                                                                    <div class="downtime-container">



                                                                                        <div class="row">
                                                                                            <div class="col-md-6">

                                                                                                <label>Downtime</label><br>

                                                                                                <select name="downtime_category[]" class="form-control form-control-sm mb-2" readonly>

                                                                                                        <option value="">

                                                                                                        </option>


                                                                                                </select>

                                                                                                <div class="row">
                                                                                                    <div class="col-md-6">
                                                                                                        <label>From</label>
                                                                                                        <input readonly  type="time" name="time_from[]" class="form-control form-control-sm mb-2">
                                                                                                    </div>
                                                                                                    <div class="col-md-6">
                                                                                                        <label>Until</label>
                                                                                                        <input readonly  type="time" name="time_until[]" class="form-control form-control-sm mb-2">
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="row">
                                                                                                    <div class="col-md-5">
                                                                                                        <label>Cause</label>
                                                                                                        <textarea readonly name="cause[]" class="form-control form-control-sm mb-2" rows="3"></textarea>
                                                                                                    </div>
                                                                                                    <div class="col-md-5">
                                                                                                        <label>Action</label>
                                                                                                        <textarea readonly name="action[]" class="form-control form-control-sm mb-2" rows="3"></textarea>
                                                                                                    </div>

                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                    </div>
                                                                                    @else
                                                                                    @foreach ($model['downtimes'] as $item)
                                                                                    <div class="downtime-container">



                                                                                        <div class="row">
                                                                                            <div class="col-md-6">

                                                                                                <label>Downtime</label><br>

                                                                                                <select name="downtime_category[{{  $item->cause_id }}][]" class="form-control form-control-sm mb-2" readonly>
                                                                                                    @foreach($downtimeCategory->where('id', $item->cause_id) as $category)
                                                                                                        <option value="{{ $category->id }}" {{ in_array($category->id, $model['downtimes']->pluck('cause_id')->toArray()) ? 'selected' : '' }}>
                                                                                                            {{ $category->category }}
                                                                                                        </option>
                                                                                                    @endforeach

                                                                                                </select>

                                                                                                <div class="row">
                                                                                                    <div class="col-md-6">
                                                                                                        <label>From</label>
                                                                                                        <input readonly value="{{$item->time_from}}" type="time" name="time_from[{{  $item->time_from }}][]" class="form-control form-control-sm mb-2">
                                                                                                    </div>
                                                                                                    <div class="col-md-6">
                                                                                                        <label>Until</label>
                                                                                                        <input readonly value="{{$item->time_to}}" type="time" name="time_until[{{  $item->time_to }}][]" class="form-control form-control-sm mb-2">
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <div class="row">
                                                                                                    <div class="col-md-5">
                                                                                                        <label>Cause</label>
                                                                                                        <textarea readonly name="cause[{{ $item->problem }}][]" class="form-control form-control-sm mb-2" rows="3">{{$item->problem}}</textarea>
                                                                                                    </div>
                                                                                                    <div class="col-md-5">
                                                                                                        <label>Action</label>
                                                                                                        <textarea readonly name="action[{{ $item->action }}][]" class="form-control form-control-sm mb-2" rows="3">{{$item->action}}</textarea>
                                                                                                    </div>

                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                    </div>
                                                                                    @endforeach
                                                                                    @endif


                                                                                </td>

                                                                                <td>
                                                                                    <label>Repair</label>
                                                                                    @foreach ($model['not_goods'] as $item)
                                                                                    <input readonly type="number" name="repair[{{ $item->repair }}][]" class="form-control form-control-sm production-planning" style="width: 80px;" value="{{ $item->repair}}" min="0">
                                                                                    @endforeach
                                                                                    <label>Reject</label>
                                                                                    @foreach ($model['not_goods'] as $item)
                                                                                    <input readonly type="number" name="reject[{{ $item->reject }}][]" class="form-control form-control-sm production-planning" style="width: 80px;" value="{{$item->reject }}" min="0">
                                                                                    @endforeach
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
<script>
    $(document).ready(function() {
        $('.chosen-select').chosen({width:"230px"});

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
        });
    });
</script>
@endsection
