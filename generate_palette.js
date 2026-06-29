function hexToRgb(hex) {
    let r = 0, g = 0, b = 0;
    if (hex.length == 4) {
        r = "0x" + hex[1] + hex[1];
        g = "0x" + hex[2] + hex[2];
        b = "0x" + hex[3] + hex[3];
    } else if (hex.length == 7) {
        r = "0x" + hex[1] + hex[2];
        g = "0x" + hex[3] + hex[4];
        b = "0x" + hex[5] + hex[6];
    }
    return [parseInt(r, 16), parseInt(g, 16), parseInt(b, 16)];
}

function rgbToHex(r, g, b) {
    return "#" + (1 << 24 | r << 16 | g << 8 | b).toString(16).slice(1).toUpperCase();
}

function mix(color1, color2, weight) {
    let w = weight / 100;
    let r = Math.round(color1[0] * w + color2[0] * (1 - w));
    let g = Math.round(color1[1] * w + color2[1] * (1 - w));
    let b = Math.round(color1[2] * w + color2[2] * (1 - w));
    return [r, g, b];
}

const base = hexToRgb('#4C940C');
const white = [255, 255, 255];
const black = [0, 0, 0];

const palette = {
    50: rgbToHex(...mix(base, white, 10)),
    100: rgbToHex(...mix(base, white, 20)),
    200: rgbToHex(...mix(base, white, 40)),
    300: rgbToHex(...mix(base, white, 60)),
    400: rgbToHex(...mix(base, white, 80)),
    500: rgbToHex(...base),
    600: rgbToHex(...mix(base, black, 80)),
    700: rgbToHex(...mix(base, black, 60)),
    800: rgbToHex(...mix(base, black, 40)),
    900: rgbToHex(...mix(base, black, 20)),
    950: rgbToHex(...mix(base, black, 10)),
};

console.log(JSON.stringify(palette, null, 4));
