<!DOCTYPE html>
<html>

<head>
    <title>{{$title}}</title>
     <style type="text/css">
        
        table{
            font-family: arial, sans-serif;
            border-collapse: collapse;
        }

        .first_row_design{
            background-color: #187d7d;
            color: #ffffff;
        }

        .row_col_design{
            background-color: #cccccc;
        }

        th{
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            font-weight: bold;

        }

        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;

        }
    </style>
</head>

<body>

    <table>

        <!------ HEADER START  ------>

        <tr class="first_row_design">
            <th>{{tr('s_no')}}</th>

            <th>{{tr('title')}}</th>

            <th>{{tr('category')}}</th>

            <th>{{tr('sub_category')}}</th>

            <th>{{tr('genre_name')}}</th>

            <th>{{tr('video_type')}}</th>

            <th>{{tr('position')}}</th>

            @if(Setting::get('is_payper_view'))

            <th>{{tr('pay_per_view')}}</th>

            @endif

            <th>{{tr('video_upload_type')}}</th>


            <th>{{tr('duration')}}</th>

            <th>{{tr('ratings')}}</th>

            <th>{{tr('viewers_cnt')}}</th>

            <th>{{tr('redeems')}}</th>
                
            <th>{{tr('uploaded_by')}}</th>
            
            <th>{{tr('ppv_created_by')}}</th>

            <th>{{tr('likes')}}</th>

            <th>{{tr('dislikes')}}</th>

            <th>{{tr('age')}}</th>

            <th>{{tr('status')}}</th>

            <th>{{tr('total')}}</th>

            <th>{{tr('admin_amount')}}</th>

            <th>{{tr('moderator_amount')}}</th>
            <th>{{tr('created_time')}}</th>
            <th>{{tr('default_image')}}</th>
            <th>{{tr('banner_image')}}</th>
            <th>{{tr('trailer_video')}}</th>
            <th>{{tr('video')}}</th>
        </tr>

        <!------ HEADER END  ------>

        @foreach($data as $i => $video_details)
        

            <tr @if($i % 2 == 0) class="row_col_design" @endif >

                <td>{{$i+1}}</td>

                <td>{{$video_details->title}}</td>

                <td>{{$video_details->category ? $video_details->category->name: "-"}}</td>

                <td>{{$video_details->subCategory ? $video_details->subCategory->name: "-" }}</td>

                <td>{{$video_details->genreName ? $video_details->genreName->name : "-"}}</td>
 
                <td>
                   @if($video_details->video_type == 1)
                        {{tr('video_upload_link')}}
                    @endif
                    @if($video_details->video_type == 2)
                        {{tr('youtube')}}
                    @endif
                    @if($video_details->video_type == 3)
                        {{tr('other_link')}}
                    @endif
                </td>

                <td>
                    @if ($video_details->genre_id > 0)
                        
                        @if($video_details->position > 0)

                        <span class="label label-success">{{$video_details->position}}</span>

                        @else

                        <span class="label label-danger">{{$video_details->position}}</span>

                        @endif

                    @else

                        <span class="label label-warning">{{tr('not_genre')}}</span>

                    @endif
                </td>

                @if(Setting::get('is_payper_view'))
                    <td class="text-center">
                        @if($video_details->amount > 0)
                            <span class="label label-success">{{tr('yes')}}</span>
                        @else
                            <span class="label label-danger">{{tr('no')}}</span>
                        @endif
                    </td>
                @endif

                <td>
                @if($video_details->video_upload_type == 1)
                    {{tr('s3')}}
                @endif
                @if($video_details->video_upload_type == 2)
                    {{tr('direct')}}
                @endif

                <td>
                {{$video_details->duration}}</td>

               <td>@if($video_details->ratings == 5)  5 @endif
                @if($video_details->ratings == 4)  4 @endif
                @if($video_details->ratings == 3)  3 @endif
                @if($video_details->ratings == 2)  2 @endif
                @if($video_details->ratings == 1)  1 @endif

               </td>

               <td>{{$video_details->watch_count ? number_format_short($video_details->watch_count) : 0}}</td>
               

                <td>{{Setting::get('currency')}} {{$video_details->redeem_amount ? $video_details->redeem_amount : 0}}</td>

                <td>
                @if(is_numeric($video_details->uploaded_by))

                    {{$video_details->moderator ? $video_details->moderator->name : ''}}

                @else 

                    {{$video_details->uploaded_by}}

                @endif
                </td>


                <td>
                @if(is_numeric($video_details->ppv_created_by))

                    {{moderator_details($video_details->ppv_created_by , 'name')}}
                
                @else 

                   {{$video_details->ppv_created_by}}

                @endif
                </td>

                <td>{{number_format_short($video_details->getScopeLikeCount->count())}}</td>

                <td>{{number_format_short($video_details->getScopeDisLikeCount->count())}}</td>

                <td>
                    @if(!$video_details->is_banner)

                        {{$video_details->age}} 

                    @endif  
                </td>

                <td>
                    @if ($video_details->compress_status < OVERALL_COMPRESS_COMPLETED)

                        <span class="label label-danger">{{tr('compress')}}</span>
                        
                    @else
                        @if($video_details->is_approved)
                            <span class="label label-success">{{tr('approved')}}</span>
                        @else
                            <span class="label label-warning">{{tr('pending')}}</span>
                        @endif
                    @endif
                </td>

                <td>{{Setting::get('currency')}} {{$video_details->admin_amount + $video_details->user_amount}}</td>

                <td>{{Setting::get('currency')}} {{$video_details->admin_amount}}</td>

                <td>{{Setting::get('currency')}} {{$video_details->user_amount}}</td>

                <td>{{$video_details->created_at}}</td>

                <td>{{$video_details->default_image}}</td>

                <td>{{$video_details->banner_image}}</td>

                <td>{{$video_details->trailer_video}}</td>

                <td>{{$video_details->video}}</td>

            </tr>

        @endforeach
    </table>

</body>

</html>