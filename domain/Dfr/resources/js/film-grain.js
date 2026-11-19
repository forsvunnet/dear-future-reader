// A film look over the whole UI, like a scan of old, dusty film.
//
// A canvas fixed to the screen draws soft, ragged patches of light, and grain
// and specks that clump inside the light. The light comes from a smooth noise
// field, like clouds. Each scroll moves through the field, and each layer of
// the noise moves at its own speed, so the patches drift and change shape.
// The lime buttons on screen add their own light.
//
// The canvas draws white. In light mode CSS inverts it, so the specks are dark.

// One cell of the light map is this many CSS pixels.
const CELL = 8;
// The light moves this much of each scrolled pixel.
const DRIFT = 0.35;
// The size of the largest patches of light, in cells.
const PATCH = 60;
// The specks drawn at full light. Fewer show where the light is weak.
const SPECKS = 2500;

const canvas = document.createElement('canvas');
canvas.className = 'dfr-film';
canvas.setAttribute('aria-hidden', 'true');
const ctx = canvas.getContext('2d');

// The light map: small, then scaled up, so the light is soft.
const light = document.createElement('canvas');
const lightCtx = light.getContext('2d');

// The grain inside the light: the noise tile, cut to the shape of the light.
const grain = document.createElement('canvas');
const grainCtx = grain.getContext('2d');
const noise = noiseTile(256);

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

let travel = 0;
let queued = false;
const lastTop = new WeakMap();

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

// Fills the light map, one cell at a time. Returns the light of each cell, from 0 to 1.
function drawLight(w, h) {
    const scroll = travel * DRIFT / CELL;
    const buttons = [...document.querySelectorAll('.dfr-button-primary')]
        .map((button) => button.getBoundingClientRect())
        .filter((r) => r.bottom > 0 && r.top < innerHeight);

    const levels = new Float32Array(w * h);
    const image = lightCtx.createImageData(w, h);
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            // Three layers, each smaller and faster than the last.
            const clouds = valueNoise(x / PATCH, (y + scroll) / PATCH) * 0.55
                + valueNoise(x / (PATCH / 2.5) + 31, (y + scroll * 1.6) / (PATCH / 2.5)) * 0.3
                + valueNoise(x / (PATCH / 7) + 77, (y + scroll * 2.4) / (PATCH / 7)) * 0.15;
            let level = ramp(0.58, 0.82, clouds);

            // Streaks: noise that is narrow and very tall, only where it peaks.
            level += ramp(0.72, 0.95, valueNoise(x / 2.5 + 13, (y + scroll * 0.6) / 90)) * 0.35 * clouds;

            for (const r of buttons) {
                const dx = (x * CELL - (r.left + r.width / 2)) / r.width;
                const dy = (y * CELL - (r.top + r.height / 2)) / (r.height * 3);
                level += Math.exp(-(dx * dx + dy * dy) * 2) * 0.8 * clouds;
            }

            level = Math.min(1, level);
            levels[y * w + x] = level;
            const i = (y * w + x) * 4;
            image.data[i] = 255;
            image.data[i + 1] = 255;
            image.data[i + 2] = 255;
            image.data[i + 3] = level * 255;
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
        canvas.width = grain.width = innerWidth;
        canvas.height = grain.height = innerHeight;
        light.width = w;
        light.height = h;
    }

    const levels = drawLight(w, h);
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // The light itself, faint.
    ctx.imageSmoothingEnabled = true;
    ctx.globalAlpha = 0.12;
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
    ctx.globalAlpha = 0.7;
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
    if (reducedMotion.matches) {
        return;
    }
    const target = event.target === document ? document.scrollingElement : event.target;
    const top = target.scrollTop;
    travel += top - (lastTop.get(target) ?? top);
    lastTop.set(target, top);
    queue();
}

document.body.append(canvas);
document.addEventListener('scroll', onScroll, { capture: true, passive: true });
window.addEventListener('resize', queue);
queue();
