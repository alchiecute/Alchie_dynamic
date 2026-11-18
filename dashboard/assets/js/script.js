document.addEventListener("DOMContentLoaded", function () {

    /* 1. MOBILE NAVIGATION */
    const menuToggle = document.getElementById("menuToggle");
    const navbar = document.getElementById("navbar");
    if (menuToggle && navbar) {
        menuToggle.addEventListener("click", () => navbar.classList.toggle("open"));
    }

    /* 2. DARK / LIGHT THEME */
    const themeToggle = document.getElementById("themeToggle");
    const root = document.documentElement;

    function updateThemeIcon() {
        if (!themeToggle) return;
        const dark = root.getAttribute("data-theme") === "dark";
        themeToggle.textContent = dark ? "☀️" : "🌙";
        themeToggle.setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
    }
    updateThemeIcon();

    if (themeToggle) {
        themeToggle.addEventListener("click", function () {
            const dark = root.getAttribute("data-theme") === "dark";
            if (dark) { root.removeAttribute("data-theme"); }
            else { root.setAttribute("data-theme", "dark"); }
            try { localStorage.setItem("portfolioTheme", dark ? "light" : "dark"); } catch (e) {}
            updateThemeIcon();
        });
    }

    /* 3. TYPING EFFECT (words come from the database via data-words) */
    const typingElement = document.getElementById("typing");
    if (typingElement) {
        const words = (typingElement.dataset.words || "").split(",")
            .map(w => w.trim()).filter(Boolean);
        if (words.length) {
            let wordIndex = 0, charIndex = 0, deleting = false;
            (function type() {
                const word = words[wordIndex];
                if (!deleting) {
                    charIndex++;
                    typingElement.textContent = word.substring(0, charIndex);
                    if (charIndex === word.length) {
                        deleting = true;
                        return setTimeout(type, 1000);
                    }
                } else {
                    charIndex--;
                    typingElement.textContent = word.substring(0, charIndex);
                    if (charIndex === 0) {
                        deleting = false;
                        wordIndex = (wordIndex + 1) % words.length;
                    }
                }
                setTimeout(type, deleting ? 50 : 100);
            })();
        }
    }

    /* 4. PROJECT FILTERING */
    const filterButtons = document.querySelectorAll(".filter");
    const projects = document.querySelectorAll(".project-card");
    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            filterButtons.forEach(b => b.classList.remove("active"));
            button.classList.add("active");
            const filter = button.dataset.filter;
            projects.forEach(function (p) {
                p.style.display = (filter === "all" || filter === p.dataset.category) ? "block" : "none";
            });
        });
    });

    /* 5. PROJECT MODAL (textContent only - never innerHTML - to prevent XSS) */
    const modal = document.getElementById("projectModal");
    const closeModal = document.getElementById("closeModal");
    document.querySelectorAll(".details-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            document.getElementById("modalTitle").textContent = button.dataset.title;
            document.getElementById("modalDescription").textContent = button.dataset.description;
            modal.classList.add("show");
        });
    });
    if (closeModal) closeModal.addEventListener("click", () => modal.classList.remove("show"));
    if (modal) {
        modal.addEventListener("click", e => { if (e.target === modal) modal.classList.remove("show"); });
        document.addEventListener("keydown", e => { if (e.key === "Escape") modal.classList.remove("show"); });
    }

    /* 6. CONTACT FORM - client-side hints only; the server re-validates everything */
    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", function (event) {
            const name = document.getElementById("name");
            const email = document.getElementById("email");
            const message = document.getElementById("message");
            const set = (id, text) => { document.getElementById(id).textContent = text; };
            let valid = true;

            set("nameError", ""); set("emailError", ""); set("messageError", "");

            if (name.value.trim().length < 2) {
                set("nameError", "Please enter your name."); valid = false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                set("emailError", "Please enter a valid email."); valid = false;
            }
            if (message.value.trim().length < 10) {
                set("messageError", "Message must contain at least 10 characters."); valid = false;
            }
            if (!valid) event.preventDefault();   // valid forms are posted to PHP
        });
    }

    /* 7. SCROLL TO TOP */
    const scrollTop = document.getElementById("scrollTop");
    if (scrollTop) {
        window.addEventListener("scroll", function () {
            scrollTop.style.display = window.scrollY > 400 ? "block" : "none";
        });
        scrollTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
    }

    /* 8. Confirm destructive admin actions (CSP blocks inline onclick) */
    document.querySelectorAll("form[data-confirm]").forEach(function (f) {
        f.addEventListener("submit", function (e) {
            if (!window.confirm(f.dataset.confirm)) e.preventDefault();
        });
    });
});
