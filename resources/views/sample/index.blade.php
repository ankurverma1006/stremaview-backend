<!DOCTYPE html>
<html>
<head>
	<title></title>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<link href="cropbox.css" rel="stylesheet">
<script type="text/javascript" src="cropbox.js"></script>
<style>
	
	.btn-file {
    position: relative;
    overflow: hidden;
}
.btn-file input[type=file] {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 100%;
    min-height: 100%;
    font-size: 100px;
    text-align: right;
    filter: alpha(opacity=0);
    opacity: 0;
    outline: none;
    background: white;
    cursor: inherit;
    display: block;
}
#plugin .workarea-cropbox,
#plugin .bg-cropbox {
    height: 500px;
    min-height: 500px;
    width: 500px;
    min-width: 500px;
}
</style>
</head>
<body>


<nav class="navbar navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#">WebSiteName</a>
		</div>
		<ul class="nav navbar-nav">
			<li class="active"><a href="#">Home</a></li>
			<li><a href="#">Page 1</a></li>
			<li><a href="#">Page 2</a></li>
			<li><a href="#">Page 3</a></li>
		</ul>
	</div>
</nav>

<div class="container">

	<div id="plugin"></div>
<input id="file-input" class="form-control" type="file" accept="image/*">

<br>
<br>

<button id="btn-crop" type="button" class="btn btn-success">Crop</button>
<button id="btn-start" type="button" class="btn btn-primary">Start</button>
<button id="btn-reset" type="button" class="btn btn-info">Reset</button>
<button id="btn-scale-out" type="button" class="btn btn-warning">-</button>
<button id="btn-scale-in" type="button" class="btn btn-danger">+</button>
<br>
<br>


<div id="cropped-container"></div>
<textarea id="cropped-data" class="form-control"></textarea>


	<!-- <div class="row">

		<form method="POST" enctype="multipart/form-data" role="form" action="{{url('/compress/image')}}">

			<div class="form-group">

				<label>Select a file: </label>

				<input class="form-control" type="file" name="default_image">

			</div>

			<div class="form-group">

				<label>Width</label>

				<input type="text" name="width" class="form-control">
			</div>

			<div class="form-group">

				<label>Height</label>	

				<input type="text" name="height" class="form-control">

			</div>
	  		
	  		<input type="submit" class="btn btn-success">



			
		</form>

		<div class="actions">
	            <a class="btn file-btn">
	                <span>Upload</span>
	                <input type="file" id="upload" value="Choose a file" accept="image/*" />
	            </a>
	            <button class="upload-result">Result</button>
	        </div>
	    </div>
	    <div class="col-1-2">
	        <div class="upload-msg">
	            Upload a file to start cropping
	        </div>
	        <div class="upload-demo-wrap">
	            <div id="upload-demo"></div>
	        </div>
	    </div>

	</div>

	<div class="row">

		@if(isset($uploaded_image))

			<p>{{$uploaded_image}}</p>

			<img src="{{$uploaded_image}}" alt="" class="img img-responsive img-thumbnail">

		@endif
		
	</div> -->

</div>



<script>

	var cropbox = new Cropbox('#plugin', {
    variants: [
        {
            width: 833,
            height: 500
        }
    ]
});
// scaling
var scaleInBtn = document.querySelector('#btn-scale-in');
scaleInBtn.addEventListener('click', function(){
    cropbox.scale(1.05);
});
var scaleOutBtn = document.querySelector('#btn-scale-out');
scaleOutBtn.addEventListener('click', function(){
    cropbox.scale(0.95);
});
cropbox.getMembrane().addEventListener('wheel', function(event){
    if (event.deltaY < 0) {
        cropbox.scale(1.01);
    } else {
        cropbox.scale(0.99);
    }
    event.preventDefault();
});
// image loading from a file
var fileInput = document.querySelector('#file-input'),
    startBtn = document.querySelector('#btn-start');
startBtn.addEventListener('click', function(){
    var fileReader = new FileReader();
    fileReader.readAsDataURL(fileInput.files[0]);
    fileReader.addEventListener('load', function(event){
        cropbox.load(event.target.result);
    });
});
// reset
var resetBtn = document.querySelector('#btn-reset');
resetBtn.addEventListener('click', function(){
    cropbox.reset();
});
// crop
var cropBtn = document.querySelector('#btn-crop');
cropBtn.addEventListener('click', function(){
    cropbox.crop();
});
// the cropped event
cropbox.getCb().addEventListener('cb:cropped', function(event){
    // add image to the container
    var img = document.createElement('img');
    img.src = event.detail.data.image;
    img.setAttribute('style', 'margin-right: 5px; margin-bottom: 5px');
    img.className = 'img-thumbnail';
    document.querySelector('#cropped-container').appendChild(img);
    // update inforamtion about crop
    document.querySelector('#cropped-data').value = JSON.stringify(cropbox.getData());
});
// the reset event
function resetHandler(){
    // clear the container
    document.querySelector('#cropped-container').innerHTML = '';
    // clear information about crop
    document.querySelector('#cropped-data').value = '';
};
cropbox.getCb().addEventListener('cb:reset', resetHandler);
// the ready event
cropbox.getCb().addEventListener('cb:ready', resetHandler);
// the disabled/enabled event
function disabledHandler(){
    scaleInBtn.setAttribute('disabled', 'disabled');
    scaleOutBtn.setAttribute('disabled', 'disabled');
    cropBtn.setAttribute('disabled', 'disabled');
};
disabledHandler();
cropbox.getCb().addEventListener('cb:disabledCtrls', disabledHandler);
cropbox.getCb().addEventListener('cb:enabledCtrls', function(){
    scaleInBtn.removeAttribute('disabled');
    scaleOutBtn.removeAttribute('disabled');
    cropBtn.removeAttribute('disabled');
});
	// $uploadCrop = $('#upload-demo').croppie({
	//     enableExif: true,
	//     viewport: {
	//         width: 200,
	//         height: 200,
	//         type: 'circle'
	//     },
	//     boundary: {
	//         width: 300,
	//         height: 300
	//     }
	// });
</script>
</body>



</html>