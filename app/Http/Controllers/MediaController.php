<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Upload an image and return its URL
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('images', 'public');
            
            $url = Storage::url($path);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'full_url' => url($url),
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided',
        ], 400);
    }
}
