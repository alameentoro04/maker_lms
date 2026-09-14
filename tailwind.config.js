/** @type {import('tailwindcss').Config} */
export default {
    content: ["./resources/views/**/*.blade.php", "./resources/js/**/*.tsx"],
    theme: {
        extend: {
            // Placeholder brand tokens — swap for the real Makers palette/typeface
            // once brand assets are supplied (see README "Brand tokens").
            colors: {
                ink: {
                    50: "#f4f6f9",
                    100: "#e6eaf1",
                    300: "#a9b6cc",
                    500: "#4c5f7d",
                    700: "#26374f",
                    900: "#14243b",
                },
                gold: {
                    50: "#fbf3e6",
                    300: "#e3b467",
                    400: "#d6a147",
                    500: "#c98a2c",
                    700: "#96631d",
                },
                paper: "#f7f5f1",
            },
            fontFamily: {
                sans: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
                display: ['"Fraunces"', "ui-serif", "Georgia", "serif"],
            },
        },
    },
    plugins: [],
};
