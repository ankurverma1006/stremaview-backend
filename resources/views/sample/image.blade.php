<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cropper.js</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="cropper.css">
    <style>
        .label {
            cursor: pointer;
        }
       
        
        .img-container img {
            max-width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>Upload cropped image to server</h1>

        <form action="{{route('sample')}}" method="POST" enctype="multipart/form-data">

        <label class="label" data-toggle="tooltip" title="Change your avatar">

            <img class="rounded" id="avatar" src="https://avatars0.githubusercontent.com/u/3456749?s=160" alt="avatar">

            <input type="file" class="sr-only" id="input" name="image" accept="image/*">

        </label>

        <button type="submit"></button>

        </form>

        
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Crop the image</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="img-container">
                            <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="crop">Crop</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.bundle.min.js"></script>
    <script src="cropper.js"></script>
    <script>

        window.addEventListener('DOMContentLoaded', function() {

            var avatar = document.getElementById('avatar');
            var image = document.getElementById('image');
            var input = document.getElementById('input');
           
            var $modal = $('#modal');
            var cropper;

            $('[data-toggle="tooltip"]').tooltip();

            input.addEventListener('change', function(e) {

                var files = e.target.files;

                var done = function(url) {

                    input.value = '';

                    image.src = url;

                    $modal.modal('show');

                };

                var reader;

                var file;

                var url;

                if (files && files.length > 0) {

                    file = files[0];

                    if (URL) {

                        done(URL.createObjectURL(file));

                    } else if (FileReader) {

                        reader = new FileReader();

                        reader.onload = function(e) {

                            done(reader.result);
                        };

                        reader.readAsDataURL(file);
                    }
                }
            });

            $modal.on('shown.bs.modal', function() {

                cropper = new Cropper(image, {
                    aspectRatio: 4/2,
                    viewMode: 3,
                });

            }).on('hidden.bs.modal', function() {
                cropper.destroy();
                cropper = null;
            });

            document.getElementById('crop').addEventListener('click', function() {

                var initialAvatarURL;

                var canvas;

                $modal.modal('hide');

                if (cropper) {

                    canvas = cropper.getCroppedCanvas({
                        width: 160,
                        height: 160,
                    });

                    initialAvatarURL = avatar.src;

                    avatar.src = canvas.toDataURL();

                    canvas.toBlob(function(blob) {

                    	console.log(blob);

                        // var formData = new FormData();

                        // formData.append('avatar', blob);

                        // var output = document.getElementById("input");

                         // $('#image').val(blob)

                        $('#input').val(blob);

                        // output.val = blob;

                    });
                }
            });
        });


// reader = new FileReader();
// reader.onload = function(e) {
// // done(reader.result);

// var output = document.getElementById("input");
// 	alert(output);
// 	output.src = reader.result;
// };

    </script>
</body>

</html>