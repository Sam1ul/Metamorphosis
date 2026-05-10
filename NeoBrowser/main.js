const { app, BrowserWindow, globalShortcut } = require("electron");

let win;

function createWindow() {
  win = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      webviewTag: true,
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  win.loadFile("index.html");
}

app.whenReady().then(() => {
  createWindow();

  // F12 toggle DevTools
  globalShortcut.register("F12", () => {
    if (win) {
      win.webContents.toggleDevTools();
    }
  });

  // optional fallback shortcut
  globalShortcut.register("CommandOrControl+Shift+I", () => {
    if (win) {
      win.webContents.toggleDevTools();
    }
  });
});
