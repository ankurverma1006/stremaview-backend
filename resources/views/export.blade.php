<!DOCTYPE html>
<html>

<head>
    <title>{{$title}}</title>
</head>

<body>

    <table style="font-family: arial, sans-serif;border-collapse: collapse;">

        <!---------- HEADER START ------------->

        <tr style="background-color: #187d7d;color: #ffffff;">
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;font-weight: bold;">S.NO</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;font-weight: bold;">USERNAME</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;font-weight: bold;">EMAIL</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;font-weight: bold;">MOBILE</th>
        </tr>

        <!---------- HEADER END ------------->

        @foreach($data as $i => $user_details)

            <tr @if($i % 2 == 0) style="background-color: #cccccc;" @endif >

                <td style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$i+1}}</td>

                <td style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$user_details->name}}</td>

                <td style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$user_details->email}}</td>

                <td style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$user_details->mobile}}</td>
            </tr>

        @endforeach
    </table>

</body>

</html>