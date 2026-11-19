// Static hosting has no PHP, so a valid form opens the visitor's email app instead.
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contactForm");
    if (!form) return;
    form.addEventListener("submit", function (event) {
        if (event.defaultPrevented) return;        // validation failed (handled in script.js)
        event.preventDefault();
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const message = document.getElementById("message").value.trim();
        const subject = encodeURIComponent("Portfolio message from " + name);
        const body = encodeURIComponent(message + "\n\n— " + name + " (" + email + ")");
        window.location.href = "mailto:" + form.dataset.to + "?subject=" + subject + "&body=" + body;
        const status = document.getElementById("formStatus");
        if (status) { status.textContent = "Opening your email app…"; }
    });
});
