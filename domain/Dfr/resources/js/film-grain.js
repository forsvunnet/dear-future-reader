// A film look over the whole UI, like a scan of old, dusty film.
//
// A canvas fixed to the screen draws grain and specks that gather on the
// content: each line of text, each border line and each coloured background.
// On each scroll and each change of the page, the script reads where these
// are on screen, from the layout, and draws the grain new.
//
// The light map holds how much grain each place gets. A thing on the page adds
// as much as it stands out from the page background. A blur spreads the map,
// so the specks bleed a little into the space around the letters. A noise field
// then cuts the map, so some words get heavy grain and others almost none.
//
// The canvas draws white. In light mode CSS inverts it, so the specks are dark.
//
// A second canvas draws fine grey grain over all the page. CSS mixes it with
// mix-blend-mode: overlay. Grey at the middle leaves a pixel as it is, so the
// grain makes each pixel below a bit lighter or darker: text, images, icons
// and backgrounds alike.

// One cell of the light map is this many CSS pixels.
const CELL = 4;
// The noise field moves this much of each scrolled pixel, so it moves slower
// than the content, and the grain on a word changes as the word scrolls.
const DRIFT = 0.35;
// The size of the patches of the noise field, in cells.
const PATCH = 40;
// The specks drawn at full light. Fewer show where the light is weak.
const SPECKS = 6000;
// A border line is thin and dim, so it gets at least this much light.
const LINE_LIGHT = 0.4;

const canvas = document.createElement('canvas');
canvas.className = 'dfr-film';
canvas.setAttribute('aria-hidden', 'true');
const ctx = canvas.getContext('2d');

// The fine grain over all the page.
const overlay = document.createElement('canvas');
overlay.className = 'dfr-grain';
overlay.setAttribute('aria-hidden', 'true');
const overlayCtx = overlay.getContext('2d');
const greyNoise = greyTile(256);

// The shapes of the content, in cells, before the blur.
const shapes = document.createElement('canvas');
const shapesCtx = shapes.getContext('2d');

// The light map: the blurred shapes, cut by the noise field.
const light = document.createElement('canvas');
const lightCtx = light.getContext('2d', { willReadFrequently: true });

// The grain inside the light: the noise tile, cut to the shape of the light.
const grain = document.createElement('canvas');
const grainCtx = grain.getContext('2d');
const noise = noiseTile(256);

// A one-pixel canvas turns any CSS colour into red, green and blue.
const probe = document.createElement('canvas').getContext('2d', { willReadFrequently: true });

const darkMode = window.matchMedia('(prefers-color-scheme: dark)');
const range = document.createRange();

let travel = 0;
let queued = false;
const lastTop = new WeakMap();
let looks = new WeakMap();
let colours = new Map();
let background = 0;

// A tile of sparse grain: most pixels are clear, a few are bright.
function noiseTile(size) {
    const tile = document.createElement('canvas');
    tile.width = size;
    tile.height = size;
    const tileCtx = tile.getContext('2d');
    const image = tileCtx.createImageData(size, size);
    for (let i = 0; i < image.data.length; i += 4) {
        image.data[i] = 255;
        image.data[i + 1] = 255;
        image.data[i + 2] = 255;
        image.data[i + 3] = Math.random() ** 4 * 255;
    }
    tileCtx.putImageData(image, 0, 0);
    return tile;
}

// A tile of grey grain around the middle grey, which overlay leaves as it is.
function greyTile(size) {
    const tile = document.createElement('canvas');
    tile.width = size;
    tile.height = size;
    const tileCtx = tile.getContext('2d');
    const image = tileCtx.createImageData(size, size);
    for (let i = 0; i < image.data.length; i += 4) {
        const grey = 128 + (Math.random() - 0.5) * 255;
        image.data[i] = grey;
        image.data[i + 1] = grey;
        image.data[i + 2] = grey;
        image.data[i + 3] = 255;
    }
    tileCtx.putImageData(image, 0, 0);
    return tile;
}

// The brightness and the opacity of a CSS colour, each from 0 to 1.
function colour(css) {
    if (!colours.has(css)) {
        probe.clearRect(0, 0, 1, 1);
        probe.fillStyle = css;
        probe.fillRect(0, 0, 1, 1);
        const [r, g, b, a] = probe.getImageData(0, 0, 1, 1).data;
        // The canvas holds the colour multiplied by its opacity, so divide it out.
        const brightness = a === 0 ? 0 : (0.2126 * r + 0.7152 * g + 0.0722 * b) / a;
        colours.set(css, { brightness, opacity: a / 255 });
    }
    return colours.get(css);
}

// How much a colour stands out from the page background, from 0 to 1.
function contrast(css) {
    const c = colour(css);
    return Math.abs(c.brightness - background) * c.opacity;
}

