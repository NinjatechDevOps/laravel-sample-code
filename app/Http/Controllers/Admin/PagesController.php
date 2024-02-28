<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use Auth;
use DataTables;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Image;
use App\Http\Requests\UpdateDataRequest;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct()
    {
        /* Pages Permissions */
        $this->middleware(
            'permission:pages-show|pages-list|
             pages-edit|pages-delete',
            ['only' => ['index']]
        );
        $this->middleware('permission:pages-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:pages-delete', ['only' => ['destroy']]);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        // dd($data[1]['data']['sections'][0]['type']);
        if ($request->ajax()) {
            $data = CmsContent::where('template', '!=', 'news')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // dd($row);
                    $btn = '';
                    if (Auth::user()->can('pages-edit')) {
                        $btn = '<a
                        href="' . route('admin.pages.edit', $row->id) . '"
                        data-toggle="tooltip"
                        data-id="' . $row->id . '"
                        data-original-title="Edit"
                        class="edit btn btn-primary btn-sm editPage">Edit</a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.cms-content.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Application|Factory|View|Response
     */
    public function edit(CmsContent $page)
    {
        return view('admin.cms-content.form', compact(
            'page'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDataRequest $request
     * @param CmsContent $cmsContent
     * @return RedirectResponse
     */
    public function update(UpdateDataRequest $request, CmsContent $page)
    {
        $requestedData = $request->validated();
        $data = [];
        if(is_array($request->data)) {
            $data = $request->data;
        }
        $updateData = [
            'title' => $request->title,
            'page_title' => $request->page_title,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'footer_text' => $request->footer_text ?? "",
        ];

        $featuredImage = $request->file('featured_image_file');
        if ($featuredImage) {
            $imageName = time() . '.' . $featuredImage->extension();
            $imgFile = Image::make($featuredImage->getRealPath());
            $imgFile->resize(1200, 1200, function ($constraint) {
                $constraint->aspectRatio();
            });
            Storage::put(config('constants.CMS_CONTENT_PATH') . $imageName, $imgFile->stream());
            $updateData['featured_image'] = $imageName;
        }

        if ($data && isset($data['sections'])) {
            foreach ($data['sections'] as $key => $section) {
                if (isset($section['contents'])) {
                    foreach ($section['contents'] as $sectionKey => $aboutUs) {
                        if ($aboutUs['title'] == null) {
                            unset($data['sections'][$key]['contents'][$sectionKey]);
                            continue;
                        }
                        if(isset($aboutUs['image'])) {
                            $image = $aboutUs['image'];
                            if ($image) {
                                $imageName = uniqid() . '.' . $image->extension();
                                $imgFile = Image::make($image->getRealPath());
                                $imgFile->resize(1200, 520, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                                Storage::put(config('constants.CMS_CONTENT_PATH') . $imageName, $imgFile->stream());
                                $data['sections'][$key]['contents'][$sectionKey]['image'] = Storage::url(config('constants.CMS_CONTENT_PATH') . $imageName);
                            }
                        }
                        else if (@$aboutUs['oldimages']) {
                            $data['sections'][$key]['contents'][$sectionKey]['image'] = $aboutUs['oldimages'] ?? "";
                            $data['sections'][$key]['contents'][$sectionKey]['oldimages'] = "";
                        }
                    }
                }
                if (array_key_exists('image_files', $section)) {
                    foreach ($section['image_files'] as $newKey => $image) {
                        if ($image) {
                            $imageName = uniqid() . '.' . $image->extension();
                            $imgFile = Image::make($image->getRealPath());
                            $imgFile->resize(1200, 520, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            Storage::put(config('constants.CMS_CONTENT_PATH') . $imageName, $imgFile->stream());
                            $data['sections'][$key]['images'][$newKey] = Storage::url(config('constants.CMS_CONTENT_PATH') . $imageName);
                        }
                    }
                    unset($data['sections'][$key]['image_files']);
                }
                if (isset($section['fields'])) {
                    if (array_key_exists('sliders', $section['fields'])) {
                        foreach ($section['fields']['sliders'] as $ikey => $image) {
                            if (isset($image['image'])) {
                                $file = $image['image'];
                                $imageName = time() . rand() . '.' . $file->extension();
                                $imgFile = Image::make($file->getRealPath());
                                $imgFile->resize(1200, 520, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                                Storage::put(config('constants.CMS_CONTENT_PATH') . $imageName, $imgFile->stream());
                                $data['sections'][$key]['fields']['sliders'][$ikey]['image'] = $imageName;
                                $data['sections'][$key]['fields']['sliders'][$ikey]['image_old'] = "";
                            } else if (@$image['image_old']) {
                                $data['sections'][$key]['fields']['sliders'][$ikey]['image'] = $image['image_old'] ?? "";
                                $data['sections'][$key]['fields']['sliders'][$ikey]['image_old'] = "";
                            }
                        }
                        unset($data['sections'][$key]['image_files']);
                    }
                }
            }
        }

        // New Logic for about-us
        if (is_array($data) && count($data)) {
            if(isset($data['contents']) && is_array($data['contents']) && count($data['contents']))
            {
                foreach($data['contents'] as $key => $aboutUs) {
                    if ($aboutUs['title'] == null) {
                        unset($data['contents'][$key]);
                        continue;
                    }
                    if(isset($aboutUs['image'])) {
                        $image = $aboutUs['image'];
                        if ($image) {
                            $imageName = uniqid() . '.' . $image->extension();
                            $imgFile = Image::make($image->getRealPath());
                            $imgFile->resize(1200, 520, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            Storage::put(config('constants.CMS_CONTENT_PATH') . $imageName, $imgFile->stream());
                            $data['contents'][$key]['image'] = Storage::url(config('constants.CMS_CONTENT_PATH') . $imageName);
                        }
                    }
                    else if (@$aboutUs['oldimages']) {
                        $data['contents'][$key]['image'] = $aboutUs['oldimages'] ?? "";
                        $data['contents'][$key]['oldimages'] = "";
                    }
                }
            }
        }
        $updateData['data'] = $data;
        $page->update($updateData);
        // return redirect()->route('admin.pages.index')->with('success', 'Data updated successfully');
        return redirect()->back()->with('success', 'Data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
