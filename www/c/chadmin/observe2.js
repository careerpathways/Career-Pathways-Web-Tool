document.observe('chart:drawn', function() {
    Charts.contextMenu.render(Charts.element);
    ChartBox.contextMenu.render(Charts.element);
    ChartCircle.contextMenu.render(Charts.element);
    Connection.contextMenu.render(Charts.element);
    widgetContextMenu.render(Charts.element);
    onDrawGridSelect();
});
