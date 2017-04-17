var emulationInterval = null;
$(document).ready(function(){
    emulationInterval = setInterval(function(){ emulateDevice() }, 10*1000); //every 60 seconds
});

function emulateDevice() {
    $.ajax({
	url: baseUrl+"/api/emulateDevice/"+$('#deviceId').val(),
	type: "POST",
	data: {},
	success: function(response) {
            //console.log('emulateDevice:',response);
	},
        error: function(response) {
            console.log('emulateDevice(): Сталася помилка, спробуйте пізніше!');
        }
    });
}

/* ------------------------- */
function stopEmulation() {
    clearInterval(emulationInterval);
}