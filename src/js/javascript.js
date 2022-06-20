window.addEventListener("load", () => {
const request = (method, url, data, callback) => {
	let req = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	req.open(method, url, true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8;");
	let datas = [];
	for (let name in data) {
		datas.push(encodeURIComponent(name) + "=" + encodeURIComponent(data[name]));
	}
	datas = datas.join("&").replace(/%20/g,"+");
	req.addEventListener("readystatechange", () => {
		if (req.readyState === 4) {
			callback(req.response);
		}
	});
	req.send(datas);
},
inUsername = document.getElementById("inUsername"),
inPassword = document.getElementById("inPassword"),
inMessage = document.getElementById("inMessage"),
outMessage = document.getElementById("outMessage"),
outAlert = document.getElementById("outAlert"),
btSignin = document.getElementById("btSignin"),
btLogin = document.getElementById("btLogin"),
btLogout = document.getElementById("btLogout"),
btSend = document.getElementById("btSend"),
signin = () => {
	request("POST", "src/php/vader.php",
		{
			method: "signin",
			username: inUsername.value,
			password: inPassword.value
		},
		(response) => {
			outAlert.innerHTML = response;
			if (outAlert.innerHTML == "signin") {
				login();
			}
		}
	);
},
login = () => {
	request("POST", "src/php/vader.php",
		{
			method: "login",
			username: inUsername.value,
			password: inPassword.value
		},
		(response) => {
			outAlert.innerHTML = response;
			if (outAlert.innerHTML == "login") {
				localStorage.setItem("configs", JSON.stringify({
					username: inUsername.value,
					password: inPassword.value
				}));
				inUsername.hidden = true;
				inPassword.hidden = true;
				btSignin.hidden = true;
				btLogin.hidden = true;
				btLogout.hidden = false;
			}
		}
	);
},
logout = () => {
	request("POST", "src/php/vader.php",
		{
			method: "logout"
		},
		(response) => {
			outAlert.innerHTML = response;
			if (outAlert.innerHTML == "logout") {
				localStorage.removeItem("configs");
				inUsername.hidden = false;
				inPassword.hidden = false;
				btSignin.hidden = false;
				btLogin.hidden = false;
				btLogout.hidden = true;
			}
		}
	);
},
send = () => {
	request("POST", "src/php/vader.php",
		{
			method: "send",
			message: inMessage.value
		},
		(response) => {
			outAlert.innerHTML = response;
			if (outAlert.innerHTML == "sended") {
				inMessage.value = "";
			}
		}
	);
},
receive = () => {
	request("POST", "src/php/vader.php",
		{
			method: "receive"
		},
		(response)	=> {
			if (outMessage.innerHTML != response) {
				outMessage.innerHTML = response;
			}
			setTimeout(receive, 1000);
		}
	);
};
if (localStorage.getItem("configs") != undefined) {
	inUsername.value = JSON.parse(localStorage.getItem("configs")).username;
	inPassword.value = JSON.parse(localStorage.getItem("configs")).password;
	login();
}
receive();
btSignin.addEventListener("click", signin);
btLogin.addEventListener("click", login);
btLogout.addEventListener("click", logout);
btSend.addEventListener("click", send);
});