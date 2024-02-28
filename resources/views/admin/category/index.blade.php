@extends('layouts.admin.app')
<!-- Dynamic Title -->
@section('title') {{ __('Categories') }} @endsection
<!-- Bread crumb -->
@section('breadcrumb-content')
<li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Categories') }}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <!-- table responsive -->
        <div class="card">
            <div class="card-body">
                @can('category-create')
                <a class="btn btn-info m-b-10 float-end text-white" href="{{route('admin.categories.create')}}" id=""><i class="fa fa-plus-circle"></i> Create New Category</a>
                @endcan
                <div class="table-responsive m-t-40">
                    <table id="category-table" class="table table-hover display w-100">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th width="50">Image</th>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Parent of Parent</th>
                                <th>Product Count</th>
                                <th style="width: 20%;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
var datatableUrl = "{!! route('admin.categories.index') !!}";
</script>
<script type="text/javascript" src="{!! asset('assets/admin/js/pages/categories.js') !!}"></script>
@endsection
