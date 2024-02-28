@extends('layouts.admin.app')
<!-- Dynamic Title -->
@section('title')
    {!! __('Products') !!}
@endsection
<!-- Bread crumb -->
@section('breadcrumb-content')
    <li class="breadcrumb-item"><a href="{!! route('admin.home') !!}">{!! __('Dashboard') !!}</a></li>
    <li class="breadcrumb-item active">{!! __('Products') !!}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <!-- table responsive -->
            <div class="card">
                <div class="card-body">
                    @can('product-create')
                        <div class="row">
                            <div class="col-md-12">
                                <a class="btn btn-info float-end text-white" href="{!! route('admin.products.create') !!}" id=""><i
                                        class="fa fa-plus-circle"></i> Create New Product</a>
                            </div>
                        </div>
                    @endcan
                    <form id="frmFilter">
                        <div class="row mt-1">
                            <div class="col-md-6">
                                <label for="category_id" class="col-form-label ">{!! __('Category') !!}</label>
                                <select id="category_id" class="form-select" name="category_id">
                                    <option value=""></option>
                                    @foreach ($categories as $cat)
                                        <option value="{!! $cat->id !!}"
                                            @if (request()->get('category_id') == $cat->id) selected @endif>{!! $cat->name !!}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="subcategory_id" class="col-form-label ">{!! __('Sub Category') !!}</label>
                                <select id="subcategory_id" class="form-select" name="subcategory_id">
                                    <option value=""></option>
                                    @if ($subCategory)
                                        <option value="{!! $subCategory->id !!}" selected>{!! $subCategory->name !!}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="subsubcategory_id" class="col-form-label ">{!! __('Sub sub Category') !!}</label>
                                <select id="subsubcategory_id" class="form-select" name="subsubcategory_id">
                                    <option value=""></option>
                                    @if ($subSubCategory)
                                        <option value="{!! $subSubCategory->id !!}" selected>{!! $subSubCategory->name !!}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="manufacturer_id" class="col-form-label ">{!! __('Manufacturer') !!}</label>
                                <select id="manufacturer_id" class="form-select" name="manufacturer_id">
                                    <option value=""></option>
                                    @foreach ($manufacturers as $manufacturer)
                                        <option value="{!! $manufacturer->id !!}"
                                            @if (request()->get('manufacturer_id') == $manufacturer->id) selected @endif>{!! $manufacturer->name !!}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="part_number" class="col-form-label ">{!! __('Part Number') !!}</label>
                                <input type="text" name="part_number" class="form-control" id="part_number"
                                    value="{!! request()->get('part_number') !!}">
                            </div>
                            <div class="col-md-6">
                                <label for="is_payable" class="col-form-label">{!! __('Is Payable') !!}</label>
                                <select id="is_payable" name="is_payable" class="form-control">
                                    <option value="">Select</option>
                                    <option value="1" @if (request()->get('is_payable') == '1') selected @endif>Yes</option>
                                    <option value="0" @if (request()->get('is_payable') == '0') selected @endif>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3 text-center">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info">
                                    {!! __('Search') !!}
                                </button>
                                <a href="{!! route('admin.products.index') !!}" class="btn btn-danger">{!! __('Reset') !!}</a>
                                <a href="javascript:void(0)" class="btn btn-success text-white" id="btnUpdateAll">{!! __('Update Payment status') !!}</a>
                                </a>

                            </div>
                        </div>
                    </form>
                    <div class="table-responsive mt-3">
                        <table id="product-table" class="table table-hover display w-100">
                            <thead>
                                <tr>
                                    <th style="max-width: 50px; !important;">No</th>
                                    <th>Part Number</th>
                                    <th>Category</th>
                                    <th>Sub Category</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Payable</th>
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
        var datatableUrl = "{{ route('admin.products.index') }}";
        var searchManufacturersRoute = "{!! route('admin.searchManufacturers') !!}";
        var searchCategoriesRoute = "{!! route('admin.searchCategories') !!}";
        var fetchSubcategoriesRoute = "{!! route('admin.products.subcategories') !!}/";
        var updatePayableRoute = "{!! route('admin.products.updateIsPayable') !!}";
        var updateAllPayableRoute = "{!! route('admin.products.updateAllPayable') !!}";
    </script>
    <script type="text/javascript" src="{!! asset('assets/admin/js/pages/products.js') !!}?v=1"></script>
@endsection
