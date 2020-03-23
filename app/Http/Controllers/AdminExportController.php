<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Excel;

use App\User;

use App\Moderator;

use App\AdminVideo;

use App\Subscription;

use App\UserPayment;

use App\PayPerView;

use Exception;

use Setting;

class AdminExportController extends Controller
{

	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');  
    }
    
    /**
	 * Function Name: users_export()
	 *
	 * @usage used export the users details into the selected format
	 *
	 * @created Vidhya R
	 *
	 * @edited Vidhya R
	 *
	 * @param string format (xls, csv or pdf)
	 *
	 * @return redirect users page with success or error message 
	 */
    public function users_export(Request $request) {

    	try {

    		// Get the admin selected format for download

    		$format = $request->format ? $request->format : 'xls';

	    	$download_filename = routefreestring(Setting::get('site_name'))."-".date('Y-m-d-h-i-s')."-".uniqid();

	    	$result = User::orderBy('created_at' , 'desc')->get();

	    	// Check the result is not empty

	    	if(count($result) == 0) {
            	
            	return redirect()->route('admin.users.index')->with('flash_error' , tr('no_user_found'));
	    	}

	    	Excel::create($download_filename, function($excel) use($result)
		    {

		        $excel->sheet('USERS', function($sheet) use($result) 
		        {

	 				$sheet->row(1, function($first_row) {

	                    $first_row->setAlignment('center');

	                });

	                $sheet->setHeight(50);

					$sheet->setAutoSize(true);

					$sheet->setAllBorders('thin');
			        
			        $sheet->setFontFamily('Comic Sans MS');

					$sheet->setFontSize(15);
				
					// Set height for a single row

		    		$sheet->setAutoFilter();

		    		$title = tr('users_management');
		    		
			        $sheet->loadView('exports.users')->with('data' , $result)->with('title' , $title);

			    });
		    
		    })->export($format);

            return redirect()->route('admin.users.index')->with('flash_success' , tr('export_success'));

		} catch(\Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.users.index')->with('flash_error' , $error);

        }

    }

    /**
	 * Function Name: moderators_export()
	 *
	 * @usage used export the moderators details into the selected format
	 *
	 * @created Maheswari
	 *
	 * @edited Maheswari
	 *
	 * @param string format (xls, csv or pdf)
	 *
	 * @return redirect users page with success or error message 
	 */
    public function moderators_export(Request $request) {

    	try {

    		// Get the admin selected format for download

    		$format = $request->format ? $request->format : 'xls';

	    	$download_filename = routefreestring(Setting::get('site_name'))."-".date('Y-m-d-h-i-s')."-".uniqid();

	    	$result = Moderator::orderBy('created_at' , 'desc')->get();

	    	// Check the result is not empty

	    	if(count($result) == 0) {
            	
            	return redirect()->route('admin.moderators')->with('flash_error' , tr('no_moderator_found'));
	    	}

	    	Excel::create($download_filename, function($excel) use($result)
		    {
		        $excel->sheet('MODERATOR', function($sheet) use($result) 
		        {

	 				$sheet->row(1, function($first_row) {

	                    $first_row->setAlignment('center');

	                });

	                $sheet->setHeight(50);

					$sheet->setAutoSize(true);

					$sheet->setAllBorders('thin');
			        
			        $sheet->setFontFamily('Comic Sans MS');

					$sheet->setFontSize(15);
				
					// Set height for a single row

		    		$sheet->setAutoFilter();

		    		$title = tr('moderator_management');

			        $sheet->loadView('exports.moderators')->with('data' , $result)->with('title' , $title);

			    });
		    
		    })->export($format);

            return redirect()->route('admin.moderators.index')->with('flash_success' ,tr('export_success'));

		} catch(\Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.moderators.index')->with('flash_error' , $error);

        }

    }

    /**
	 * Function Name: videos_export()
	 *
	 * @usage used export the videos details into the selected format
	 *
	 * @created Maheswari
	 *
	 * @edited Maheswari
	 *
	 * @param string format (xls, csv or pdf)
	 *
	 * @return redirect users page with success or error message 
	 */
    public function videos_export(Request $request) {

    	try {

    		// Get the admin selected format for download

    		$format = $request->format ? $request->format : 'xls';

	    	$download_filename = routefreestring(Setting::get('site_name'))."-".date('Y-m-d-h-i-s')."-".uniqid();

	    	$result = AdminVideo::orderBy('created_at' , 'desc')->get();

	    	// Check the result is not empty

	    	if(count($result) == 0) {
            	
            	return redirect()->route('admin.videos')->with('flash_error' , tr('no_video_found'));
	    	}

	    	Excel::create($download_filename, function($excel) use($result) {

		        $excel->sheet('VIDEO', function($sheet) use($result) {

	 				$sheet->row(1, function($first_row) {

	                    $first_row->setAlignment('center');

	                });

	                $sheet->setHeight(50);

					$sheet->setAutoSize(true);

					$sheet->setAllBorders('thin');
			        
			        $sheet->setFontFamily('Comic Sans MS');

					$sheet->setFontSize(15);
				
					// Set height for a single row

		    		$sheet->setAutoFilter();

		    		$title = tr('video_management');

			        $sheet->loadView('exports.videos')->with('data' , $result)->with('title' , $title);

			    });
		    
		    })->export($format);

            return redirect()->route('admin.videos')->with('flash_success' ,tr('export_success'));

		} catch(\Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.videos')->with('flash_error' , $error);

        }

    }

    /**
	 * Function Name: subscription_export()
	 *
	 * @usage used export the subscription details into the selected format
	 *
	 * @created Maheswari
	 *
	 * @edited Maheswari
	 *
	 * @param string format (xls, csv or pdf)
	 *
	 * @return redirect users page with success or error message 
	 */
    public function subscription_export(Request $request) {

    	try {

    		// Get the admin selected format for download

    		$format = $request->format ? $request->format : 'xls';

	    	$download_filename = routefreestring(Setting::get('site_name'))."-".date('Y-m-d-h-i-s')."-".uniqid();

	    	$result = UserPayment::orderBy('created_at' , 'desc')->get();

	    	// Check the result is not empty

	    	if(count($result) == 0) {
            	
            	return redirect()->route('admin.user.payments')->with('flash_error' , tr('no_payments_found'));
	    	}

	    	Excel::create($download_filename, function($excel) use($result) {

		        $excel->sheet('SUBSCRIPTION', function($sheet) use($result) {

	 				$sheet->row(1, function($first_row) {

	                    $first_row->setAlignment('center');

	                });

	                $sheet->setHeight(50);

					$sheet->setAutoSize(true);

					$sheet->setAllBorders('thin');
			        
			        $sheet->setFontFamily('Comic Sans MS');

					$sheet->setFontSize(15);
				
					// Set height for a single row

		    		$sheet->setAutoFilter();

		    		$title = tr('subscription_management');

			        $sheet->loadView('exports.subscription')->with('data' , $result)->with('title' , $title);

			    });
		    
		    })->export($format);

            return redirect()->route('admin.user.payments')->with('flash_success' ,tr('export_success'));

		} catch(\Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.user.payments')->with('flash_error' , $error);

        }

    }

    /**
	 * Function Name: payperview_export()
	 *
	 * @usage used export the video payperview details into the selected format
	 *
	 * @created Maheswari
	 *
	 * @edited Maheswari
	 *
	 * @param string format (xls, csv or pdf)
	 *
	 * @return redirect users page with success or error message 
	 */
    public function payperview_export(Request $request) {

    	try {

    		// Get the admin selected format for download

    		$format = $request->format ? $request->format : 'xls';

	    	$download_filename = routefreestring(Setting::get('site_name'))."-".date('Y-m-d-h-i-s')."-".uniqid();

	    	$result = PayPerView::orderBy('created_at' , 'desc')->get();

	    	// Check the result is not empty

	    	if(count($result) == 0) {
            	
            	return redirect()->route('admin.user.video-payments')->with('flash_error' , tr('no_pay_per_view_found'));
	    	}

	    	Excel::create($download_filename, function($excel) use($result) {

		        $excel->sheet('PAYPERVIEW', function($sheet) use($result) {

	 				$sheet->row(1, function($first_row) {

	                    $first_row->setAlignment('center');

	                });

	                $sheet->setHeight(50);

					$sheet->setAutoSize(true);

					$sheet->setAllBorders('thin');
			        
			        $sheet->setFontFamily('Comic Sans MS');

					$sheet->setFontSize(15);
				
					// Set height for a single row

		    		$sheet->setAutoFilter();

		    		$title = tr('payperview_management');

			        $sheet->loadView('exports.payperview')->with('data' , $result)->with('title' , $title);

			    });
		    
		    })->export($format);

            return redirect()->route('admin.user.video-payments')->with('flash_success',tr('export_success'));

		} catch(\Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.user.video-payments')->with('flash_error' , $error);

        }

    }
}
