

var newComponentMenu = new YAHOO.widget.Menu('Charts.newComponentMenu');
newComponentMenu.addItems([{
    text: 'Box',
    onclick: {
        fn: onNewBoxSelect
    }
}, {
    text: 'Circle',
    onclick: {
        fn: onNewCircleSelect
    }
}, {
    text: 'Line',
    onclick: {
        fn: onNewLineSelect,
        obj: null
    }
}, {
    text: 'Arrow',
    onclick: {
        fn: onNewLineSelect,
        obj: {
            arrowheadAtEnd: true
        }
    }
}]);

var gridMenu = new YAHOO.widget.Menu('Charts.gridMenu');
var snapToGridMenuItem = new YAHOO.widget.MenuItem('Snap to Grid', {
    onclick: {
        fn: onSnapToGridSelect
    }
});
var showGridMenuItem = new YAHOO.widget.MenuItem('Show Grid', {
    onclick: {
        fn: onDrawGridSelect
    }
});
var gridSizeMenuItem = new YAHOO.widget.MenuItem('Edit Grid Size', {
    onclick: {
        fn: onGridSizeSelect
    }
});

gridMenu.addItems([
    showGridMenuItem,
    snapToGridMenuItem,
    gridSizeMenuItem
]);

Charts.contextMenu = new YAHOO.widget.ContextMenu('Charts.contextMenu');
Charts.contextMenu.addItems([
    [
        //{text: 'Create Box', onclick: {fn: onNewBoxSelect}},
        {
            text: 'New',
            submenu: newComponentMenu
        },
    ],
    [{
        text: 'Grid',
        submenu: gridMenu
    }]
]);

Charts.contextMenu.subscribe('show', function() {
    showGridMenuItem.cfg.setProperty('checked', Charts.drawGrid);
    snapToGridMenuItem.cfg.setProperty('checked', Charts.snapToGrid);
});
