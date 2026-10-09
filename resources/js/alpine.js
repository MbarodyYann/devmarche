// Alpine seul, pour les pages SANS composant Livewire (accueil, À propos, profils, fiches en lecture) :
// environ 16 Ko compressés au lieu des 57 Ko de livewire.min.js. Sur les pages avec un composant,
// Livewire est chargé et fournit déjà Alpine (même version, 3.15.12) : ce fichier n'y est pas chargé.
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
