<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function delete($id)
    {
        $image = ProductImage::findOrFail($id);

        if ($image->is_primary) {
            return back()->withErrors(
                'Không thể xoá ảnh đại diện. Hãy đổi ảnh đại diện trước.'
            );
        }

        if (
            $image->image_path &&
            Storage::disk('public')->exists($image->image_path)
        ) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Đã xoá ảnh');
    }
}
