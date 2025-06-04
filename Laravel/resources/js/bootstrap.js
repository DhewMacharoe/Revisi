import axios from "axios";
import Echo from "laravel-echo";

window.axios = axios;
window.io = require("socket.io-client");

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.Echo = new Echo({
    broadcaster: "socket.io",
    host: window.location.hostname + ":6001",
});
