<?php

use App\Helpers\Helper;

use App\Helpers\EnvEditorHelper;

use App\Repositories\VideoRepository as VideoRepo;

use Carbon\Carbon;

use App\SubCategoryImage;

use App\AdminVideoImage;

use App\Category;

use App\SubCategory;

use App\Genre;

use App\Wishlist;

use App\AdminVideo;

use App\UserHistory;

use App\UserRating;

use App\User;

use App\MobileRegister;

use App\PageCounter;

use App\UserPayment;

use App\Settings;

use App\Flag;

use App\PayPerView;

use App\Language;

use App\SubProfile;

use App\Redeem;

use App\LikeDislikeVideo;

use App\Moderator;

use App\ContinueWatchingVideo;

use App\VideoCastCrew;

function moderator_details($id , $property = "") {

    $moderator_details = Moderator::find($id);

    if($property) {
        return $moderator_details ? $moderator_details->$property : "";
    }

    return $moderator_details;

}
/**
* Function Name: tr()
*
* Description : Language key file contents and default language
*
*/
function tr($key , $confirmation_content_lang_key = "") {

    if(Auth::guard('admin')->check()) {
        // $locale = config('app.locale');
        $locale = Setting::get('default_lang' , 'en');
        
    } else {
        
        if (!\Session::has('locale')) {

            // $locale = \Session::put('locale', config('app.locale'));
            $locale = Setting::get('default_lang' , 'en');
        }else {
            $locale = \Session::get('locale');
        }

    }
    
    return \Lang::choice('messages.'.$key, 0, Array('confirmation_content_lang_key' => $confirmation_content_lang_key), $locale);
}

function envfile($key) {

    $data = EnvEditorHelper::getEnvValues();

    if($data) {
        return $data[$key];
    }

    return "";
}

function sub_category_image($picture , $sub_category_id , $position, $path) {

    $image = new SubCategoryImage;

    $check_image = SubCategoryImage::where('sub_category_id' , $sub_category_id)->where('position' , $position)->first();

    if($check_image) {
        $image = $check_image;
    }

    $image->sub_category_id = $sub_category_id;
    $url = Helper::normal_upload_picture($picture, $path);
    $image->picture = $url ? $url : asset('admin-css/img/dummy.jpeg');
    $image->position = $position;
    $image->save();

    return true;
}

/*
function get_sub_category_image($sub_category_id) {

    $images = SubCategoryImage::where('sub_category_id' , $sub_category_id)
                    ->orderBy('position' , 'ASC')
                    ->get();

    return $images;

}*/

function get_categories() {

    $categories = Category::where('categories.is_approved' , 1)
                        ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                            'categories.is_series' ,'categories.status' , 'categories.is_approved')
                        ->leftJoin('admin_videos' , 'categories.id' , '=' , 'admin_videos.category_id')
                        // ->leftJoin('flags' , 'flags.video_id' , '=' , 'admin_videos.id')
                        ->where('admin_videos.status' , 1)
                        ->where('admin_videos.is_approved' , 1)
                        ->groupBy('admin_videos.category_id')
                        ->havingRaw("COUNT(admin_videos.id) > 0")
                        ->orderBy('name' , 'ASC')
                        ->get();
    return $categories;
}

function get_sub_categories($category_id) {

    $sub_categories = SubCategory::where('sub_categories.category_id' , $category_id)
                        ->select('sub_categories.id as id' , 'sub_categories.name' ,
                            'sub_categories.status' , 'sub_categories.is_approved')
                        ->leftJoin('admin_videos' , 'sub_categories.id' , '=' , 'admin_videos.sub_category_id')
                        ->leftJoin('sub_category_images' , 'sub_categories.id' , '=' , 'sub_category_images.sub_category_id')
                        ->select('sub_category_images.picture' , 'sub_categories.*')
                        ->where('sub_category_images.position' , 1)
                        ->groupBy('admin_videos.sub_category_id')
                        ->havingRaw("COUNT(admin_videos.id) > 0")
                        ->where('sub_categories.is_approved' , 1)
                        ->where('admin_videos.status' , 1)
                        ->orderBy('sub_categories.name' , 'ASC')
                        ->get();
    return $sub_categories;
}

function get_category_video_count($category_id) {

    $count = AdminVideo::where('category_id' , $category_id)
                    ->where('is_approved' , 1)
                    ->where('admin_videos.status' , 1)
                    ->count();

    return $count;
}

