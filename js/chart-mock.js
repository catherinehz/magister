/* --- Document events --- */
window.onload = function () {
    chartInit();
}

/* --- Chart Mechanics --- */
function chartInit() {
    var chart = new CanvasJS.Chart("chartContainer",
        {
            zoomEnabled: true,
            panEnabled: true,
            animationEnabled: true,
            animationDuration: 10000,
            title: {
                text: "Показники вузлу №54"
            },
            legend: {
                horizontalAlign: "right",
                verticalAlign: "center"
            },
            axisY: {
                includeZero: true,
                valueFormatString:"## t°C"
            },
            axisX: {
                valueFormatString:"DD-MMM-YY, HH:mm"
            },
            data: [{
                yValueType: "number",
                xValueType: "dateTime",
                type: "spline",
                color:getColor(),
                xValueFormatString: "DD-MMM-YY, HH:mm:ss",
                yValueFormatString: "## t°C",
                dataPoints: mathModel()
            }]
        }
    );
    chart.render();
}

//TBD: get data points from MySQL database through AJAX-request
function mathModel() {
    //Initial values
    var yValue = 22;
    var xValue = new Date(Date.parse('01 Sep 2016 11:00:00 GMT'));
    var dataPointsArray = [];
    dataPointsArray.push({
        x: xValue.setMinutes(xValue.getMinutes()+30),
        y: yValue
    });

    //Math data generator
    var limit = 1000;
    for (var i = 0; i < limit; i += 1) {
        if (yValue <= 15) {
            yValue += getRandomInt(1, 5);
        } else if (yValue >= 80) {
            yValue -= getRandomInt(1, 5);
        } else {
            yValue += getRandomInt(-5, 5);
        }

        dataPointsArray.push({
            x: xValue.setMinutes(xValue.getMinutes()+30),
            y: yValue
        });
    }

    //Return values to Chart library
    return dataPointsArray;
}

function getColor() {
    var arr = ['red','orange','lime','green','black','blue','#258454','#8b29b2'];
    return arr[getRandomInt(0,7)];
}

/* --- Help Functions --- */
function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min)) + min;
}