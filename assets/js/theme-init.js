// Runs in <head> so the saved theme is applied before the page paints (no flash).
try {
    if (localStorage.getItem("portfolioTheme") === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
    }
} catch (e) { /* storage unavailable */ }
