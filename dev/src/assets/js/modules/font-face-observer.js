import FontFaceObserver from 'fontfaceobserver';

export default function (fonts, cb) {
    const html = document.documentElement;
    const fontsObservers = [];

    if (!Array.isArray(fonts) ) {
        return false;
    }

    fonts.forEach((item) => {
        const font = new FontFaceObserver(item.name);
        fontsObservers.push(font.load(null, 60000));
        font.load(null, 60000).then(() => {
            html.classList.add(item.className);
        });
    });

    if (typeof cb === 'function') {
        Promise.all(fontsObservers).then(cb);
    }
}
