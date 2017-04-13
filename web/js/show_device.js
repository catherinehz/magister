var baseUrl = $('meta[name=baseurl]').attr("content");
var co2Config = parseFloat($('#co2Config').val().trim());

$(document).ready(function(){
    $('#saveCo2Config').click(saveCo2Config);
    $('#co2Config').keyup(updateCo2ConfigBtn);
});

function updateCo2ConfigBtn() {
    //Перевірка, чи є зміни у полі "Завдання"
    var currentCo2Config = parseFloat($('#co2Config').val().trim());
    if (isNaN(currentCo2Config)) {
        alert('Неправильне значення! Напр: 0.06');
        return;
    }

    //Змін нема
    if (co2Config == currentCo2Config) {
        $('#saveCo2Config').addClass('disabled');
        $('#saveCo2Config').removeClass('btn-success');
        $('#saveCo2Config').addClass('btn-default');
        return;
    }

    //Зміни є
    $('#saveCo2Config').removeClass('disabled');
    $('#saveCo2Config').removeClass('btn-default');
    $('#saveCo2Config').addClass('btn-success');
}

function saveCo2Config() {
    //Перевірка, чи є зміни у полі "Завдання"
    var currentCo2Config = parseFloat($('#co2Config').val().trim());
    if (isNaN(currentCo2Config)) {
        alert('Неправильне значення! Напр: 0.06');
        return;
    }

    //Змін нема
    if (co2Config == currentCo2Config) return;

    //Є зміни у полі "Завдання" - відсилаємо нове значення для запису у базу
    $.ajax({
	url: baseUrl+"/api/updateDeviceConfig/"+$('#deviceId').val(),
	type: "POST",
	data: {'CO2-in-C2H2-y1':currentCo2Config},
	success: function(response) {
            console.log(response);

            //Після запису вважаємо, що змін нема
            co2Config = currentCo2Config;
            if (co2Config == currentCo2Config) {
                $('#saveCo2Config').addClass('disabled');
                $('#saveCo2Config').removeClass('btn-success');
                $('#saveCo2Config').addClass('btn-default');
                return;
            }
	},
        error: function(response) {
            console.log(response);
            alert('Сталася помилка, спробуйте пізніше!');
        }
    });
}