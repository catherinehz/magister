window.onload = function () {
    chartInit();
}
function chartInit() {
    var chart1 = new CanvasJS.Chart("chartContainer1",
        {
            theme: "theme1",
            zoomEnabled: true,
            panEnabled: true,
            animationEnabled: true,
            animationDuration: 2000,
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
                valueFormatString: "## t°C"
            },
            axisX: {
                //valueFormatString:"DD-MMM-YY, HH:mm",
                valueFormatString: "### sec.",
            },
            data: [{
                    yValueType: "number",
                    //xValueType: "dateTime",
                    xValueType: "number",
                    type: "area",
                    color: '#119911',
                    //xValueFormatString: "DD-MMM-YY, HH:mm:ss",
                    xValueFormatString: "### sec.",
                    yValueFormatString: "## t°C",
                    dataPoints: dataPointsArray1
                }]
        }
    );
    chart1.render();
    var chart2 = new CanvasJS.Chart("chartContainer2",
        {
            theme: "theme1",
            zoomEnabled: true,
            panEnabled: true,
            animationEnabled: true,
            animationDuration: 2000,
            title: {
                text: "Зміна витрати NaOH в залежності від часу",
                fontsize: 20,
                fontColor: "#7e838d"
            },
            legend: {
                horizontalAlign: "right",
                verticalAlign: "center"
            },
            axisY: {
                includeZero: true,
                valueFormatString: "## L/min"
            },
            axisX: {
                //valueFormatString:"DD-MMM-YY, HH:mm",
                valueFormatString: "### sec.",
            },
            data: [{
                    yValueType: "number",
                    //xValueType: "dateTime",
                    xValueType: "number",
                    type: "area",
                    color:'#551111',
                    //xValueFormatString: "DD-MMM-YY, HH:mm:ss",
                    xValueFormatString: "### sec.",
                    yValueFormatString: "## L/min",
                    dataPoints: dataPointsArray2
                }]
        }
    );
    chart2.render();

}
function getColor() {
    var arr = ['#aabde7', '#aabde7', '#aabde7', '#aabde7', '#aabde7', '#aabde7', '#aabde7', '#aabde7'];
    return arr[getRandomInt(0, 7)];
}
function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min)) + min;
}