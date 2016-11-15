/* ============================== ChartBox Menus =========================== */
// create the box program menu
var boxColorMenu = new YAHOO.widget.Menu('boxColorMenu');
chColor.each(function(color) {
    boxColorMenu.addItem({
        text: '<span style="border:1px solid grey;background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onColorSelect,
            obj: color,
            scope: boxColorMenu
        }
    });
});
boxColorMenu.addItem({
    text: '<span title="Transparent" style="border:1px solid grey;color:red;padding: 0 3px;" title="My tip">&#216;</span>',
    onclick: {
        fn: onColorSelect,
        obj: 'transparent',
        scope: boxColorMenu
    }
});

var boxColorBackgroundMenu = new YAHOO.widget.Menu('boxColorBackgroundMenu');
chColor.each(function(color) {
    boxColorBackgroundMenu.addItem({
        text: '<span style="border:1px solid grey;background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onColorBackgroundSelect,
            obj: color,
            scope: boxColorBackgroundMenu
        }
    });
});
boxColorBackgroundMenu.addItem({
    text: '<span title="Transparent" style="border:1px solid grey;color:red;padding: 0 3px;" title="Transparent">&#216;</span>',
    onclick: {
        fn: onColorBackgroundSelect,
        obj: 'transparent',
        scope: boxColorBackgroundMenu
    }
});
var boxTitleColorMenu = new YAHOO.widget.Menu('boxTitleColorMenu');
var titleColors = ['ffffff', '000000'];
titleColors.each(function(color) {
    boxTitleColorMenu.addItem({
        text: '<span style="border:1px solid grey;background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onEditTitleColorSelect,
            obj: color,
            scope: boxTitleColorMenu
        }
    });
});
boxTitleColorMenu.addItem({
    text: '<span title="Transparent" style="border:1px solid grey;color:red;padding: 0 3px;" title="Transparent">&#216;</span>',
    onclick: {
        fn: onEditTitleColorSelect,
        obj: 'transparent',
        scope: boxTitleColorMenu
    }
});
// create the box connection menu item
var linkBoxesMenuItem = new YAHOO.widget.MenuItem(LINK_TO_LABEL, {
    onclick: {
        fn: onLinkToSelect
    }
});
// create the box context menu
ChartBox.contextMenu = new YAHOO.widget.ContextMenu('ChartBox.contextMenu');
ChartBox.contextMenu.addItems([
    [{
            text: 'Edit Content',
            onclick: {
                fn: onEditContentSelect
            }
        }, {
            text: 'Edit Title',
            onclick: {
                fn: onEditTitleSelect
            }
        }, {
            text: 'Title Color',
            submenu: boxTitleColorMenu
        }, {
            text: 'Border Color',
            submenu: boxColorMenu
        }, {
            text: 'Background Color',
            submenu: boxColorBackgroundMenu
        },
        linkBoxesMenuItem, {
            text: 'Duplicate',
            onclick: {
                fn: onDuplicateSelect
            }
        }
    ],
    [{
        text: 'Delete',
        onclick: {
            fn: onDeleteSelect
        }
    }]
]);
