/* --- Document events --- */
window.onload = function () {
    chartInit();
}

/* --- Get fresh data values --- */
var reloadTimerId = setInterval(function() {
    reloadData();
}, 2000);

function reloadData() {
    /*AJAX
    getData
    updateDataOnPage
     - updateCurrentValues
     - updateChart*/
    
    //console.log(getRandomInt(15,25),'C');
    $('#temp').text(getRandomInt(10,40));
    $('#naohinp').text(getRandomInt(1, 3));
    $('#co2inp').text(getRandomInt(10,4));
     $('#massinp').text(getRandomInt(1,5));
    $('#co2out').text(getRandomInt(4,1));
    $('#shlam').text(getRandomInt(1,6));
}

/* --- Chart Mechanics --- */
function chartInit() {
    var chart = new CanvasJS.Chart("chartContainer",
        {
            theme: "theme1", 
            zoomEnabled: true,
            panEnabled: true,
            animationEnabled: true,
            animationDuration: 45000,
            title: {
                text: "Зміна температури у скрубері в залежності від часу",
                fontsize: 20,
                fontColor: "#7e838d"
       
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
                //valueFormatString:"DD-MMM-YY, HH:mm",
                valueFormatString:"### sec.",
               
            },
            data: [{
                
                yValueType: "number",
                //xValueType: "dateTime",
                xValueType: "number",
                type: "area",
                color: getColor(),
                //xValueFormatString: "DD-MMM-YY, HH:mm:ss",
                xValueFormatString: "### sec.",
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
    //var xValue = new Date(Date.parse('01 Sep 2016 11:00:00 GMT'));
    var xValue = 150;
    var dataPointsArray = [];
    dataPointsArray.push({
        //x: xValue.setMinutes(xValue.getMinutes()+30),
        x: xValue,
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

        xValue = xValue+1;
        dataPointsArray.push({
            //x: xValue.setMinutes(xValue.getMinutes()+30),
            x: xValue,
            y: yValue
        });
    }

    //Return values to Chart library
    return dataPointsArray;
}

function getColor() {
    var arr = ['#aabde7','#aabde7','#aabde7','#aabde7','#aabde7','#aabde7','#aabde7','#aabde7'];
    return arr[getRandomInt(0,7)];
}

/* --- Help Functions --- */
function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min)) + min;
}