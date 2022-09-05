function isPhoneNumber(inputtxt) {
	var phoneno = /^\d{11}$/;
	inputtxt = inputtxt.replace(/\D+/g,"");
	if(inputtxt.match(phoneno)) {
		return true;
	} else {
		return false;
	}
}