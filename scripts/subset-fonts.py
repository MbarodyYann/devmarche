"""
Réduit les polices de public/fonts (latin) : sous-ensemble Latin-1 + ponctuation française,
et plage de graisse limitée à celle utilisée par le site. Gain d'environ 33 % (3G).

Sources : Fontsource 5.3 (Newsreader, Schibsted Grotesk), licence SIL OFL 1.1 (fichiers OFL-*.txt).
Outil : fontTools (MIT) et brotli (MIT), dans un environnement virtuel jetable :
    python3 -m venv /tmp/fonts-venv && /tmp/fonts-venv/bin/pip install fonttools brotli
    /tmp/fonts-venv/bin/python scripts/subset-fonts.py chemin/vers/les/originaux public/fonts
Les fichiers d'origine (Fontsource) sont dans l'historique git (avant le lot 7).
"""
import os
import sys

from fontTools import subset
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer

SOURCE, TARGET = sys.argv[1], sys.argv[2]

# Latin de base, Latin-1, œ Œ ı, ponctuation générale (– — ‘ ’ “ ” … espace fine insécable), €, ™, flèches, −.
UNICODES = (
    list(range(0x20, 0x7F)) + list(range(0xA0, 0x100))
    + [0x131, 0x152, 0x153, 0x2BC, 0x2C6, 0x2DA, 0x2DC]
    + list(range(0x2010, 0x2027)) + [0x202F, 0x2030, 0x2039, 0x203A, 0x2044, 0x20AC, 0x2122]
    + [0x2190, 0x2191, 0x2192, 0x2193, 0x2212]
)

# Graisses utilisées : Newsreader 400 à 600 (titres), Schibsted 400 à 700 (texte, titres du Markdown).
FONTS = {
    'newsreader-latin-wght-normal': (400, 600),
    'newsreader-latin-wght-italic': (400, 600),
    'schibsted-grotesk-latin-wght-normal': (400, 700),
}

for name, (low, high) in FONTS.items():
    font = TTFont(os.path.join(SOURCE, name + '.woff2'))

    options = subset.Options()
    options.flavor = 'woff2'
    options.layout_features = ['kern', 'liga', 'calt', 'ccmp', 'locl', 'mark', 'mkmk', 'tnum', 'lnum', 'pnum', 'case']
    options.name_IDs = ['*']
    options.notdef_outline = True
    subsetter = subset.Subsetter(options)
    subsetter.populate(unicodes=UNICODES)
    subsetter.subset(font)

    font = instancer.instantiateVariableFont(font, {'wght': (low, high)})
    font.flavor = 'woff2'
    font.save(os.path.join(TARGET, name + '.woff2'))
    print(name, os.path.getsize(os.path.join(TARGET, name + '.woff2')), 'octets')
