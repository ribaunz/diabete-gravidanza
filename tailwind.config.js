/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/Views/**/*.php', './public/assets/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        verde: {
          50: '#f0fdf9', 100: '#ccfbef', 200: '#99f6e0', 300: '#5eead4',
          400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
          800: '#115e59', 900: '#134e4a',
        },
        sabbia: {
          50: '#faf9f7', 100: '#f4f1ec', 200: '#e8e2d9',
        },
      },
      fontFamily: {
        sans: ['system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
