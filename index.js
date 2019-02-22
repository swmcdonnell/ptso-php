/**
 * Implements most of the logic for the Perspective Taking/Spatial Orientation Test (PTSO).
 *
 * The logic is contained in two event loops. The first controls the question flow
 * and the second responds to user clicks in the circle that draw the line that
 * makes up the response to each question.
*/

/* ***************************************************************************
    Variables:
 *****************************************************************************/
// Coordinates for center of the circle
const center = {
    x : 200,
    y : 150
};
// Radius of the circle
const radius = 100;
// Default for the timer (5 minutes)
let timer = 300;
// Object to hold the timer clock
let clock = {};
// Current question (task)
let taskNum = 0;
// User finished test all the way
let completed = false;
// Angle drawn by the user for each question (answer to each question)
let angles = [];
// Question text
let question = "";
// SVG elements, bounded by the canvas
const svg = {
    canvas: document.getElementById("canvas"),
    dot: document.getElementById("dot"),
    circle: document.getElementById("circle"),
    anchor: document.getElementById("anchor"),
    line: document.getElementById("line")
};
// HTML elements, jQuery references
const html = {
    instructions : $("#instructions"),
    button : $("#button"),
    standing : $("#standing"),
    facing : $("#facing"),
    progressBar : $("#progressbar"),
    timer : $("#timer"),
    completed : $("#completed")
};

/* ***************************************************************************
 Question Flow
 *****************************************************************************/
/**
 * The initial HTML page displays the instructions. We use the name of the button,
 * which has no effect on the program, as the way to control the flow. We also change
 * the button label to make it meaningful to the user. After the
 * initial page, the button is renamed to "example," because clicking the button
 * takes the user to Question 0, which is the example question. Then we change the name
 * of the button to "tasks", and it remains this way for each question in the test.
 * At the last question, we change the name to "finish" to indicate that the test is
 * done.
  */
html.button.click((event) => {
    switch (event.currentTarget.name) {

        // First question #0 (example)
        case "example":
            taskNum = 0;
            // Display instructions
            html.instructions.html(`<?php echo file_get_contents("example.html");?>`);
            // Relabel the button to indicate we're in the questions and to indicate that timer starts
            html.button.attr("name", "task").text("Start PTSO >>");
            html.button.removeClass("btn-primary").addClass("btn-success");
            // Add anchor text
            html.standing.text(tasks[0].standing);
            html.facing.text(tasks[0].facing);

            // These are the x,y coordinates of the answer to the first question (example)
            svg.line.setAttribute("x2", "122");
            svg.line.setAttribute("y2", "87");
            svg.line.setAttribute("visibility", "visible");
            angles[0] = tasks[0].result;
            break;

        // All other questions
        case "task":
            // Special thins to do at the first question
            if (++taskNum === 1) {
                // Show the progress bar and change the button label
                html.progressBar.show();
                html.button.text("Save >>");

                // Start the timer
                clock = setInterval(() => {
                    let minutes = parseInt(timer/60);
                    let seconds = timer - minutes*60;
                    let display = minutes + ":" + ("0" + seconds).slice(-2);
                    html.timer.html(display);
                    if (--timer < 0) {
                        html.button.attr("name", "timeout").click();
                    }
                }, 1000);

            // Right before the last question
            } else if (taskNum === tasks.length - 1) {
                // Change the button label at last question
                html.button.attr("name", "finish").text("Finish");
            }

            // For all questions, first build question text
            question = `<p><br/></p><p>${taskNum}. Imagine your are standing at the <strong>${tasks[taskNum].standing}</strong>` +
                ` and facing the <strong>${tasks[taskNum].facing}</strong>.</p><p>Point to the <strong>${tasks[taskNum].point}.</p>`;

            // Hide previous user line
            svg.line.setAttribute("visibility", "hidden");

            // Add anchor text
            html.standing.text(tasks[taskNum].standing);
            html.facing.text(tasks[taskNum].facing);

            // Update progress bar
            html.completed.attr("style", "width:" + Math.round(taskNum/12*100) + "% !important");

            // Display the question and wait for a button click
            html.instructions.html(question);

            // Disable the button until the user draws a line in the circle
            html.button.attr("disabled", "disabled");
            break;

        /* We do the following at the last question. The user could have either finished normally,
            or the clock might have run out. We issue different messages for these two cases. */
        case "finish":
        case "timeout":
            // Stop the clock
            clearInterval(clock);

            // Remove the circle event listener
            svg.circle.removeEventListener("click", userInterface);

            // Remove the user line and disable the button
            svg.line.setAttribute("visible", "hidden");
            html.button.hide();

            // Save the results
            saveResults();

            // Display closing messages
            let msg = "<p><br/></p>";
            if (event.currentTarget.name === "timeout") {
                msg += `<div class="bg-danger text-white">Time's Up! Sorry, but there is a 5-minute time limit on the test, and you ran out of time.</div>`;
            } else {
                completed = true;
                msg += `<div class="bg-success text-white">You successfully completed the test!</div>`;
            }
            html.instructions.html(`${msg}<div class="lead">` +
                `<p>Your responses have been recorded as participant #${participant}.</p>` +
                `<p>Thank you for participating and have a great day!</p></div>`);
            break;
    }
});

/* ***************************************************************************
 * Drawing Event.
 *
 * Listens for the user to click on the edge of the circle, draws the line
 * and measures and stores the angle.
 *****************************************************************************/
svg.circle.addEventListener('click', userInterface);
function userInterface(event) {
    // No line drawn for Question #0 (example)
    if (taskNum < 1) return;

    // Get the click position and translate to SVG coordinates
    let pt = svg.canvas.createSVGPoint();
    pt.x = event.clientX;
    pt.y = event.clientY;
    let loc = pt.matrixTransform(svg.canvas.getScreenCTM().inverse());

    // Draw the line
    svg.line.setAttribute("x2", loc.x);
    svg.line.setAttribute("y2", loc.y); // for the arrowhead
    svg.line.setAttribute("visibility", "visible");

    // Calculate the angle (radians) and convert to degrees
    let angle = (Math.atan2(loc.y - center.y, loc.x - center.x) * 180 / Math.PI + 360) % 360;
    // Angle calculated at 3:00 and we need it at 12:00 so dial back by 90 degrees)
    angle = Math.round(angle + 90);
    angle = (angle >= 360) ? angle - 360 : angle;

    // Store the result for this task
    angles[taskNum] = angle;

    // Make the save and continue button active
    html.button.removeAttr("disabled");
}

/**
 * Saves results by posting to save.php, which saves to disk.
 */
function saveResults() {
    // Calculate average error; exclude question 0
    let sum = 0;
    for (let i=1; i<tasks.length; i++) {
        sum += Math.abs(tasks[i].result - angles[i]);
    }
    let avgDiff = sum / (tasks.length-1);

    // Put together result JSON
    let results = {
        "partnum" : participant,
        "angles" : angles,
        "avgerr" : avgDiff,
        "submitted" : `<?php echo date("Y-m-d H:i:s");?>`,
        "complete" : completed ? 1 : 0
    };

    // Post to self
    results = JSON.stringify(results);
    $.post("/save.php", {"response":results});
}


