<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Image;

use File;

use App\Repositories\PushNotificationRepository as PushRepo;

use Log;

use App\AdminVideo;

class SampleController extends Controller
{

    public function test(Request $request) {

        return view('admin.sample');

    }

    public function get_image(Request $request) {

        dd($request->file('image'));

    }

    public function send_push_notification(Request $request) {

        Log::info("send_push_notification");

        $data = [];

        $data['admin_video_id'] = 157;

        PushRepo::push_notification_android($request->register_ids , "Hello World" , "Hello Sharon!!!" , $data);

        $response_array = ['success' => true , 'message' => "done"];

        return response()->json($response_array , 200);

    }

	/**
     * Function Name : ajax_subscription_payments()
     *
     * To load user payments using ajax
     *
     * @created_by shobana Chandrasekar
     *
     * @updated_by -
     *
     * @param object $request - Object details
     *
     * @return response of details
     */
    public function ajax_subscription_payments(Request $request) {

        // Based on draw count skip will increase

        $skip = $request->start;

        $payments = UserPayment::orderBy('created_at' , 'desc')->skip($skip)
            ->take(10)->get();

        $datas = [];

        // Intialize S.no count

        $i = $skip;

        foreach ($payments as $key => $payment) {

            $i++;

            $name = $payment->user ? "<a href='".route('admin.users.view' , $payment->user_id)."'>".$payment->user->name."</a>" : '-';

            $subscription_title = $payment->subscription ? 
        "<a href='".route('admin.subscriptions.view', $payment->subscription->unique_id)."' target='_blank'>".$payment->subscription->title."</a>" : "-";

            $payment_mode = $payment->payment_mode ? $payment->payment_mode : '';

            $coupon_amount = Setting::get('currency').' '.$payment->coupon_amount ? $payment->coupon_amount : "0.00";

            $subscription_amount = Setting::get('currency').' '.$payment->sub_amount ? $payment->sub_amount : "0.00";

            $total_amount = Setting::get('currency').' '.$payment->amount ? $payment->amount : "0.00";

            $is_coupon_applied = $payment->is_coupon_applied ? "<span class='label label-success'>".tr('yes')."</span>" : "<span class='label label-danger'>".tr('no')."</span>";

            $coupon_reason = $payment->coupon_reason ? $payment->coupon_reason : '-';

            $payment_status = $payment->status ? "<span class='label label-success'>".tr('paid')."</span>" : "<span class='label label-danger'>".tr('not_paid')."</span>";

            $datas[] = [$i, $name, $subscription_title, $payment_mode, $payment->payment_id, $payment->coupon_code, $coupon_amount, $subscription_amount,
                $total_amount, date('d M Y',strtotime($payment->expiry_date)),
                $is_coupon_applied, $coupon_reason,$payment_status
                ];

        }

        // Total Records count

        $payment_count = UserPayment::count();

        return response()->json(['data'=>$datas,'recordsFiltered'=>$payment_count, 'recordsTotal'=>$payment_count,'draw'=>$request->draw]);

    }

    public function compress_image_upload() {
        return view('sample.index');
    }

    public function compress_image_check(Request $request) {

        $image_path = public_path("uploads/game.png");

        $output_path = public_path("uploads/save-bg.png");

        $large_output_path = public_path("uploads/large.png");

        // create an image
        $image_compression = Image::make($image_path);

        $image_compression->fit(300, 200);

        $image_compression->save($output_path);

                dd($size = $image_compression->filesize());


        // your desired ratio
        $ratio = 16/9;

        // resize
        $image->fit($image->width(), intval($image->width() / $ratio));




        // $image_compression->encode('png');

        // $image_compression->resize(200, 200, function ($constraint) {
        //     $constraint->aspectRatio();
        // });

        // // backup status
        // $image_compression->backup();

        // perform some modifications
        // $image_compression->resize(385, 225);
        // $image_compression->invert();
        $image_compression->save($output_path);

        // // reset image (return to backup state)
        // $image_compression->reset();

        // // perform other modifications

        // $image_compression->resize(1280, 548.57);
        // // $image_compression->invert();
        // $image_compression->save($large_output_path);

        return response()->json(['success' => true]);

    }

    public function getImageThumbnail(Request $request) {

        $picture = $request->file('default_image');

        $file_name = "123"."-".rand();

        $ext = $picture->getClientOriginalExtension();

        $file_name = $file_name . ".png";

        $folder_path = $images_path = "uploads/check/".$file_name;

        $output_path = public_path($folder_path);


        $path = "uploads"; $width = $request->width ? $request->width : 300; $height = $request->height ? $request->height : 100; $type = "fit";

        $images_path = "images";

        $path = ltrim($path, "/");

        //returns the original image if isn't passed width and height

        if (is_null($width) && is_null($height)) {

            return url($folder_path);
        }

        //if thumbnail exist returns it
        if (File::exists(public_path("{$images_path}/thumbs/" . "{$width}x{$height}/" . $path))) {
            return url("{$images_path}/thumbs/" . "{$width}x{$height}/" . $path);
        }

        //If original image doesn't exists returns a default image which shows that original image doesn't exist.
        // if (!File::exists(public_path("{$images_path}/" . $path))) {

        //     /*
        //      * 2 ways
        //      */

        //     //1. recursive call for the default image
        //     //return $this->getImageThumbnail("error/no-image.png", $width, $height, $type);

        //     //2. returns an image placeholder generated from placehold.it
        //     return "http://placehold.it/{$width}x{$height}";
        // }

        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png'];

        $contentType = $picture->getMimeType();


        if (in_array($contentType, $allowedMimeTypes)) { //Checks if is an image

            $image = Image::make($picture);

            switch ($type) {
                case "fit": {
                    $image->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                    break;
                }
                case "resize": {
                    //stretched
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                }
                case "background": {
                    $image->resize($width, $height, function ($constraint) {
                        //keeps aspect ratio and sets black background
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                case "resizeCanvas": {
                    $image->resizeCanvas($width, $height, 'center', false, 'rgba(0, 0, 0, 0)'); //gets the center part
                }
            }

            //relative directory path starting from main directory of images
            $dir_path = (dirname($output_path) == '.') ? "" : dirname($output_path);


            //Create the directory if it doesn't exist
            // if (!File::exists(public_path("{$images_path}/thumbs/" . "{$width}x{$height}/" . $dir_path))) {
            //     File::makeDirectory(public_path("{$images_path}/thumbs/" . "{$width}x{$height}/" . $dir_path), 0775, true);
            // }

            //Save the thumbnail
            $image->save($output_path);

            $uploaded_image = url($folder_path);

            //return the url of the thumbnail
            return redirect()->route('compress.image')->with('uploaded_image' , $uploaded_image);

        } else {


            //return a placeholder image
            return "http://placehold.it/{$width}x{$height}";
        }
    }

    public function interview_tasks(Request $request) {

        $skip = $request->skip ?: 0;

        $take = $request->take ?: 12;

        $admin_videos = AdminVideo::where('status', 1)
                            ->skip($skip)->take($take)
                            ->select('id as admin_video_id', 'title', 'description', 'created_at', 'updated_at')
                            ->get();

        $vides_list = ['http://184.72.239.149/vod/mp4:BigBuckBunny_115k.mov/playlist.m3u8', 'https://www.hdwplayer.com/videos/300.mp4'];

        foreach ($admin_videos as $key => $admin_video_details) {

            $admin_video_details->video_url = $vides_list[array_rand($vides_list)];
            
        }

        $response_array = ['success' => true, 'data' => $admin_videos];

        return response()->json($response_array, 200);



    }
	
}
