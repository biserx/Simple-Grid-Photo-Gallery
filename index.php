<?php
	$set_filename= "images-set.cfg";
	$images = [];
	if (!file_exists($set_filename)) { /* check if set exists */
		$files = glob("thumbs/*.jpg"); /* get all files from thumbs folder */
		$files = array_reverse($files); /* reverse order to show newer images first */
		foreach ($files as $file) {
			list($width, $height, $type, $attr) = getimagesize("$file"); /* get image size */
			array_push($images, [$file, $width, $height]);
		}
		$file_handler = fopen($set_filename, 'w');
		fwrite($file_handler, serialize($images)); /* serialize array to file for future use */
		fclose($file_handler);
	} else {
		$file = file_get_contents($set_filename); 
		$images = unserialize($file); /* unserialize file content */
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Gallery | Your Name</title>
		<script>
			var loadedCount = 0;
			var images = [<?php foreach ($images as list($file, $width, $height)) echo "['".$file."',".$width.",".$height."],"; ?>];
		</script>
		<style>
			body { background: #222224; }

			a { color: silver; }
			a:link { text-decoration: none; }
			a:visited { text-decoration: none; }
			a:hover { text-decoration: none; color: white; }
			a:active { text-decoration: none; }

			span, h1 {
				color: white;
				font-family: Verdana, Geneva, sans-serif;
			}

			/* https://css-tricks.com/css-transparency-settings-for-all-broswers/ */
			.transparent {
				zoom: 1;
				filter: alpha(opacity=0);
				opacity: 0;
			}

			div.container { 		
				margin: 0 auto;
				min-width: 510px;
		    }

			div.header {
				margin: 25px auto 0; 
				width: 100%;
			}

			@media all and (max-width: 1020px) {
				div.header {
					font-size: 70%;
				}
			}

			span#back {
				font-size: 3em;
				font-weight: bold;
			}

			h1#title {
				margin: 0;
				float:right;
				font-size: 3em;
			}
			
			div.thumbs-container {
				width: 100%;
				height: 100%;
			}

		    img.thumb {
				position: absolute;
			}
			
			div.footer {
				margin: 25px auto 0; 
				width: 100%;
				height: 40px;
				text-align: center;
			}
		</style>

		<script>
			var headerHeight = 125;
			var footerHeight = 65;

			var imageWidth = 350; /* you can change this */
			var margin = 10;
			var columnWidth = imageWidth + margin;

			var topOffset = headerHeight;
			var leftOffset = 0;

			var columnProgress = [];
			
			function reposition(photo) {
				var leastColumnProgress = columnProgress[0];
				var columnIndex = 0;
				for (var j = 1; j < columnProgress.length; j++) {
					if (leastColumnProgress > columnProgress[j]) {
						leastColumnProgress = columnProgress[j];
						columnIndex = j;
					}
				}
				photo.style.top = (topOffset + margin + leastColumnProgress) + 'px';
				photo.style.left = (leftOffset + columnIndex * columnWidth) + 'px';
				columnProgress[columnIndex] += margin + parseInt(window.getComputedStyle(photo).height);
			}

			function loadOne() {
				var data = images[loadedCount++];
				var scaleFactor = imageWidth / data[1];	

				var img = document.createElement("IMG");

				img.setAttribute("class", "thumb");
				img.width = data[1] * scaleFactor;
				img.height = data[2] * scaleFactor;
				img.setAttribute("src", data[0]);
				img.setAttribute("onclick", "showFullscreen(this)");

				document.getElementsByClassName('thumbs-container')[0].appendChild(img);
				reposition(img);
			}
			
			function calcContainerHeight() {
				var maxColumnProgress = columnProgress[0];
				for (var i = 1; i < columnProgress.length; i++) {
					if (maxColumnProgress < columnProgress[i]) {
						maxColumnProgress = columnProgress[i];
					}
				}

				var thumbs = document.getElementsByClassName("thumbs-container")[0];
				thumbs.style.height = (maxColumnProgress + 2*margin) + 'px';
			}

			function scroll() {
				var windowHeight = window.innerHeight;

				var photos = document.getElementsByClassName("thumb");
				if (photos.length == 0) return;
				var lastPhotoTop = photos[photos.length-1].getBoundingClientRect().top;

				if (lastPhotoTop < 2 * windowHeight && images.length > 0) {
					loadOne();
				}
				calcContainerHeight();
			}

			function initColumns() {
				var columnsCount = Math.floor(window.innerWidth / columnWidth);
				if (columnsCount == 0) columnsCount = 1;
				if (columnsCount == 1) headerHeight -= 30; /* css hack */

				var container = document.getElementsByClassName("container")[0];
				container.style.width = (columnsCount * columnWidth) + 'px';
			
				leftOffset = (window.innerWidth - columnsCount * columnWidth) / 2;

				columnProgress = [];
				for (var i = 0; i < columnsCount; i++)
					columnProgress[i] = 0;
			}
			
			function resize() {
				/* reinit columns when window dimensions change */
				initColumns();
				var photos = document.getElementsByClassName("thumb");
				for (var i = 0; i < photos.length; i++) {
					reposition(photos[i]);
				}
				/* call scroll to load more images if necessary */
				scroll();
			}

			function load() {
				checkIfFullscreen();

				initColumns();

				/* Load some photos to fill the window */
				var windowHeight = window.innerHeight;
				while(true) {
					loadOne();
					var photos = document.getElementsByClassName("thumb");
					var lastPhotoTop = photos[photos.length-1].getBoundingClientRect().top;
					if (lastPhotoTop > windowHeight)
						break;
				}

				calcContainerHeight();

				document.getElementsByClassName("container transparent")[0].className = "container";
			}
		</script>
	</head>
	<body onresize="resize()" onload="load()" onscroll="scroll()">
		<div class="container transparent">
			<div class="header">
				<div>
					<span id="back"><a href="http://link.to.your.site.root/">..</a></span>
					<h1 id="title">Gallery | Your Name</h1>
				</div>
				<div style="clear:both; text-align: right">
					<span id="follow500px">short about</span>
				</div>
			</div>
			<div class="thumbs-container">
			</div>
			<div class="footer">
				<span>Copyright © 2016 by Your Name</span>
			</div>
		</div>
		
		<style>
			#gallery-div
			{		 
				position: fixed; 
				width: 100%; 
				height: 100%; 
				top: 0px; 
				left: 0px; 
				text-align: center; 
				background: #191922;
				z-index: 5;
			}
			#gallery-div-helper
			{
				height: 100%;
				width: 100%;
				vertical-align: middle;
    			background-size: contain;
    			background-repeat: no-repeat;
    			background-position: center;
			}
		</style>

		<script>
			function checkIfFullscreen() {
				var url = window.location.href.toString().split('/');
				var last = url.pop();
				var baseUrl = url.join('/') + '/';
				window.history.replaceState(null, null, baseUrl);
				for (var i = 0; i < images.length; i++) {
					if (images[i][0].indexOf(last) > 0) {	
						console.log(images[i]);
						var img = {"src" : images[i][0], "getAttribute" : function(attr) { return this.src; }};
						showFullscreen(img);
						break;
					}
				}
			}

			function showFullscreen(src) {
			 	var container = document.getElementById("gallery-div");
				var helper = document.getElementById("gallery-div-helper");
			 	var img = document.getElementById("gallery-img");

			 	helper.style.backgroundImage = src.getAttribute('src');
				img.src = src.getAttribute('src');

				if (container.style.display == "none")
					container.style.display = "block";
		
				var orientation_landscape = window.innerWidth / window.innerHeight <= img.clientWidth / img.clientHeight;

				img.style.height = "";
				img.style.width = "";	

				if (orientation_landscape) {
					container.style.display = "table";
					helper.style.display = "table-cell";
					img.style.width = "inherit";
				} else {
					container.style.display = "block";
					helper.style.display = "block";
					img.style.height = "inherit";
				}	

				img.src = src.getAttribute('src').replace("thumbs","fullscreen");

				window.history.pushState(null, null, window.location.href + src.getAttribute('src').replace("thumbs/","").replace(".jpg", ""));
			}

			function closeFullscreen() {
				window.history.back();
			}

			window.onpopstate = function(e){
			    var container = document.getElementById("gallery-div");
				container.style.display = "none";
				checkIfFullscreen();
			};

		</script>

		<div id="gallery-div" style="display: none;" onclick="closeFullscreen()">	
			<div id="gallery-div-helper">
				<img id="gallery-img" class="gallery" src="">
			</div>
		</div>

	</body>
</html>
