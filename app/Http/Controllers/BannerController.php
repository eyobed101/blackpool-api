<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BannerModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class BannerController extends Controller
{
    //
    public function createBannerImage(Request $request)
    {
         try { 
            $validator = Validator::make($request->all(), [
                'banner_alt' => 'required',
                'banner_image' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 500);
            }
            if (!$request->hasFile('banner_image')) {
              return response()->json(['error' => "please upload banner image"], 500);
            }
            $time_now = Carbon::now()->getTimestamp();
            $banner_image = $request->file('banner_image');
            $filename = strval($time_now) . '-' . $banner_image->getClientOriginalName();
            $new_banner = BannerModel::create([
                'banner_alt' => $request->banner_alt,
                'banner_image' => $request->file('banner_image')->storeAs('bannerImage', $filename),
            ]);
            return response()->json(['banner' => $new_banner]);
         } catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
         }
    }
    public function getAllBanners()
    {
          try {
               $banner = BannerModel::all();
               return response()->json(['banners' => $banner]);
          } catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
          }
    }
    public function getActiveBannerImage()
    {
          try {
            $current_banner = BannerModel::where('isActive', '=', true)->get();
            return response()->json(['banner' => $current_banner]);
          } catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
          }
    }
    public function setDefaultBannerImage(Request $request)
    {
          try {
               $validator = Validator::make($request->all(), [
                   'banner_id' => 'required'
               ]);
               if ($validator->fails()) {
                     return response()->json(['error' => $validator->errors()], 500);
               }
               $id = $request->banner_id;
               // set the old wallet false
               walletModel::where('isCurrent', '=', true)->update([
                  'isCurrent' => false
               ]);
               $current_banner = BannerModel::find($id);
               $current_banner->update([
                 'isCurrent' => true
               ]);
               return response()->json(['wallet' => $current_banner]);
          }
          catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
          }
    }
}
