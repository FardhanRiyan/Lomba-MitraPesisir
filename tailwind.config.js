/** @type {import('tailwindcss').Config} */
module.exports = {
  // Kelas Tailwind juga dipakai di file PHP (enum badge, helper), jadi ikut dipindai.
  content: ['./resources/views/**/*.blade.php', './app/**/*.php', './public/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        navy: { 900: '#0A2647', 800: '#123A5E' },
        blue: { 600: '#1E7FE0', 500: '#2F8FEF', 100: '#E4F1FD' },
        sky: { 50: '#EEF7FC' },
        teal: { 500: '#149E8C', 100: '#DFF6F1' },
        amber: { 500: '#E68A2E', 100: '#FBEAD7' },
        grey: { 700: '#3C4A5C', 600: '#5C7089', 400: '#93A4B8', 100: '#EEF2F6' },
        danger: '#D9534F',
        whatsapp: '#25A25A',
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
        serif: ['Playfair Display', 'Georgia', 'serif'],
      },
      borderRadius: { lg: '22px', md: '16px', sm: '10px' },
      boxShadow: {
        card: '0 10px 28px rgba(10,38,71,.07)',
        lift: '0 18px 36px rgba(10,38,71,.14)',
      },
    },
  },
  plugins: [],
};
