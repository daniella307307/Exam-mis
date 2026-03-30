document.addEventListener("DOMContentLoaded", function () {
    let countdownElement = document.getElementById("countdown");
    let countdownDuration = 60; // 5 minutes in seconds

    function startCountdown() {
        let remainingTime = countdownDuration;

        function updateCountdown() {
            let minutes = Math.floor(remainingTime / 60);
            let seconds = remainingTime % 60;

            countdownElement.textContent = `Session expires in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (remainingTime > 0) {
                remainingTime--;
            } else {
                clearInterval(countdownInterval);
                window.location.href = '../../index'; // Redirect to the login page
            }
        }

        let countdownInterval = setInterval(updateCountdown, 1000);
    }

    startCountdown();
});
