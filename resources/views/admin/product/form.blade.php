@extends('layouts.admin.app')
<!-- Dynamic Title -->
@section('title') {!! $product->id ? __('Edit Product') :  __('Create Product')!!} @endsection
<!-- Bread crumb -->
@section('breadcrumb-content')
<li class="breadcrumb-item"><a href="{!! route('admin.home') !!}">{!! __('Dashboard') !!}</a></li>
<li class="breadcrumb-item"><a href="{!! route('admin.products.index') !!}">{!! __('Products') !!}</a></li>
<li class="breadcrumb-item active">{!! $product->id ? __('Edit Product') :  __('Create Product') !!}</li>
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="frmAddUpdate" action="{!! $product->id ? route('admin.products.update', $product->id) :  route('admin.products.store')!!}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($product->id)
                            @method('PUT')
                        @endif
                        <div class="row mb-3">
                            <label for="part_number" class="col-md-2 col-form-label ">{!! __('Part Number') !!}<span style="color: red;">*</span> </label>
                            <div class="col-md-10">
                                <input id="part_number" type="text" class="form-control @error('part_number') is-invalid @enderror" name="part_number" value="{!! old('part_number', $product->part_number) !!}">
                                @error('part_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="category_id" class="col-md-2 col-form-label ">{!! __('Category') !!}<span style="color: red;">*</span> </label>
                            <div class="col-md-4">
                                <select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                                    <option value=""></option>
                                    @foreach($categories as $cat)
                                    <option value="{!!$cat->id!!}" @if(old('category_id', $product->category_id) == $cat->id) selected @endif>{!!$cat->name!!}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>

                            <label for="subcategory_id" class="col-md-2 col-form-label text-end">{!! __('Sub Category') !!}<span style="color: red;">*</span> </label>
                            <div class="col-md-4">
                                <select id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" name="subcategory_id">
                                    <option value=""></option>
                                    @if($product->subcategory_id)
                                    @foreach($subCategories as $subCat)
                                    <option value="{!!$subCat->id!!}" @if(old('subcategory_id', $product->subcategory_id) == $subCat->id) selected @endif>{!!$subCat->name!!}</option>
                                    @endforeach
                                    @endif
                                </select>
                                @error('subcategory_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="subsubcategory_id" class="col-md-2 col-form-label">{!! __('Sub sub Category') !!}</label>
                            <div class="col-md-4">
                                <select id="subsubcategory_id" class="form-select @error('subsubcategory_id') is-invalid @enderror" name="subsubcategory_id">
                                    <option value=""></option>
                                    @if($product->subsubcategory_id)
                                    @foreach($subSubCategories as $subSubcat)
                                        <option value="{!!$subSubcat->id!!}" @if(old('subsubcategory_id', $product->subsubcategory_id) == $subSubcat->id) selected @endif>{!!$subSubcat->name!!}</option>
                                    @endforeach
                                    @endif
                                </select>
                                @error('subsubcategory_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>

                            <label for="manufacturer_id" class="col-md-2 col-form-label text-end">{!! __('Manufacturer') !!}<span style="color: red;">*</span> </label>
                            <div class="col-md-4">
                                <select id="manufacturer_id" class="form-select @error('manufacturer_id') is-invalid @enderror" name="manufacturer_id">
                                    <option value=""></option>
                                    @foreach($manufacturers as $manufacturer)
                                    <option value="{!!$manufacturer->id!!}"
                                            @if(old('category_id', $product->manufacturer_id) == $manufacturer->id) selected @endif>{!!$manufacturer->name!!}</option>
                                    @endforeach
                                </select>
                                @error('manufacturer_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tags" class="col-md-2 col-form-label ">{!! __('Tags') !!}</label>
                            <div class="col-md-10">
                                <textarea id="tags" type="text" class="form-control @error('tags') is-invalid @enderror" name="tags" rows="4">{!! old('tags', $product->tags) !!}</textarea>
                                @error('tags')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="price_per_quantity" class="col-md-2 col-form-label ">{!! __('Price Per Quantity') !!}</label>
                            <div class="col-md-4">
                                <input id="price_per_quantity" type="number" class="form-control @error('price_per_quantity') is-invalid @enderror" name="price_per_quantity" value="{!! old('price_per_quantity', $product->price_per_quantity) !!}">
                                @error('price_per_quantity')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>

                            <label for="quantity" class="col-md-2 col-form-label text-end">{!! __('Quantity') !!}</label>
                            <div class="col-md-4">
                                <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{!! old('quantity', $product->quantity) !!}">
                                @error('quantity')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="short_description" class="col-md-2 col-form-label ">{!! __('Description') !!}</label>
                            <div class="col-md-10">
                                <textarea id="short_description" type="text" class="form-control @error('short_description') is-invalid @enderror" name="short_description" rows="4">{!! old('short_description', $product->short_description) !!}</textarea>
                                @error('short_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="description" class="col-md-2 col-form-label ">{!! __('Detailed Description') !!}</label>
                            <div class="col-md-10">
                                <textarea id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" rows="4">{!! old('description', $product->description) !!}</textarea>
                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="datasheet_file" class="col-md-2 col-form-label ">{!! __('Datasheet') !!}</label>
                            <div class="col-md-4">
                                <input id="datasheet_file" type="file" class="form-control @error('datasheet_file') is-invalid @enderror" name="datasheet_file">
                                @error('datasheet_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                                @if($product->data_sheet_full_url)
                                    <a href="{!!$product->data_sheet_full_url!!}" target="_blank">View</a>
                                @endif
                            </div>

                            <label for="image_file" class="col-md-2 col-form-label text-end">{!! __('Image') !!}</label>
                            <div class="col-md-4">
                                <input id="image_file" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg" type="file" class="form-control @error('image_file') is-invalid @enderror" name="image_file">
                                @error('image_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                                @if($product->image_full_url && $product->image_url)
                                    <div>
                                        <img src="{!!$product->image_full_url!!}" class="mt-2" style="max-width: 100px;" id="image_file_preview">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Meta Title -->
                        <div class="row mb-3">
                            <label for="rohs_status" class="col-md-2 col-form-label ">{!! __('Meta Title') !!}</label>
                            <div class="col-md-10">
                                <input id="meta_title" type="text" class="form-control @error('meta_title') is-invalid @enderror" name="meta_title" value="{!! old('meta_title', $productDetail->meta_title) !!}">
                                @error('meta_title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="is_payable" class="col-md-2 col-form-label">{!! __('Is Payable') !!}</label>
                            <div class="col-md-10">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_payable" id="is_payable_true" value="1" {{ (old('is_payable', $product->is_payable) == 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_payable_true">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_payable" id="is_payable_false" value="0" {{ (old('is_payable', $product->is_payable) == 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_payable_false">No</label>
                                </div>
                                @error('is_payable')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="meta_description" class="col-md-2 col-form-label ">{!! __('Meta Description') !!}</label>
                            <div class="col-md-10">
                                <input id="meta_description" type="text" class="form-control @error('meta_description') is-invalid @enderror" name="meta_description" value="{!! old('meta_description', $productDetail->meta_description) !!}">
                                @error('meta_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <!--<label for="email" class="col-md-2 col-form-label ">{!! __('Attributes') !!}</label>-->
                            @for($i = 1; $i <= 56; $i++)
                            <div class="col-md-2"></div>
                            <div class="col-md-5 mb-3">
                                <input type="text" placeholder="Attribute Name {!!$i!!}" class="form-control @error('data[attribute_' . $i . ']') is-invalid @enderror" name="{!!'data[attribute_' . $i . ']'!!}" value="{!! old('data[attribute_' . $i . ']', @$productDetail->data['attribute_' . $i]) !!}">
                                @error('attribute_' . $i)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <input type="text" placeholder="Value {!!$i!!}" class="form-control @error('data[value_' . $i . ']') is-invalid @enderror" name="{!!'data[value_' . $i . ']'!!}" value="{!! old('data[value_' . $i . ']', @$productDetail->data['value_' . $i]) !!}">
                                @error('value_' . $i)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{!! $message !!}</strong>
                                </span>
                                @enderror
                            </div>
                            @endfor
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-12 text-center">
                                <a href="{{ route('admin.products.index') }}" class="btn common-back-button-link">{{ __('Back') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    {!! $product->id ? __('Update') :  __('Create')!!}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
var searchManufacturersRoute = "{!! route('admin.searchManufacturers') !!}";
var searchCategoriesRoute = "{!! route('admin.searchCategories') !!}";
var fetchSubcategoriesRoute = "{!! route('admin.products.subcategories') !!}/";
</script>
<script type="text/javascript" src="{!! asset('assets/admin/js/pages/products.js') !!}"></script>
@endsection
