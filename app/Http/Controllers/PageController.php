<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'title'         => 'Data Page | VarTech Indonesia',
            'title_table'   => 'Data Page'
        ];
        $data['page_category']  = PageCategory::where('status', 'Active')->orderBy('title')->get();
        $data['data']           = Page::with('PageCategory', 'User')->orderByDesc('updated_at')->get();
        dd($data['data']);
        return view('admin.page.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'title'         => 'Add Data Page | VarTech Indonesia',
            'title_table'   => 'Add Data Page'
        ];
        $data['page_category']  = PageCategory::where('status', 'Active')->orderBy('title')->get();
        return view('admin.page.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'title'             => 'required|min:5|max:255',
            'excerpt'           => 'required',
            'body'              => 'required',
            'image'             => 'image|mimes:png,jpg,jpeg|max:10240',
            'status'            => 'required'
        ];
        $messages = [
            'title.required'    => 'Title Required',
            'title.min'         => 'Title Min. 5 karakter',
            'title.max'         => 'Title Max. 255 karakter',

            'excerpt.required'  => 'Excerpt Required',
            'body.required'     => 'Body Required',

            'image.image'       => 'Gambar Wajib  di Isi dengan Image Format : JPEG, JPG, PNG Max. 10 Mb',
            'image.mimes'       => 'Gambar Wajib  dengan Format : JPEG, JPG, PNG Max. 10 Mb',
            'image.max'         => 'Gambar Max. 10 Mb',

            'status.required'   => 'Status Required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $slug       = strtolower(str_replace(' ', '-', $request->title));
        $seo_title  = "vartech-indonesia-" . $slug;

        // Image
        $image              = $request->file('image');
        if ($image == null) {
            $uploadedFile   = "";
        } else {
            // Ganti Nama Image
            $image          = $request->file('image');
            $ImgValue       = $request->file('image');
            $getFileExt     = $ImgValue->getClientOriginalExtension();
            $uploadedFile   = $slug . '-' . '.' . $getFileExt;
            // Upload Image
            $image->storeAs('public/images-page', $uploadedFile);
            // Save Image di DB
            $uploadedFile   = 'images-page/' . $uploadedFile;
        }

        $query  = Page::create([
            'id_page_category'  => $request->id_page_category,
            'id_author'         => Auth::user()->id,
            'slug'              => $slug,
            'seo_title'         => $seo_title,
            'meta_keywords'     => $request->meta_keywords,
            'meta_description'  => $request->meta_description,
            'title'             => $request->title,
            'excerpt'           => $request->excerpt,
            'body'              => $request->body,
            'image'             => $uploadedFile,
            'status'            => $request->status
        ]);

        if ($query) {
            return response()->json(['success'  => 'Page Category Saved Successfully']);
        } else {
            return response()->json(['error'    => 'Page Category Saved Failed']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data   = Page::find($id);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'title'             => 'required|min:5|max:255',
            'excerpt'           => 'required',
            'body'              => 'required',
            'image'             => 'image|mimes:png,jpg,jpeg|max:10240',
            'status'            => 'required'
        ];
        $messages = [
            'title.required'    => 'Title Required',
            'title.min'         => 'Title Min. 5 karakter',
            'title.max'         => 'Title Max. 255 karakter',

            'excerpt.required'  => 'Excerpt Required',
            'body.required'     => 'Body Required',

            'image.image'       => 'Gambar Wajib  di Isi dengan Image Format : JPEG, JPG, PNG Max. 10 Mb',
            'image.mimes'       => 'Gambar Wajib  dengan Format : JPEG, JPG, PNG Max. 10 Mb',
            'image.max'         => 'Gambar Max. 10 Mb',

            'status.required'   => 'Status Required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $slug       = strtolower(str_replace(' ', '-', $request->title));
        $seo_title  = "vartech-indonesia-" . $slug;

        // Image
        $image              = $request->file('image');
        if (($request->title != $request->title_hidden) && ($image != NULL)) {
            // Change Image Name
            $image          = $request->file('image');
            $ImgValue       = $request->file('image');
            $getFileExt     = $ImgValue->getClientOriginalExtension();
            $uploadedFile   = $slug . '-' . '.' . $getFileExt;
            // Delete Old Image
            File::delete('storage/' . $request->image_hidden);
            // Upload New Image
            $image->storeAs('public/images-page', $uploadedFile);
            // Save Image in DB
            $uploadedFile   = 'images-page/' . $uploadedFile;
        } else if (($request->title != $request->title_hidden) && ($image == NULL)) {
            // Change Image Name
            $image          = $request->image_hidden;
            $getFileExt     = substr($image, strpos($image, ".") + 1);
            $uploadedFile   = $slug . '-' . '.' . $getFileExt;
            // Save Image in DB
            $uploadedFile   = 'images-page/' . $uploadedFile;
            // Rename Image
            Storage::rename('public/' . $request->image_hidden, 'public/' . $uploadedFile);
        } else if (($request->title == $request->title_hidden) && ($image != NULL)) {
            // Change Image Name
            $image          = $request->file('image');
            $ImgValue       = $request->file('image');
            $getFileExt     = $ImgValue->getClientOriginalExtension();
            $uploadedFile   = $slug . '-' . '.' . $getFileExt;
            // Delete Old Image
            File::delete('storage/' . $request->image_hidden);
            // Upload New Image
            $image->storeAs('public/images-page', $uploadedFile);
            // Save Image in DB
            $uploadedFile   = 'images-page/' . $uploadedFile;
        } else {
            $uploadedFile   = $request->image_hidden;
        }

        $query  = Page::whereId($id)->update([
            'id_page_category'  => $request->id_page_category,
            'id_author'         => Auth::user()->id,
            'slug'              => $slug,
            'seo_title'         => $seo_title,
            'meta_keywords'     => $request->meta_keywords,
            'meta_description'  => $request->meta_description,
            'title'             => $request->title,
            'excerpt'           => $request->excerpt,
            'body'              => $request->body,
            'image'             => $uploadedFile,
            'status'            => $request->status
        ]);

        if ($query) {
            return response()->json(['success'  => 'Page Saved Successfully']);
        } else {
            return response()->json(['error'    => 'Page Saved Failed']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $find  = Page::find($id);
        File::delete('storage/' . $find->image);
        // $query ?  return response()->json(['success'  => 'Page Delete Successfully']) :  return response()->json(['error'    => 'Page Delete Failed']);
        if (Page::where('id', $find->id)->delete()) {
            return response()->json(['success'  => 'Page Delete Successfully']);
        } else {
            return response()->json(['error'    => 'Page Delete Failed']);
        }
    }
}