// The light of the text, the border and the background of an element.
// The styles of an element change rarely, so the result is kept.
function look(element) {
    if (!looks.has(element)) {
        const style = getComputedStyle(element);
        const borders = ['Top', 'Right', 'Bottom', 'Left'].map((side) =>
            style[`border${side}Style`] === 'none' ? 0 : parseFloat(style[`border${side}Width`]));
        looks.set(element, {
            hidden: style.visibility === 'hidden' || style.opacity === '0',
            contents: style.display === 'contents',
            text: contrast(style.color),
            fill: contrast(style.backgroundColor) * 0.5,
            borders,
            line: Math.max(LINE_LIGHT, contrast(style.borderTopColor)),
        });
    }
    return looks.get(element);
}

function onScreen(r) {
    return r.bottom > 0 && r.top < innerHeight && r.right > 0 && r.left < innerWidth;
}

function shape(x, y, w, h, level) {
    shapesCtx.globalAlpha = Math.min(1, level);
    shapesCtx.fillRect(x, y, w, h);
}

// Draws the text, the borders and the backgrounds of an element and its children.
// ponytail: an element off screen is skipped with its children. A child that
// sits outside its parent, for example with position: absolute, can be missed.
function drawElement(element) {
    if (element === canvas || element === overlay || element instanceof SVGElement) {
        return;
    }
    const l = look(element);
    if (l.hidden) {
        return;
    }
    const r = element.getBoundingClientRect();
    if (!l.contents && (r.width === 0 || r.height === 0 || !onScreen(r))) {
        return;
    }

    if (l.fill > 0) {
        shape(r.left, r.top, r.width, r.height, l.fill);
    }
    const [top, right, bottom, left] = l.borders;
    if (top) shape(r.left, r.top, r.width, top, l.line);
    if (right) shape(r.right - right, r.top, right, r.height, l.line);
    if (bottom) shape(r.left, r.bottom - bottom, r.width, bottom, l.line);
    if (left) shape(r.left, r.top, left, r.height, l.line);

    for (const child of element.childNodes) {
        if (child.nodeType === Node.ELEMENT_NODE) {
            drawElement(child);
            continue;
        }
        if (child.nodeType !== Node.TEXT_NODE || l.text === 0 || child.data.trim() === '') {
            continue;
        }
        range.selectNodeContents(child);
        for (const line of range.getClientRects()) {
            if (!onScreen(line)) {
                continue;
            }
            // A line box is taller than the letters, so keep the middle of it.
            shape(line.left, line.top + line.height * 0.2, line.width, line.height * 0.6, l.text);
        }
    }
}

// A fixed random value from 0 to 1 for each point of a whole-number grid.
function hash(x, y) {
    let h = Math.imul(x, 374761393) + Math.imul(y, 668265263);
    h = Math.imul(h ^ (h >>> 13), 1274126177);
    return ((h ^ (h >>> 16)) >>> 0) / 4294967296;
}

function smooth(t) {
    return t * t * (3 - 2 * t);
}

// Smooth noise from 0 to 1: the grid values, blended between the grid points.
function valueNoise(x, y) {
    const x0 = Math.floor(x);
    const y0 = Math.floor(y);
    const tx = smooth(x - x0);
    const ty = smooth(y - y0);
    const top = hash(x0, y0) + (hash(x0 + 1, y0) - hash(x0, y0)) * tx;
    const bottom = hash(x0, y0 + 1) + (hash(x0 + 1, y0 + 1) - hash(x0, y0 + 1)) * tx;
    return top + (bottom - top) * ty;
}

// 0 below the edge, 1 above the top, and a smooth step between.
function ramp(edge, top, value) {
    return smooth(Math.min(1, Math.max(0, (value - edge) / (top - edge))));
}

// Fills the light map. Returns the light of each cell, from 0 to 1.
function drawLight(w, h) {
    background = colour(getComputedStyle(document.body).backgroundColor).brightness;

    shapesCtx.setTransform(1, 0, 0, 1, 0, 0);
    shapesCtx.clearRect(0, 0, w, h);
    shapesCtx.setTransform(1 / CELL, 0, 0, 1 / CELL, 0, 0);
    shapesCtx.fillStyle = '#fff';
    shapesCtx.globalCompositeOperation = 'lighter';
    drawElement(document.body);

    // A tight blur for the letters, and a wide, faint one for the bleed around them.
    lightCtx.clearRect(0, 0, w, h);
    lightCtx.globalCompositeOperation = 'lighter';
    lightCtx.filter = 'blur(1px)';
    lightCtx.globalAlpha = 1;
    lightCtx.drawImage(shapes, 0, 0);
    lightCtx.filter = 'blur(4px)';
    lightCtx.globalAlpha = 0.6;
    lightCtx.drawImage(shapes, 0, 0);
    lightCtx.filter = 'none';
    lightCtx.globalAlpha = 1;
    lightCtx.globalCompositeOperation = 'source-over';

    const scroll = travel * DRIFT / CELL;
    const levels = new Float32Array(w * h);
    const image = lightCtx.getImageData(0, 0, w, h);
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            const field = valueNoise(x / PATCH, (y + scroll) / PATCH) * 0.65
                + valueNoise(x / (PATCH / 3) + 31, (y + scroll * 1.6) / (PATCH / 3)) * 0.35;
            const i = y * w + x;
            const level = image.data[i * 4 + 3] / 255 * ramp(0.35, 0.7, field);
            levels[i] = level;
            image.data[i * 4] = 255;
            image.data[i * 4 + 1] = 255;
            image.data[i * 4 + 2] = 255;
            image.data[i * 4 + 3] = level * 255;
        }
    }
    lightCtx.putImageData(image, 0, 0);
    return levels;
}

