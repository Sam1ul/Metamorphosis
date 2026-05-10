const { contextBridge } = require('electron');

contextBridge.exposeInMainWorld('caesar', {
    encode: (text, shift) => {
        shift = shift % 26; // ensure shift is within alphabet
        let result = '';
        for (let char of text) {
            if (char.match(/[a-z]/i)) {
                const code = char.charCodeAt(0);
                const base = code >= 65 && code <= 90 ? 65 : 97; // uppercase or lowercase
                result += String.fromCharCode(((code - base + shift + 26) % 26) + base);
            } else {
                result += char;
            }
        }
        return result;
    }
});