function get_video_fav_count($video_id) {

    $count = Wishlist::where('admin_video_id' , $video_id)
                ->leftJoin('admin_videos' ,'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->count();

    return $count;
}

function get_user_history_count($user_id) {
    $count = UserHistory::where('user_id' , $user_id)
                ->leftJoin('admin_videos' ,'user_histories.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->count();

    return $count;
}

function get_user_wishlist_count($user_id) {

    $count = Wishlist::where('user_id' , $user_id)
                ->leftJoin('admin_videos' ,'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->count();

    return $count;
}

function get_user_comment_count($user_id) {

    $count = UserRating::where('user_id' , $user_id)
                ->leftJoin('admin_videos' ,'user_ratings.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->count();

    return $count;

}

function get_video_comment_count($video_id) {

    $count = UserRating::where('admin_video_id' , $video_id)
                ->leftJoin('admin_videos' ,'user_ratings.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->count();

    return $count;

}

function total_video_count() {
    
    $count = AdminVideo::where('is_approved' , 1)->where('admin_videos.status' , 1)->count();

    return $count;

}

function get_sub_category_video_count($id) {
    
    $count = AdminVideo::where('sub_category_id' , $id)->where('admin_videos.status' , 1)->where('is_approved' , 1)->count();

    return $count;
}
function get_genre_video_count($id) {
    
    $count = AdminVideo::where('genre_id' , $id)->where('admin_videos.status' , 1)->where('is_approved' , 1)->count();

    return $count;
}

function get_sub_category_details($id) {

    $sub_category = SubCategory::where('id' , $id)->first();

    return $sub_category;
}

function get_genre_details($id) {

    $genre = Genre::where('id' , $id)->first();

    return $genre;
}

function get_genres($id) {

    $genres = Genre::where('sub_category_id' , $id)->where('is_approved'  , 1)->get();

    return $genres;
}

function get_youtube_embed_link($video_url) {

    if(strpos($video_url , 'embed')) {
        $video_url_id = explode('embed/', $video_url);
        if (count($video_url_id) == 2) {
            return "https://www.youtube.com/watch?v=".$video_url_id[1];
        }
    }

    return $video_url;

}

// Need to convert the youtube link to mobile acceptable format

function get_api_youtube_link($url) {

    $youtube_embed = $url;
    
    if($url) {

        // Ex : https://www.youtube.com/watch?v=jebJ9itYTJE 

        if(strpos($url, "=")) {

            $video_url_id = substr($url, strpos($url, "=") + 1);

            $youtube_embed = "https://www.youtube.com/embed/" . $video_url_id;

        } elseif(strpos($url , 'embed')) {

            $youtube_embed = $url;

        } else {

            // EX : https://youtu.be/2CLJuuKvou4

            if(strpos($url , "youtu.be")) {
                $youtube_embed = str_replace("https://youtu.be/", "https://www.youtube.com/embed/" , $url );
            }
        }
    }

    return $youtube_embed;

}


function get_video_end($video_url) {
    $url = explode('/',$video_url);
    $result = end($url);
    return $result;
}

function get_video_end_smil($video_url) {
    $url = explode('/',$video_url);
    $result = end($url);
    if ($result) {
        $split = explode('.', $result);
        if (count($split) == 2) {
            $result = $split[0];
        }
    }
    return $result;
}


function get_video_image($video_id)
{
    $video_image = AdminVideoImage::where('admin_video_id',$video_id)->orderBy('position','ASC')->get();
    return $video_image;
}

function wishlist($user_id) {

    $videos = Wishlist::where('user_id' , $user_id)
                    ->leftJoin('admin_videos' ,'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                    ->leftJoin('categories' ,'admin_videos.category_id' , '=' , 'categories.id')
                    ->where('admin_videos.is_approved' , 1)
                    ->select(
                            'wishlists.id as wishlist_id',
                            'admin_videos.id as admin_video_id' ,
                            'admin_videos.title','admin_videos.description' ,
                            'default_image','admin_videos.watch_count',
                            'admin_videos.default_image',
                            'admin_videos.ratings',
                            'admin_videos.duration',
                            DB::raw('DATE_FORMAT(admin_videos.publish_time , "%e %b %y") as publish_time') , 'categories.name as category_name')
                    ->orderby('wishlists.created_at' , 'desc')
                    ->skip(0)->take(10)
                    ->get();

    if(!$videos) {
        $videos = array();
    }

    return $videos;
}

function trending() {

    $videos = AdminVideo::where('watch_count' , '>' , 0)
                    ->select(
                        'admin_videos.id as admin_video_id' , 
                        'admin_videos.title',
                        'admin_videos.description',
                        'default_image','admin_videos.watch_count' , 
                        'admin_videos.publish_time',
                        'admin_videos.default_image',
                        'admin_videos.ratings'
                        )
                    ->orderby('watch_count' , 'desc')
                    ->skip(0)->take(10)
                    ->get();

    return $videos;
}

function category_video_count($category_id)
{
    $category_video_count = AdminVideo::where('category_id',$category_id)->where('is_approved' , 1)->count();
    return $category_video_count;
}

function sub_category_videos($sub_category_id, $web = null, $skip = 0, $take = 0, $sub_id = null) 
{

    $videos_query = AdminVideo::where('admin_videos.is_approved' , 1)
                ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                ->where('admin_videos.sub_category_id' , $sub_category_id)
                ->where('admin_videos.status', 1)
                ->select(
                    'admin_videos.id as admin_video_id' , 
                    'admin_videos.default_image' , 
                    'admin_videos.ratings' , 
                    'admin_videos.watch_count' , 
                    'admin_videos.title' ,
                    'admin_videos.description',
                    'admin_videos.sub_category_id' , 
                    'admin_videos.category_id',
                    'categories.name as category_name',
                    'sub_categories.name as sub_category_name',
                    'admin_videos.duration',
                    DB::raw('DATE_FORMAT(admin_videos.publish_time , "%e %b %y") as publish_time')
                    )
                ->orderby('admin_videos.sub_category_id' , 'asc');
    if ($sub_id) {
        // Check any flagged videos are present
        $flagVideos = getFlagVideos($sub_id);

        if($flagVideos) {
            $videos_query->whereNotIn('admin_videos.id', $flagVideos);
        }
    }

    if($web) {
        $videos = $videos_query->paginate(12);
    } else {
        $videos = $videos_query->skip($skip)->take($take)->get();
    }

    $videos = $videos_query->get();

    if(!$videos) {
        $videos = array();
    }

    return $videos;
} 

function change_theme($old_theme,$new_theme) {

    Log::info('The Request came inside of the \'change_theme\' function');

    \Artisan::call('view:clear');

    \Artisan::call('cache:clear');

    /** View Folder change **/

    if(file_exists(base_path('resources/views/user'))) {

        Log::info('Change old theme as original theme ');

        // Change current theme as original theme 

        $current_path=base_path('resources/views/user');
        $new_path=base_path('resources/views/'.$old_theme);

        rename($current_path,$new_path);

        Log::info('Old theme changed');

    }

    if(file_exists(base_path('resources/views/'.$new_theme))) {

        Log::info('make the user requested theme as the current theme');

        // make the user requested theme as the current theme

        $current_path=base_path('resources/views/'.$new_theme);
        $new_path=base_path('resources/views/user');

        rename($current_path,$new_path); 

        Log::info('Current theme changed');

    }

    /** View Folder change **/

    /** Layout User Folder change **/

    if(file_exists(base_path('resources/views/layouts/user'))) {

        Log::info('Change old theme as original theme ');

        // Change current theme as original theme 

        $current_path=base_path('resources/views/layouts/user');
        $new_path=base_path('resources/views/layouts/'.$old_theme);

        rename($current_path,$new_path);

        Log::info('Old theme changed');

    }

    if(file_exists(base_path('resources/views/layouts/'.$new_theme))) {

        Log::info('make the user requested theme as the current theme');

        // make the user requested theme as the current theme

        $current_path=base_path('resources/views/layouts/'.$new_theme);
        $new_path=base_path('resources/views/layouts/user');

        rename($current_path,$new_path); 

        Log::info('Current theme changed');

    }

    /** Layout User Folder change **/

    /** User file change **/

    if(file_exists(base_path('resources/views/layouts/user.blade.php'))) {

        Log::info('Change old theme as original theme ');

        // Change current theme as original theme 

        $current_path=base_path('resources/views/layouts/user.blade.php');
        $new_path=base_path('resources/views/layouts/'.$old_theme.'.blade.php');

        rename($current_path,$new_path);

        Log::info('Old theme changed');

    }

    if(file_exists(base_path('resources/views/layouts/'.$new_theme.'.blade.php'))) {

        Log::info('make the user requested theme as the current theme');

        // make the user requested theme as the current theme

        $current_path=base_path('resources/views/layouts/'.$new_theme.'.blade.php');
        $new_path=base_path('resources/views/layouts/user.blade.php');

        rename($current_path,$new_path); 

        Log::info('Current theme changed');

    }

    /** User file change **/

}

function register_mobile($device_type) {
    if($reg = MobileRegister::where('type' , $device_type)->first()) {
        $reg->count = $reg->count + 1;
        $reg->save();
    }
    
}

/**
 * Function Name : subtract_count()
 *
 * While Delete user, subtract the count from mobile register table based on the device type
 *
 * @param string $device_ype : Device Type (Andriod,web or IOS)
 * 
 * @return boolean
 */
function subtract_count($device_type) {

    if($reg = MobileRegister::where('type' , $device_type)->first()) {

        $reg->count = $reg->count - 1;
        
        $reg->save();
    }
}

function get_register_count() {

    $ios_count = MobileRegister::where('type' , 'ios')->first()->count;

    $android_count = MobileRegister::where('type' , 'android')->first()->count;

    $web_count = MobileRegister::where('type' , 'web')->first()->count;

    $total = $ios_count + $android_count + $web_count;

    return array('total' => $total , 'ios' => $ios_count , 'android' => $android_count , 'web' => $web_count);
}

function last_days($days){

  $views = PageCounter::orderBy('created_at','asc')->where('created_at', '>', Carbon::now()->subDays($days))->where('page','home');
  $arr = array();
  $arr['count'] = $views->count();
  $arr['get'] = $views->get();

  return $arr;

}
function counter($page){

    $count_home = PageCounter::wherePage($page)->where('created_at', '>=', new DateTime('today'));

        if($count_home->count() > 0){
            $update_count = $count_home->first();
            $update_count->count = $update_count->count + 1;
            $update_count->save();
        }else{
            $create_count = new PageCounter;
            $create_count->page = $page;
            $create_count->count = 1;
            $create_count->save();
        }
}

function get_recent_users() {
    $users = User::orderBy('created_at' , 'desc')->skip(0)->take(12)->get();

    return $users;
}
function get_recent_videos() {
    $videos = AdminVideo::orderBy('publish_time' , 'desc')->skip(0)->take(12)->get();

    return $videos;
}

/**
 * Function Name: total_revenue()
 *
 * @uses used to get the total admin revenue
 *
 * @created Vidhya R
 *
 * @updated Vidhya R
 *
 * @param -
 *
 */

function total_revenue() {

    $user_payments_revenue = UserPayment::sum('amount');

    $ppv_payments_revenue = PayPerView::sum('admin_amount'); 

    $total_revenue = $user_payments_revenue + $ppv_payments_revenue;
    
    return $total_revenue ? $total_revenue : 0.00;
}



function check_s3_configure() {

    $key = config('filesystems.disks.s3.key');

    $secret = config('filesystems.disks.s3.secret');

    $bucket = config('filesystems.disks.s3.bucket');

    $region = config('filesystems.disks.s3.region');

    if($key && $secret && $bucket && $region) {
        return 1;
    } else {
        return false;
    }
}

function get_slider_video() {
    return AdminVideo::where('is_home_slider' , 1)
            ->select('admin_videos.id as admin_video_id' , 'admin_videos.default_image',
                'admin_videos.title','admin_videos.trailer_video', 'admin_videos.video_type','admin_videos.video_upload_type')
            ->first();
}

function check_valid_url($file) {

    $video = get_video_end($file);

    // if(file_exists(public_path('uploads/'.$video))) {
        return 1;
    // } else {
    //     return 0;
    // }

}

function check_nginx_configure() {
    $nginx = shell_exec('nginx -V');
    if($nginx) {
        return true;
    } else {
        if(file_exists("/usr/local/nginx-streaming/conf/nginx.conf")) {
            return true;
        } else {
           return false; 
        }
    }
}

function check_php_configure() {
    return phpversion();
}

function check_mysql_configure() {

    $output = shell_exec('mysql -V');

    $data = 1;

    if($output) {
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version); 
        $data = $version[0];
    }

    return $data; 
}

function check_database_configure() {

    $status = 0;

    $database = config('database.connections.mysql.database');
    $username = config('database.connections.mysql.username');

    if($database && $username) {
        $status = 1;
    }
    return $status;

}

function check_settings_seeder() {
    return Settings::count();
}

function delete_install() {
    $controller = base_path('app/Http/Controllers/InstallationController.php');

    $public = base_path('public/install');
    
    $views = base_path('resources/views/install');

    if(is_dir($public)) {
        rmdir($public);
    }

    if(is_dir($views)) {
        rmdir($views);
    }

    if(file_exists($controller)) {
        unlink($controller);
    } 

    return true;
}

function get_banner_count() {
    return AdminVideo::where('is_banner' , 1)->count();
}

function get_expiry_days($id) {
    
    
    $data = UserPayment::where('user_id' , $id)->orderBy('created_at' , 'desc')->where('status' , 1)->first();
    
    $days = 0;

    if($data) {

        $user  = User::where('id',$id)->select('users.timezone')->first();
        
        if($user){

            $start_date = convertTimeToUSERzone(date('Y-m-d H:i:s'), $user->timezone);
            
        } else {

            $timezone = 'Asia/Kolkata';

            $start_date = convertTimeToUSERzone(date('Y-m-d H:i:s'), $timezone);
        }
        
        $end_date = $data->expiry_date;
     
        $days = (strtotime($end_date)- strtotime($start_date))/24/3600; 

        return round(abs($days));



        // $start_date = new \DateTime(date('Y-m-d h:i:s'));
        // $end_date = new \DateTime($data->expiry_date);

        // $time_interval = date_diff($start_date,$end_date);
        // $days = $time_interval->days;
    }

    return $days;
}

function all_videos($web = NULL , $skip = 0) 
{

    $videos_query = AdminVideo::where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                ->select(
                    'admin_videos.id as admin_video_id' , 
                    'admin_videos.default_image' , 
                    'admin_videos.ratings' , 
                    'admin_videos.watch_count' , 
                    'admin_videos.title' ,
                    'admin_videos.description',
                    'admin_videos.sub_category_id' , 
                    'admin_videos.category_id',
                    'categories.name as category_name',
                    'sub_categories.name as sub_category_name',
                    'admin_videos.duration',
                    DB::raw('DATE_FORMAT(admin_videos.publish_time , "%e %b %y") as publish_time')
                    )
                ->orderby('admin_videos.created_at' , 'desc');
    if (Auth::check()) {
        // Check any flagged videos are present
        $flagVideos = getFlagVideos(Auth::user()->id);

        if($flagVideos) {
            $videos_query->whereNotIn('admin_videos.id',$flagVideos);
        }
    }

    if($web) {
        $videos = $videos_query->paginate(20);
    } else {
        $videos = $videos_query->skip($skip)->take(20)->get();
    }

    return $videos;
} 

function get_trending_count() {

    $data = AdminVideo::where('watch_count' , '>' , 0)
                    ->where('admin_videos.is_approved' , 1)
                    ->where('admin_videos.status' , 1)
                    ->skip(0)->take(20)
                    ->count();

    return $data;

}

function get_wishlist_count($id) {
    
    $query = Wishlist::where('user_id' , $id)
                ->leftJoin('admin_videos' ,'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->where('wishlists.status' , 1);

    $flagVideos = getFlagVideos($id);
    
    if($flagVideos) {

        $query->whereNotIn('admin_video_id', $flagVideos);
    }

    $data = $query->count();

    return $data;

}

function get_suggestion_count($id) {

    $data = Wishlist::where('user_id' , $id)
                ->leftJoin('admin_videos' ,'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->where('wishlists.status' , 1)
                ->count();

    return $data;

}

function get_recent_count($id) {

    $data = Wishlist::where('user_id' , $id)
                ->leftJoin('admin_videos' ,'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->where('wishlists.status' , 1)
                ->count();

    return $data;

}

function get_history_count($id) {

    $data = UserHistory::where('user_id' , $id)
                ->leftJoin('admin_videos' ,'user_histories.admin_video_id' , '=' , 'admin_videos.id')
                ->where('admin_videos.is_approved' , 1)
                ->where('admin_videos.status' , 1)
                ->count();

    return $data;

}


//this function convert string to UTC time zone

function convertTimeToUTCzone($str, $userTimezone, $format = 'Y-m-d H:i:s') {

    $new_str = new DateTime($str, new DateTimeZone($userTimezone));

    $new_str->setTimeZone(new DateTimeZone('UTC'));

    return $new_str->format( $format);
}

//this function converts string from UTC time zone to current user timezone

function convertTimeToUSERzone($str, $userTimezone, $format = 'Y-m-d H:i:s') {

    if(empty($str)){
        return '';
    }
    try{
        $new_str = new DateTime($str, new DateTimeZone('UTC') );
        $new_str->setTimeZone(new DateTimeZone( $userTimezone ));
    }
    catch(\Exception $e) {
        // Do Nothing
    }
    
    return $new_str->format( $format);
}



/**
 * Function Name : getReportVideoTypes()
 * Load all report video types in settings table
 *
 * @return array of values
 */ 
function getReportVideoTypes() {
    // Load Report Video values
    $model = Settings::where('key', REPORT_VIDEO_KEY)->get();
    // Return array of values
    return $model;
}

/**
 * Function Name : getFlagVideos()
 * To load the videos based on the user
 *
 * @param int $id User Id
 *
 * @return array of values
 */
function getFlagVideos($sub_profile_id) {
    // Load Flag videos based on logged in user id
    $model = Flag::where('sub_profile_id', $sub_profile_id)
        ->leftJoin('admin_videos' , 'flags.video_id' , '=' , 'admin_videos.id')
        ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
        ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
        ->where('admin_videos.is_approved' , 1)
        ->where('admin_videos.status' , 1)
        ->pluck('video_id')->toArray();
    // Return array of id's
    return $model;
}


/**
 * Function Name : continueWatchingVideos()
 * To load the videos based on the user
 *
 * @param int $id User Id
 *
 * @return array of values
 */
function continueWatchingVideos($sub_profile_id) {
    // Load Flag videos based on logged in user id
    $model = ContinueWatchingVideo::where('sub_profile_id', $sub_profile_id)
        ->leftJoin('admin_videos' , 'continue_watching_videos.admin_video_id' , '=' , 'admin_videos.id')
        ->where('admin_videos.is_approved' , 1)
        ->where('admin_videos.status' , 1)
        ->pluck('continue_watching_videos.admin_video_id')->toArray();
    // Return array of id's
    return $model;
}


/**
 * Function Name : getFlagVideosCnt()
 * To load the videos cnt based on the user 
 *
 * @param int $id User Id
 *
 * @return cnt
 */
function getFlagVideosCnt($id) {
    // Load Flag videos based on logged in user id
    $model = Flag::where('user_id', $id)
        ->leftJoin('admin_videos' , 'flags.video_id' , '=' , 'admin_videos.id')
        ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
        ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
        ->where('admin_videos.is_approved' , 1)
        ->where('admin_videos.status' , 1)
        ->count();
    // Return array of id's
    return $model;
}


/**
 * Function Name : watchFullVideo()
 * To check whether the user has to pay the amount or not
 * 
 * @param integer $user_id User id
 * @param integer $user_type User Type
 * @param integer $video_id Video Id
 * 
 * @return true or not
 */
function watchFullVideo($user_id, $user_type, $video) {

    // Check the video Amount zero means

    if ($video->amount == 0 || $video->is_pay_per_view == DEFAULT_FALSE) {
        return true;
    }

    if ($user_type) {

       if($video->amount > 0 && ($video->type_of_user == PAID_USER || $video->type_of_user == BOTH_USERS)) {
            
            $paymentView = PayPerView::where('user_id', $user_id)
                ->where("video_id",$video->admin_video_id)
                ->orderBy('id', 'desc')
                ->where('status', DEFAULT_TRUE)
                ->first();

            if ($paymentView) {

                if ($video->type_of_subscription == ONE_TIME_PAYMENT) {
                    // Load Payment view
                    return true;
                    
                } else {

                    if ($paymentView->is_watched == DEFAULT_FALSE) {
                        return true;
                    }
                       
                }

            }

        } else if($video->amount > 0 && $video->type_of_user == NORMAL_USER){

            return true;
        }
   
    } else {

        if($video->amount > 0 && ($video->type_of_user == NORMAL_USER || $video->type_of_user == BOTH_USERS)) {

            $paymentView = PayPerView::where('user_id', $user_id)
            ->whereRaw("video_id", $video->admin_video_id)
            ->where('status', DEFAULT_TRUE)
            ->orderBy('id', 'desc')->first();

            if ($paymentView) {

                if ($video->type_of_subscription == ONE_TIME_PAYMENT) {
                    // Load Payment view
                        return true;
                    
                } else {

                    if ($paymentView->is_watched == DEFAULT_FALSE) {

                        return true;

                    }
                    
                }

            }

        }  else if($video->amount > 0 && $video->type_of_user == PAID_USER) {
            
            return true;

        }
    }
    
    return false;
}

/**
 * Function Name : total_video_revenue
 * To sum all the payment based on video subscription
 *
 * @return amount
 */
function total_video_revenue() {
    
    return PayPerView::sum('amount');
}

/**
* FUnction Name: total_moderator_video_revenue()
*
* Description: Get the total moderator ppv video revenues details
*
* @param Moderator id
* 
* @return Moderator video revenue
*/
function total_moderator_video_revenue($id) {

    $total_moderator_video_revenue =  PayPerView::leftJoin('admin_videos', 'admin_videos.id', '=', 'video_id')
            ->where('admin_videos.uploaded_by', $id)
            ->where('pay_per_views.amount', '>' , 0)
            ->sum('pay_per_views.moderator_amount');

    return $total_moderator_video_revenue ?: 0.00;
}

function redeem_amount($id) {
    return AdminVideo::where('admin_videos.uploaded_by',$id)
            ->sum('admin_videos.redeem_amount');

}


/**
 * Function Name : user_total_amount
 * To sum all the payment based on video subscription
 *
 * @return amount
 */
function user_total_amount() {
    return PayPerView::where('user_id', Auth::user()->id)->sum('amount');
}


/**
 * Function Name : getImageResolutions()
 * Load all image resoltions types in settings table
 *
 * @return array of values
 */ 
function getImageResolutions() {
    // Load Report Video values
    $model = Settings::where('key', IMAGE_RESOLUTIONS_KEY)->get();
    // Return array of values
    return $model;
}

/**
 * Function Name : getVideoResolutions()
 * Load all video resoltions types in settings table
 *
 * @return array of values
 */ 
function getVideoResolutions() {
    // Load Report Video values
    $model = Settings::where('key', VIDEO_RESOLUTIONS_KEY)->get();
    // Return array of values
    return $model;
}

/**
 * Function Name : convertMegaBytes()
 * Convert bytes into mega bytes
 *
 * @return number
 */
function convertMegaBytes($bytes) {
    return number_format($bytes / 1048576, 2);
}

/**
 * Function Name : get_video_attributes()
 * To get video Attributes
 *
 * @param string $video Video file name
 *
 * @return attributes
 */
function get_video_attributes($video) {

    $command = 'ffmpeg -i ' . $video ;

    Log::info("Path ".$video);

    $output = shell_exec($command);

    Log::info("Shell Exec : ".$output);

    $codec = null; $width = null; $height = null;

    $regex_sizes = "/Video: ([^,]*), ([^,]*), ([0-9]{1,4})x([0-9]{1,4})/";

    Log::info("Preg Match :" .preg_match($regex_sizes, $output, $regs));

    if (preg_match($regex_sizes, $output, $regs)) {
        $codec = $regs [1] ? $regs [1] : null;
        $width = $regs [3] ? $regs [3] : null;
        $height = $regs [4] ? $regs [4] : null;
    }



    $hours = $mins = $secs = $ms = null;
    
    $regex_duration = "/Duration: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}).([0-9]{1,2})/";
    if (preg_match($regex_duration, $output, $regs)) {
        $hours = $regs [1] ? $regs [1] : null;
        $mins = $regs [2] ? $regs [2] : null;
        $secs = $regs [3] ? $regs [3] : null;
        $ms = $regs [4] ? $regs [4] : null;
    }

    Log::info("Width of the video : ".$width);
    Log::info("Height of the video : ".$height);


    return array('codec' => $codec,
        'width' => $width,
        'height' => $height,
        'hours' => $hours,
        'mins' => $mins,
        'secs' => $secs,
        'ms' => $ms
    );
}

/**
 * Function Name : get_video_resoltuions()
 * To get video Attributes width*height
 *
 * @param string $video Video file name
 *
 * @return attributes
 */
function get_video_resolutions($video) {

    $command = "ffmpeg -i ".$video ." 2>&1 | grep -oP 'Stream .*, \K[0-9]+x[0-9]+'";

    Log::info("Path ".$command);

    $output = shell_exec($command);

    Log::info("Shell Exec : ".$output);

    // Return resolutions of video

    return $output;
}
/**
 * Function Name :readFile()
 * To read a input file and get attributes
 * 
 * @param string $inputFile File name
 *
 * @return $attributes
 */
function readFileName($inputFile) {

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $video_attributes = [];

    if (file_exists($inputFile)) {

        $mime_type = finfo_file($finfo, $inputFile); // check mime type

        finfo_close($finfo);
        
        if (preg_match('/video\/*/', $mime_type)) {

            Log::info("Inside ffmpeg");

            // $video_attributes = get_video_attributes($inputFile, 'ffmpeg');

            $video_attributes = get_video_resolutions($inputFile);
        } 

    }

    return $video_attributes;
}

function getResolutionsPath($video, $resolutions, $streaming_url) {

    $video_resolutions = ($streaming_url) ? [$streaming_url.Setting::get('original_key').get_video_end($video)] : [$video];

    $pixels = ['Original'];
    $exp = explode('original/', $video);

    if (count($exp) == 2) {
        if ($resolutions) {
            $split = explode(',', $resolutions);
            foreach ($split as $key => $resoltuion) {
                $streamUrl = ($streaming_url) ? $streaming_url.Setting::get($resoltuion.'_key').$exp[1] : $exp[0].$resoltuion.'/'.$exp[1];
                array_push($video_resolutions, $streamUrl);
                $splitre = explode('x', $resoltuion);
                array_push($pixels, $splitre[1].'p');
            }
        }
    }
    $video_resolutions = implode(',', $video_resolutions);

    $pixels = implode(',', $pixels);
    return ['video_resolutions' => $video_resolutions, 'pixels'=> $pixels];
}


function deleteVideoAndImages($video) {
    if ($video->video_type == VIDEO_TYPE_UPLOAD ) {
        if($video->video_upload_type == VIDEO_UPLOAD_TYPE_s3) {
            Helper::s3_delete_picture($video->video);   
            Helper::s3_delete_picture($video->trailer_video);  
        } else {
            $videopath = '/uploads/videos/original/';
            Helper::delete_picture($video->video, $videopath); 
            $splitVideos = ($video->video_resolutions) 
                        ? explode(',', $video->video_resolutions)
                        : [];
            foreach ($splitVideos as $key => $value) {
               Helper::delete_picture($video->video, $videopath.$value.'/');
            }

            Helper::delete_picture($video->trailer_video, $videopath);
            // @TODO
            $splitTrailer = ($video->trailer_video_resolutions) 
                        ? explode(',', $video->trailer_video_resolutions)
                        : [];
            foreach ($splitTrailer as $key => $value) {
               Helper::delete_picture($video->trailer_video, $videopath.$value.'/');
            }
        }
    }

    if($video->default_image) {
        Helper::delete_picture($video->default_image, "/uploads/images/");
    }

    if($video->is_banner == 1) {
        if($video->banner_image) {
            Helper::delete_picture($video->banner_image, "/uploads/images/");
        }
    }
}

/**
 * Check the default subscription is enabled by admin
 *
 */

function user_type_check() {

    // User need subscripe the plan

    if(Setting::get('is_subscription')) {

       return 1;

    } else {

        // Enable the user as paid user
    
        return 0;
        
    }

}

function readFileLength($file)  {

    $variableLength = 0;
    if (($handle = fopen($file, "r")) !== FALSE) {
         $row = 1;
         while (($data = fgetcsv($handle, 1000, "\n")) !== FALSE) {
            $num = count($data);
            $row++;
            for ($c=0; $c < $num; $c++) {
                $exp = explode("=>", $data[$c]);
                if (count($exp) == 2) {
                    $variableLength += 1; 
                }
            }
        }
        fclose($handle);
    }

    return $variableLength;
}

function getActiveLanguages() {
    return Language::where('status', DEFAULT_TRUE)->get();
}

/**
 * Function Name : displayFullDetails()
 *
 * To display main details for web and mobile (Common function)
 *
 * @created_by shobana chandrasekar
 *
 * @updated_by shobana chandrasekar
 *
 * @param mixed - $id -> Video Id, $user_id -> User Id, $user_type -> User Type, $device_type -> Device Type (Web, android, IOS)
 *
 * @return response of video details
 */
function displayFullDetails($id, $user_id, $user_type, $device_type, $sub_profile_id = 0) {

    $details = [];

    $video = AdminVideo::videoResponse()
            ->leftjoin('categories', 'categories.id','=', 'admin_videos.category_id')
            ->leftjoin('sub_categories', 'sub_categories.id','=', 'admin_videos.sub_category_id')
            ->leftjoin('genres', 'genres.id','=', 'admin_videos.genre_id')
            ->where('admin_videos.is_approved', VIDEO_APPROVED)
            ->where('admin_videos.status', VIDEO_PUBLISHED)
            ->where('categories.is_approved', CATEGORY_APPROVED)
            ->where('sub_categories.is_approved', SUB_CATEGORY_APPROVED)
            ->where('admin_videos.id', $id)
            ->withCount('getLikeCount')
            ->withCount('getDisLikeCount')
            ->first();

    if($video) {

        $wishlist_status = $history_status = $like_status = $ppv_status = 0;

        $sub_videos = $genres = $genre_names = $first_part = $video_cast_crews = [];

        $is_ppv_status = DEFAULT_TRUE;

        $is_saved = "";

        if ($user_id) {

            $wishlist_status = Helper::wishlist_status($id, $sub_profile_id);

            $history_status = Helper::history_status($sub_profile_id, $id);

            $like_status = Helper::like_status($sub_profile_id,$video->id);

            $is_ppv_status = ($video->type_of_user == NORMAL_USER || $video->type_of_user == BOTH_USERS) ? ( ( $user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

            $ppv_status = VideoRepo::pay_per_views_status_check($user_id, $user_type, $video)->getData();

            $is_saved = Helper::download_status($id, $user_id);

        }

        if ($device_type == DEVICE_WEB) {

            $sub_category_videos = Helper::recently_video(4);

            foreach ($sub_category_videos as $key => $value) {

                $ppv_status = VideoRepo::pay_per_views_status_check($user_id, $user_type, $value)->getData();

                $sub_videos[] = [
                        'title'=>$value->title,
                        'description'=>$value->description,
                        'ratings'=>$value->ratings,
                        'publish_time'=>date('M Y', strtotime($value->publish_time)),
                        'duration'=>$value->duration,
                        'watch_count'=>$value->watch_count,
                        'default_image'=>sliderImage($value),
                        'admin_video_id'=>$value->admin_video_id,
                        'pay_per_view_status'=>$ppv_status->success,
                        'ppv_details'=>$ppv_status,
                        'amount'=>$value->amount,
                        'age'=>$video->age > 0 ? $video->age : '18+',
                        'currency'=>Setting::get('currency')
                    ];
            
            }

            if ($video->genre_id > 0) {

                $genre_trailer_videos = Genre::where('sub_category_id' , $video->sub_category_id)
                            ->select(
                                    'genres.id as genre_id',
                                    'genres.name as genre_name',
                                    'genres.video',
                                    'genres.image'
                                    )
                            ->where('is_approved', DEFAULT_TRUE)
                            ->get();


                foreach ($genre_trailer_videos as $key => $genre) {

                    $genre_names[] = ['genre_name'=>$genre->genre_name, 'genre_id'=>$genre->genre_id];

                    if($key <= 4) {

                        $genres[] = ['genre_id'=>$genre->genre_id, 'genre_name'=>$genre->genre_name, 'genre_video'=>$genre->video, 'genre_image'=>$genre->image, 'genre_subtitle'=>$genre->subtitle];

                    }
                    # code...
                }

                $seasons = AdminVideo::where('genre_id', $video->genre_id)
                                ->where('admin_videos.status' , 1)
                                ->where('admin_videos.is_approved' , 1)
                                ->skip(0)
                                ->take(4)
                                ->get();
                foreach ($seasons as $key => $value) {

                    $ppv_status = VideoRepo::pay_per_views_status_check($user_id, $user_type, $value)->getData();

                    $first_part[] = [
                            'title'=>$value->title,
                            'description'=>$value->description,
                            'ratings'=>$value->ratings,
                            'publish_time'=>date('M Y', strtotime($value->publish_time)),
                            'duration'=>$value->duration,
                            'watch_count'=>number_format_short($value->watch_count),
                            'default_image'=>sliderImage($value),
                            'admin_video_id'=>$value->id,
                            'pay_per_view_status'=>$ppv_status->success,
                            'ppv_details'=>$ppv_status,
                            'amount'=>$value->amount,
                            'age'=>$video->age > 0 ? $video->age : '18+',
                            'currency'=>Setting::get('currency')
                        ];
                }

            }

            $video_cast_crews = VideoCastCrew::select('cast_crew_id', 'name')
                    ->where('admin_video_id', $video->admin_video_id)
                    ->leftjoin('cast_crews', 'cast_crews.id', '=', 'video_cast_crews.cast_crew_id')
                    ->get()
                    ->toArray();


        }

        $video_images = get_video_image($video->admin_video_id);

        $images = [$video->default_image];

        foreach ($video_images as $key => $value) {
            # code...
            array_push($images, $value->image);
        }

        $details = [
            'admin_video_id'=>$video->admin_video_id,
            'title'=>$video->title,
            'category_name'=>$video->category_name,
            'genre_name'=>$video->genre_name ? $video->genre_name : '',
            'sub_category_name'=>$video->sub_category_name,
            'amount'=>$video->amount,
            'ratings'=>$video->ratings,
            'pay_per_view_status'=>$ppv_status ? $ppv_status->success : false,
            'ppv_details'=>$ppv_status,
            'publish_time'=>date('M Y', strtotime($video->publish_time)),
            'duration'=>$video->duration,
            'watch_count'=>number_format_short($video->watch_count),
            'default_image'=>$video->default_image, 
            'mobile_image'=> $video->mobile_image ?: $video->default_image, 
            'images'=>$images,
           // 'default_image'=>sliderImage($video), 
          //  'video_subtitle'=>$video->video_subtitle,
           // 'trailer_subtitle'=>$video->trailer_subtitle,
            'trailer_embed_link'=>route('embed_video', array('v_t'=>2, 'u_id'=>$video->unique_id)),
            'video_embed_link'=>route('embed_video', array('v_t'=>1, 'u_id'=>$video->unique_id)),
            'trailer_video'=>$video->trailer_video,
            'video'=>$video->video,
            'is_genre'=>$video->is_series,
            'is_kids_video'=>$video->is_kids_video,
            'genre_id'=>$video->genre_id ? $video->genre_id : DEFAULT_FALSE,
            'wishlist_status'=>$wishlist_status,
            'history_status'=>$history_status,
            'is_liked'=>$like_status,
            'like_count'=>$video->get_like_count_count ? number_format_short($video->get_like_count_count) : 0,
            'dis_like_count'=>$video->get_dis_like_count_count ? number_format_short($video->get_dis_like_count_count) : 0,
            'is_ppv_subscribe_page'=>$is_ppv_status, // 0 - Dont shwo subscribe+ppv_ page 1- Means show ppv subscribe page
            'age'=>$video->age > 0 ? $video->age : '18+',
            'currency'=>Setting::get('currency'),
            'description'=>$video->description,
            'details'=>$video->details,
            'cast_crews'=>$video_cast_crews,
            'video_gif_image'=>$video->video_gif_image ? $video->video_gif_image : $video->default_image,
            'sub_videos'=>$sub_videos,
            'genres'=>$genres,
            'first_part'=>$first_part,
            'genre_names'=>$genre_names,
            'download_status'=>$video->download_status, // Download Status used to display option to the user / not
            'download_link'=>$video->video,

            'is_saved'=>$is_saved ? $is_saved->status : VIDEO_NOT_DOWNLOADED, // The video downloaded by user /not,
            'video_save_status_text'=>$is_saved ? downloadStatus($is_saved->download_status) : "",

            'video_save_status'=>$is_saved ? $is_saved->download_status : 0,
        ];


    } else {

        Log::info("Video not found ... ".$id);
    }

    return $details;

}

function convertToBytes($from){
    $number=substr($from,0,-2);
    switch(strtoupper(substr($from,-2))){
        case "KB":
            return $number*1024;
        case "MB":
            return $number*pow(1024,2);
        case "GB":
            return $number*pow(1024,3);
        case "TB":
            return $number*pow(1024,4);
        case "PB":
            return $number*pow(1024,5);
        default:
            return $from;
    }
}

function checkSize() {

    $php_ini_upload_size = convertToBytes(ini_get('upload_max_filesize')."B");

    $php_ini_post_size = convertToBytes(ini_get('post_max_size')."B");

    $setting_upload_size = convertToBytes(Setting::get('upload_max_size')."B");

    $setting_post_size = convertToBytes(Setting::get('post_max_size')."B");

    if(($php_ini_upload_size < $setting_upload_size) || ($php_ini_post_size < $setting_post_size)) {

        return true;

    }

    return false;
}


// Based on the request type, it will return string value for that request type

function redeem_request_status($status) {
    
    if($status == REDEEM_REQUEST_SENT) {
        $string = tr('REDEEM_REQUEST_SENT');
    } elseif($status == REDEEM_REQUEST_PROCESSING) {
        $string = tr('REDEEM_REQUEST_PROCESSING');
    } elseif($status == REDEEM_REQUEST_PAID) {
        $string = tr('REDEEM_REQUEST_PAID');
    } elseif($status == REDEEM_REQUEST_CANCEL) {
        $string = tr('REDEEM_REQUEST_CANCEL');
    } else {
        $string = tr('REDEEM_REQUEST_SENT');
    }

    return $string;
}

/**
 * Function : add_to_redeem()
 * 
 * @param $id = role ID
 *
 * @param $amount = earnings
 *
 * @uses : If the role earned any amount, use this function to update the redeems
 *
 */

function add_to_redeem($id , $amount , $admin_amount = 0) {

    \Log::info('Add to Redeem Start');

    if($id && $amount) {

        $redeems_details = Redeem::where('moderator_id' , $id)->first();

        if(!$redeems_details) {

            $redeems_details = new Redeem;

            $redeems_details->moderator_id = $id;
        
        }

        $redeems_details->total = $redeems_details->total + $amount;

        $redeems_details->remaining = $redeems_details->remaining+$amount;

        // Update the earnings for moderator and admin amount

        $redeems_details->total_admin_amount = $redeems_details->total_admin_amount + $admin_amount;

        $redeems_details->total_moderator_amount = $redeems_details->total_moderator_amount + $amount;
        
        $redeems_details->save();
   
    }

    \Log::info('Add to Redeem End');
}

function admin_commission($id) {

    $video = AdminVideo::where('uploaded_by', $id)->sum('admin_amount');

    return $video ? $video : 0;
}

function moderator_commission($id) {

    $video = AdminVideo::where('uploaded_by', $id)->sum('user_amount');

    return $video ? $video : 0;
}


function number_format_short( $n = 0 , $precision = 1 ) {

    $n = (int) $n;
    
    if ($n < 900) {
        // 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } else if ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } else if ($n < 900000000) {
        // 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } else if ($n < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }
  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ( $precision > 0 ) {
        $dotzero = '.' . str_repeat( '0', $precision );
        $n_format = str_replace( $dotzero, '', $n_format );
    }
    return $n_format . $suffix;
}


function check_flag_video($admin_video_id, $sub_profile_id) {

    $model = Flag::where('sub_profile_id', $sub_profile_id)->where('video_id', $admin_video_id)->first();

    return $model ? DEFAULT_TRUE : DEFAULT_FALSE;

}


function seoUrl($string) {
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}


function active_plan($id) {
   
    $model = UserPayment::where('user_id', $id)
              
                ->where('status', DEFAULT_TRUE)
                ->orderBy('created_at', 'desc')->first();
  
    return $model ? ($model->subscription ? $model->subscription->title : '-') : '-';

}

function videoPlayDuration($admin_video_id, $sub_profile_id) {

    $model = ContinueWatchingVideo::where('admin_video_id', $admin_video_id)->where('sub_profile_id', $sub_profile_id)->first();

    $duration = $model ? $model->duration : "";

    return $duration;
}


function seek($time) {

    $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);

    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

    $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;

    return $time_seconds;
}


function getTemplateName($template) {

    $template_type_labels = [
        USER_WELCOME => tr('user_welcome_email'), 
        ADMIN_USER_WELCOME => tr('admin_created_user_welcome_mail'), 
        FORGOT_PASSWORD => tr('forgot_password'), 
        MODERATOR_WELCOME => tr('moderator_welcome'), 
        PAYMENT_EXPIRED => tr('payment_expired'), 
        PAYMENT_GOING_TO_EXPIRY => tr('payment_going_to_expiry'), 
        NEW_VIDEO => tr('new_video'), 
        EDIT_VIDEO => tr('edit_video'),
        AUTOMATIC_RENEWAL => tr('automatic_renewal_notification'),
        MODERATOR_UPDATE_MAIL => tr('email_change_notification')];

    return isset($template_type_labels[$template]) ? $template_type_labels[$template] : '-';
}

function amount_convertion($percentage, $amt) {

    $converted_amt = $amt * ($percentage/100);

    return $converted_amt;
}

/**
 *
 * Function check_token_expiry()
 *
 * @usage - used to check the user token expiry and generate token
 *
 * @created Vidhya R
 *
 * @edited Vidhya R
 *
 * @param integer user_id
 *
 * @return -
 */

function check_token_expiry($user_id) {

    Log::info("check_token_expiry".$user_id);

    $user_details = User::find($user_id);

    if($user_details) {

        if($user_details->token_expiry <= time("H:i:s" , "+10minutes")) {

            $user_details->token_expiry = Helper::generate_token_expiry();

            $user_details->save();

            Log::info("TOKEN EXPIRY EXTENDED");
        }

    }
}

/**
 * function routefreestring()
 * 
 * @uses used for remove the route parameters from the string
 *
 * @created vidhya R
 *
 * @edited vidhya R
 *
 * @param string $string
 *
 * @return Route parameters free string
 */

function routefreestring($string) {

    $search = array(' ', '&', '%', "?",'=','{','}','$');

    $replace = array('-', '-', '-' , '-', '-', '-' , '-','-');

    $string = str_replace($search, $replace, $string);

    return $string;
    
}

/**
 * Function Name : sliderImage()
 *
 * To get Slider image while hivering/showing
 *
 * @param string $filename - File name of the image
 *
 * @created_by shobana chandrasekar
 *
 * @updated_by -
 *
 * @return response of 385x225 path
 */
function sliderImage($video) {

    $path = "/uploads/images/";

    $image = public_path() . $path . basename($video->default_image);

    if (file_exists($image)) {
    // "/uploads/"
       $image = public_path() . $path.'385x225/' . basename($video->default_image);

        if(file_exists($image)) {

            return Helper::web_url().$path.'385x225/' . basename($video->default_image);

        } else {


            return Helper::web_url().$path. basename($video->default_image);
        }

    }   

    return "";
}

/**
 * Function Name : showEntries()
 *
 * To load the entries of the row
 *
 * @created_by Shobana Chandrasekar 
 *
 * @updated_by -- 
 *
 * @return reponse of serial number
 */
function showEntries($request, $i) {

    $s_no = $i;

    // Request Details + s.no

    if (isset($request['page'])) {

        $s_no = (($request['page'] * 10) - 10 ) + $i;

    }

    return $s_no;

}


function mobileResponseDetails($id, $user_id, $sub_profile_id) {

    $video = AdminVideo::find($id);

    $wishlist_status = Helper::wishlist_status($id, $user_id);

    $is_saved = Helper::download_status($id, $user_id); // User ID

    $current_date = date('Y-m-d');

    $diff_bw_days = calculateDays($current_date,$is_saved->expiry_date);

    $details = [
            'admin_video_id'=>$video->id,
            'title'=>$video->title,
            'description'=>$video->description,
            'publish_time'=>date('M Y', strtotime($video->publish_time)),
            'duration'=>$video->duration,
            'watch_count'=>number_format_short($video->watch_count),
            'wishlist_status'=>$wishlist_status,
            'default_image'=>sliderImage($video), 
            'is_saved'=>$is_saved->status,
            'video_save_status'=>$is_saved->download_status,
            'video_save_status_text'=>downloadStatus($is_saved->download_status),
            'download_link'=>$video->video,
            'download_date'=>$is_saved->download_date,
            'expiry_date'=>$is_saved->expiry_date,
            'is_expired'=>$is_saved->is_expired,
            'no_of_days'=>$diff_bw_days > 0 ? $diff_bw_days : 0,
            'is_spam' => check_flag_video($video->id, $sub_profile_id)


    ];

    return $details;

}
function downloadStatus($status) {

    $text = "";

    switch ($status) {

        case DOWNLOAD_INITIATE_STAUTS:
                
            $text = tr('download_initiated');

            break;

        case DOWNLOAD_PROGRESSING_STAUTS:
                
            $text = tr('download_progressing');

            break;

        case DOWNLOAD_PAUSE_STAUTS:
                
            $text = tr('download_paused');

            break;

        case DOWNLOAD_COMPLETE_STAUTS:
                
            $text = tr('download_completed');

            break;

        case DOWNLOAD_CANCEL_STAUTS:
                
            $text = tr('download_cancelled');

            break;
        
        default:
            $text = "";

            break;
    }

    return $text;

}



function calculateDays($start_date, $end_date) {

    $end_date = strtotime($end_date); 

    $start_date = strtotime($start_date);

    $datediff = $end_date - $start_date;

    return round($datediff / (60 * 60 * 24));

}

function type_of_subscription($type_of_subscription) {
    
    return $type_of_subscription == RECURRING_PAYMENT ? tr('API_RECURRING_PAYMENT') : tr('API_ONE_TIME_PAYMENT');
}

function type_of_user($type_of_user) {
    
    if ($type_of_user == NORMAL_USER) {

        $string =  tr('normal_users');

    } else if($type_of_user == PAID_USER) {

        $string = tr('paid_users');

    } else if($type_of_user == BOTH_USERS) {

        $string = tr('both_users');
    } else {
        $string =  '';
    }

    return $string;

}

