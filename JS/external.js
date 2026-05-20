document.getElementById("myForm").addEventListener('submit', function(event) {
event.preventDefault();
let flag = true;
if(event.target.username.value === "") {
	document.getElementById("usernameErrMsg").innerHTML = "Username Error";
	flag = false;
}
if(event.target.password.value === "") {
	document.getElementById("passwordErrMsg").innerHTML = "Password Error";
	flag = false;
}
if (flag === true) {
	event.target.submit();
}
});