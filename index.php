<!doctype html>
<html lang="en">
<!--
* Implements the Perspective Taking/Spatial Orientation Test by Hegarty, Kozhevnikov and Waller
* in PHP, JavaScript and HTML to run in a Web browser. Based on the Python version by Tim Domino
* https://github.com/TimDomino/ptsot

Author: Steve McDonnell <swmcdonnell@gmail.com>
Version: 1.0
Revised: 2019-02-20

Copyright 2019 Steve McDonnell <swmcdonnell@gmail.com>
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
persons to whom the Software is furnished to do so, subject to the following conditions:

      The above copyright notice and this permission notice shall be included in all copies or substantial portions
      of the Software.

      THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
      THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
      AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
      CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
      IN THE SOFTWARE.
-->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Perspective Taking/Spatial Orientation Test</title>
    <style>
        #timer { font-size:48px; font-weight:bold; color:midnightblue}
        #progressbar { width:50%; height:20px; border-radius:10px; border:solid 1px #000000; overflow:hidden;}
        #completed { position:relative; height:100%; background-color:#99cc66;}
    </style>
</head>
<body>
<!--
Set up the page. The left side has the timer at the top, the object array under that and the circle
that the user draws on to indictate direction below that. The drawings are SVG components. The right
side of the screen has instructions and displays the individual question text, including an example. It
also contains the navigation buttons.
-->
<div class="container">
    <div class="row">
        <div class="col-lg-6" id="timer">5:00</div>
        <div class="col-lg-6 h1 text-center">PTSO Test</div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <img id="image" src="object_array.png" alt="Object Array" width="437" height="300" />
            <br/>
            <svg xmlns="http://www.w3.org/2000/svg" id="canvas" width="400px" height="500px">
                <defs>
                    <!-- arrowhead marker definition -->
                    <marker id="arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="3"
                            markerHeight="3" orient="auto-start-reverse" stroke="context-stroke">
                        <path d="M 0 0 L 10 5 L 0 10 z" />
                    </marker>
                </defs>
                <!-- Circle at 200,150 with large enough border to make clicking the border easy -->
                <circle id="circle" cx="200" cy="150" r="100" stroke="black" stroke-width="8" fill="none" />
                <!-- Dot in center of the circle (where you are) -->
                <circle id="dot" cx="200" cy="150" r="2" stroke="black" fill="black" stroke-width="1" />
                <!-- Anchor text in center of the circle -->
                <text text-anchor="middle" id="standing" x="200" y="170" fill="midnightblue" class="font-weight-bold"></text>
                <!-- Anchor line that indicates forward  -->
                <line id="anchor" x1="200" y1="150" x2="200" y2="55" stroke="black" stroke-width="4" marker-end="url(#arrow)" />
                <!-- Anchor text that indicates forward facing -->
                <text text-anchor="middle" id="facing" x="200" y="40" fill="midnightblue" class="font-weight-bold"></text>
                <!-- Line the user draws by clicking on the edge of the circle -->
                <line id="line" x1="200" y1="150" x2="200" y2="150" stroke="red" stroke-width="4" marker-end="url(#arrow)" visibility="hidden" />
            </svg>
            <button id="button" class="btn btn-primary" name="example">View Example</button>
        </div>

        <!-- Begin with instructions for the test and let the user navigate to the first
        question, which is actually an example. -->
        <div class="col-lg-6">
            <div id="progressbar" style="display:none;">
                <div id="completed" style="width:0% !important;"></div>
            </div>
            <div id="instructions">
                <?php echo file_get_contents("instructions.html");?>
            </div>

        </div>
    </div>
</div>

<!-- Using the Bootstrap front-end framework -->
<script src="http://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<!-- Most of the logic is in a JavaScript file. Here we inject the participant number and the question text read from a file.
Then we inject the JavaScript -->
<script>
    $(document).ready(function() {
        <?php file_put_contents("count.dat", ($participant = @file_get_contents("count.dat") + 1)); ?>
        const participant = parseInt(`<?php echo $participant;?>`);
        const tasks = JSON.parse(`<?php echo file_get_contents("tasks.json");?>`);
        <?php include_once("index.js");?>
    });
</script>
</body>
</html>
