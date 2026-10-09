/*
 | Contrôle du débordement à 360 px (checklist de livraison, 09-organisation-equipe.md).
 | À coller dans la console d'une page de http://cadev.test (connecté si besoin).
 | `overflow-x: clip` sur html et body masque les débordements : on mesure donc la position
 | réelle de chaque élément (getBoundingClientRect().right) et non le scrollWidth.
 */
(async () => {
    const pages = ['/', '/missions', '/missions/1', '/missions/publier', '/devs', '/devs/2', '/questions', '/register', '/login', '/page-inexistante'];
    const results = [];

    for (const url of pages) {
        const frame = Object.assign(document.createElement('iframe'), { src: url });
        frame.style.cssText = 'width:360px;height:700px;border:0;position:fixed;left:-9999px';
        document.body.append(frame);
        await new Promise((resolve) => frame.addEventListener('load', () => setTimeout(resolve, 800), { once: true }));

        const doc = frame.contentDocument;
        const width = doc.documentElement.clientWidth;
        const overflowing = [...doc.querySelectorAll('main *, header *, footer *')]
            .filter((el) => el.getClientRects().length && getComputedStyle(el).position !== 'fixed' && !el.closest('.defile'))
            .filter((el) => el.getBoundingClientRect().right > width + 0.5)
            .map((el) => `${el.tagName.toLowerCase()}.${String(el.className.baseVal ?? el.className).slice(0, 40)} → ${Math.round(el.getBoundingClientRect().right)}px`);

        results.push({ page: url, largeur: width, debordements: overflowing.length, exemples: overflowing.slice(0, 3).join(' | ') });
        frame.remove();
    }

    console.table(results);
})();
