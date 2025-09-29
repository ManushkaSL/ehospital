/*document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;

    // Create toggle button dynamically if not already added
    if (!document.getElementById("darkModeToggle")) {
        const toggle = document.createElement("button");
        toggle.id = "darkModeToggle";
        toggle.innerHTML = "🌙"; // default icon
        document.body.appendChild(toggle);

        // Apply inline styles (optional, can also use CSS)
        toggle.style.cssText = `
            position: fixed;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            background: #ffffffcc;
            color: #000;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            z-index: 1000;
            transition: background 0.3s, transform 0.2s;
        `;

        // Load saved theme from localStorage
        if (localStorage.getItem("theme") === "dark") {
            body.classList.add("dark-mode");
            toggle.innerHTML = "☀️";
            toggle.style.background = "#333333cc";
            toggle.style.color = "#fff";
        }

        // Toggle dark mode on click
        toggle.addEventListener("click", () => {
            body.classList.toggle("dark-mode");

            if (body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
                toggle.innerHTML = "☀️";
                toggle.style.background = "#333333cc";
                toggle.style.color = "#fff";
            } else {
                localStorage.setItem("theme", "light");
                toggle.innerHTML = "🌙";
                toggle.style.background = "#ffffffcc";
                toggle.style.color = "#000";
            }
        });

        // Responsive adjustment for small screens
        const adjustPosition = () => {
            if (window.innerWidth <= 768) {
                toggle.style.top = "15px";
                toggle.style.right = "10px";
            } else {
                toggle.style.top = "20px";
                toggle.style.right = "20px";
            }
        };

        // Run initially and on resize
        adjustPosition();
        window.addEventListener("resize", adjustPosition);
    }
});
*/

document.addEventListener("DOMContentLoaded", () => { 
    const body = document.body;

    if (!document.getElementById("darkModeToggle")) {
        const toggle = document.createElement("button");
        toggle.id = "darkModeToggle";
        toggle.innerHTML = "🌙"; // default icon
        document.body.appendChild(toggle);

        // Load saved theme from localStorage
        if (localStorage.getItem("theme") === "dark") {
            body.classList.add("dark-mode");
            toggle.innerHTML = "☀️";
        }

        toggle.addEventListener("click", () => {
            body.classList.toggle("dark-mode");

            if (body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
                toggle.innerHTML = "☀️";
            } else {
                localStorage.setItem("theme", "light");
                toggle.innerHTML = "🌙";
            }
        });
    }
});
