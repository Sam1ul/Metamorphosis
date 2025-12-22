const { app, BrowserWindow, screen, ipcMain, globalShortcut } = require('electron');
const path = require('path');

let win;

function createWindow() {
    const { width, height } = screen.getPrimaryDisplay().workAreaSize;

    const windowWidth = Math.min(width * 0.9, 1200);
    const windowHeight = Math.min(height * 0.9, 650);

    win = new BrowserWindow({
        width: windowWidth,
        height: windowHeight,
        minWidth: 800,
        minHeight: 500,
        autoHideMenuBar: true,
        frame: false,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            preload: path.join(__dirname, 'preload.js'),
                            webSecurity: true
        }
    });

     win.loadURL('https://metamorphosis.pythonanywhere.com');
    //win.loadURL('http://127.0.0.1:8000');
    app.whenReady().then(() => {
        // Window control shortcuts
        globalShortcut.register('CommandOrControl+Q', () => app.quit());
        globalShortcut.register('CommandOrControl+M', () => {
            if (win.isMinimized()) win.restore();
            else win.minimize();
        });

            // Browser-like zoom shortcuts
            globalShortcut.register('CommandOrControl+Plus', () => {
                if (win) win.webContents.setZoomFactor(win.webContents.getZoomFactor() + 0.1);
            });

                globalShortcut.register('CommandOrControl+-', () => {
                    if (win) win.webContents.setZoomFactor(Math.max(0.1, win.webContents.getZoomFactor() - 0.1));
                });

                    globalShortcut.register('CommandOrControl+0', () => {
                        if (win) win.webContents.setZoomFactor(1.0);
                    });
    });
}

// Create window when app ready
app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') app.quit();
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
});

// Handle window control events from renderer via IPC
ipcMain.on('window-control', (event, action) => {
    if (!win) return;
    switch(action) {
        case 'minimize': win.minimize(); break;
        case 'maximize': win.isMaximized() ? win.unmaximize() : win.maximize(); break;
        case 'close': win.close(); break;
    }
});

app.on('will-quit', () => {
    globalShortcut.unregisterAll();
});