function draw() {
    queued = false;
    const w = Math.ceil(innerWidth / CELL);
    const h = Math.ceil(innerHeight / CELL);
    if (canvas.width !== innerWidth || canvas.height !== innerHeight) {
        canvas.width = grain.width = overlay.width = innerWidth;
        canvas.height = grain.height = overlay.height = innerHeight;
        shapes.width = light.width = w;
        shapes.height = light.height = h;
    }

    // The fine grain. A new random offset each time, so the grain changes.
    overlayCtx.save();
    overlayCtx.translate(-Math.random() * 256, -Math.random() * 256);
    overlayCtx.fillStyle = overlayCtx.createPattern(greyNoise, 'repeat');
    overlayCtx.fillRect(0, 0, overlay.width + 256, overlay.height + 256);
    overlayCtx.restore();

    const levels = drawLight(w, h);
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // A faint glow on the content.
    ctx.imageSmoothingEnabled = true;
    ctx.globalAlpha = 0.08;
    ctx.drawImage(light, 0, 0, canvas.width, canvas.height);

    // The grain, cut to the light. A new random offset each time, so the grain changes.
    grainCtx.globalCompositeOperation = 'source-over';
    grainCtx.clearRect(0, 0, grain.width, grain.height);
    grainCtx.save();
    grainCtx.translate(-Math.random() * 256, -Math.random() * 256);
    grainCtx.fillStyle = grainCtx.createPattern(noise, 'repeat');
    grainCtx.fillRect(0, 0, grain.width + 256, grain.height + 256);
    grainCtx.restore();
    grainCtx.globalCompositeOperation = 'destination-in';
    grainCtx.drawImage(light, 0, 0, grain.width, grain.height);
    ctx.globalAlpha = 0.8;
    ctx.drawImage(grain, 0, 0);

    // The specks: a random place keeps its speck only as often as the light there allows.
    ctx.fillStyle = '#fff';
    for (let i = 0; i < SPECKS; i++) {
        const x = Math.random() * canvas.width;
        const y = Math.random() * canvas.height;
        const level = levels[Math.floor(y / CELL) * w + Math.floor(x / CELL)];
        if (Math.random() > level ** 2) {
            continue;
        }
        ctx.globalAlpha = 0.4 + Math.random() * 0.6;
        // Most specks are dots. A few are a clump of dots.
        const dots = Math.random() < 0.15 ? 6 : 1;
        for (let d = 0; d < dots; d++) {
            const size = Math.random() < 0.9 ? 1 : 2;
            ctx.fillRect(x + (Math.random() - 0.5) * 6 * (dots > 1), y + (Math.random() - 0.5) * 6 * (dots > 1), size, size);
        }
    }
    ctx.globalAlpha = 1;
}

function queue() {
    if (queued) {
        return;
    }
    queued = true;
    requestAnimationFrame(draw);
}

// The page and the sidebar scroll apart, so each scroll adds its own distance.
function onScroll(event) {
    const target = event.target === document ? document.scrollingElement : event.target;
    const top = target.scrollTop;
    travel += top - (lastTop.get(target) ?? top);
    lastTop.set(target, top);
    queue();
}

// New colours come with the other mode, so the kept styles are old.
function onModeChange() {
    looks = new WeakMap();
    colours = new Map();
    queue();
}

// Livewire changes the page in place. A new class can give an element a new
// colour, so the kept styles are old.
function onPageChange() {
    looks = new WeakMap();
    queue();
}

document.body.append(overlay, canvas);
document.addEventListener('scroll', onScroll, { capture: true, passive: true });
window.addEventListener('resize', queue);
darkMode.addEventListener('change', onModeChange);
new MutationObserver(onPageChange).observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true,
    attributes: true,
    attributeFilter: ['class', 'style'],
});
queue();
