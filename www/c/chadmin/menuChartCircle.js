
/* ============================== ChartCircle Menus =========================== */
// create the circle program menu
var circleColorMenu = new YAHOO.widget.Menu('circleColorMenu');
chColor.each(function(color) {
    circleColorMenu.addItem({
        text: '<span style="border:1px solid grey;background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onColorSelect,
            obj: color,
            scope: circleColorMenu
        }
    });
});
circleColorMenu.addItem({
    text: '<span title="Transparent" style="border:1px solid grey;color:red;padding: 0 3px;" title="Transparent">&#216;</span>',
    onclick: {
        fn: onColorSelect,
        obj: 'transparent',
        scope: circleColorMenu
    }
});
var circleColorBackgroundMenu = new YAHOO.widget.Menu('circleColorBackgroundMenu');
chColor.each(function(color) {
    circleColorBackgroundMenu.addItem({
        text: '<span style="border:1px solid grey;background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onColorBackgroundSelect,
            obj: color,
            scope: circleColorBackgroundMenu
        }
    });
});
circleColorBackgroundMenu.addItem({
    text: '<span title="Transparent" style="border:1px solid grey;color:red;padding: 0 3px;" title="Transparent">&#216;</span>',
    onclick: {
        fn: onColorBackgroundSelect,
        obj: 'transparent',
        scope: circleColorBackgroundMenu
    }
});
var circleTitleColorMenu = new YAHOO.widget.Menu('circleTitleColorMenu');
var titleColors = ['ffffff', '000000'];
titleColors.each(function(color) {
    circleTitleColorMenu.addItem({
        text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onEditTitleColorSelect,
            obj: color,
            scope: circleTitleColorMenu
        }
    });
});
// create the box connection menu item
var linkCirclesMenuItem = new YAHOO.widget.MenuItem(LINK_TO_LABEL, {
    onclick: {
        fn: onLinkToSelect
    }
});

// create the box context menu
ChartCircle.contextMenu = new YAHOO.widget.ContextMenu('ChartCircle.contextMenu');
ChartCircle.contextMenu.addItems([
    [{
            text: 'Edit Content',
            onclick: {
                fn: onEditContentSelect
            }
        }, {
            text: 'Border Color',
            submenu: circleColorMenu
        }, {
            text: 'Background Color',
            submenu: circleColorBackgroundMenu
        },
        linkCirclesMenuItem, {
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
