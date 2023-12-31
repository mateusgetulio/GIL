setTimeout(function () {
  window.location.reload();
}, 15000);

var countdownNumberEl = document.getElementById("countdown-number");
var countdown = 15;

countdownNumberEl.textContent = countdown;

setInterval(function () {
  countdown = --countdown <= 0 ? 15 : countdown;

  countdownNumberEl.textContent = countdown;
}, 1000);
