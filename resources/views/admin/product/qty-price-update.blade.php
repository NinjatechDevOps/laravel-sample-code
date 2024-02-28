@extends('layouts.admin.app')
<!-- Dynamic Title -->
@section('title') {!! __('Products Qty & Price Update / Products Delete') !!} @endsection
<!-- Bread crumb -->
@section('breadcrumb-content')
<li class="breadcrumb-item"><a href="{!! route('admin.home') !!}">{!! __('Dashboard') !!}</a></li>
<li class="breadcrumb-item active">{!! __('Products Qty & Price Update / Products Delete') !!}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <!-- table responsive -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-end">
                        <a class="btn btn-primary  text-white" href="javascript:void(0)" onclick="javascript:$('#frmFilter').toggle();">Filters @if($totalFiltered)({!! $totalFiltered !!}) @endif</a>

                        <a class="btn btn-info  text-white" href="javascript:void(0)" onclick="javascript:$('#frmQtyPriceUpdate').toggle();" id="btnFrmQtyPrice">Update Price & Qty</a>

                        <a href="javascript:void(0)" class="btn btn-danger text-white" id="btnDeleteAll">{!! __('Delete Products') !!}</a>
                    </div>
                </div>
                <form id="frmFilter" style="display:none">
                    <div class="row mt-1">
                        <div class="col-md-6">
                            <label for="category_id" class="col-form-label ">{!! __('Category') !!}</label>
                            <select id="category_id" class="form-select" name="category_id">
                                <option value=""></option>
                                @foreach ($categories as $cat)
                                <option value="{!! $cat->id !!}" @if(request()->get('category_id') == $cat->id) selected @endif>{!! $cat->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="subcategory_id" class="col-form-label ">{!! __('Sub Category') !!}</label>
                            <select id="subcategory_id" class="form-select" name="subcategory_id">
                                <option value=""></option>
                                @if($subCategory)
                                <option value="{!! $subCategory->id !!}" selected>{!! $subCategory->name !!}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="subsubcategory_id" class="col-form-label ">{!! __('Sub sub Category') !!}</label>
                            <select id="subsubcategory_id" class="form-select" name="subsubcategory_id">
                                <option value=""></option>
                                @if($subSubCategory)
                                <option value="{!! $subSubCategory->id !!}" selected>{!! $subSubCategory->name !!}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="manufacturer_id" class="col-form-label ">{!! __('Manufacturer') !!}</label>
                            <select id="manufacturer_id" class="form-select" name="manufacturer_id">
                                <option value=""></option>
                                @foreach ($manufacturers as $supplier)
                                <option value="{!! $supplier->id !!}" @if(request()->get('manufacturer_id') == $supplier->id) selected @endif>{!! $supplier->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="part_number" class="col-form-label ">{!! __('Part Number') !!}</label>
                            <input type="text" name="part_number" class="form-control" id="part_number" value="{!! request()->get('part_number') !!}">
                        </div>
                    </div>
                    <div class="row mt-3 text-center">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-info">
                                {!! __('Search') !!}
                            </button>
                            <a href="{!! route('admin.products.qtyPriceUpdate') !!}" class="btn btn-danger">{!! __('Reset') !!}</a>
                        </div>
                    </div>
                </form>
                <form id="frmQtyPriceUpdate" method="post" style="display:none">
                    @csrf
                    <input type="hidden" name="isFilter" value="{!! $isFilter !!}">
                    <input type="hidden" name="category_id" value="{!! $category->id ?? '' !!}">
                    <input type="hidden" name="subcategory_id" value="{!! $subCategory->id ?? '' !!}">
                    <input type="hidden" name="subsubcategory_id" value="{!! $subSubCategory->id ?? '' !!}">
                    <input type="hidden" name="manufacturer_id" value="{!! $manufacturer->id ?? '' !!}">
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label for="update_qty" class="col-form-label ">{!! __('Update Qty (Multiple By)') !!}</label>
                            <input type="number" class="form-control @error('update_qty') is-invalid @enderror" name="update_qty" value="update_qty" step="0.01" min="0">
                            @error('update_qty')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="update_price" class="col-form-label ">{!! __('Update Price (Multiple By)') !!}</label>
                            <input type="number" class="form-control @error('update_price') is-invalid @enderror" name="update_price" value="update_price" step="0.01" min="0">
                            @error('update_price')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-info">{!! __('Update') !!}</button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive mt-3">
                    <table id="product-table" class="table table-hover display w-100">
                        <thead>
                            <tr>
                                <th><div class="form-check"><input type="checkbox" class="form-check-input checkAll">All</div></th>
                                <th>No</th>
                                <th>Part Number</th>
                                <th>Category</th>
                                <th>Sub Category</th>
                                <th>Manufacturer</th>
                                <th>Quantity</th>
                                <th>Price</th>
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
var datatableUrl = "{{ route('admin.products.qtyPriceUpdate') }}";
var searchManufacturersRoute = "{!! route('admin.searchManufacturers') !!}";
var searchCategoriesRoute = "{!! route('admin.searchCategories') !!}";
var fetchSubcategoriesRoute = "{!! route('admin.products.subcategories') !!}/";
</script>
<script type="text/javascript" src="{!! asset('assets/admin/js/pages/products.js') !!}"></script>
@endsection
