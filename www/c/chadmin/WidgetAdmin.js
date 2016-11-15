/**************************************************************
Chart Utilities/Items -- the actual widgets that live on the chart
***************************************************************/
var WidgetAdmin = {
    remove: function() {
        if (!Charts.confirmDelete(this.type)) {
            return;
        }
        if (this.mMoveHandler) document.stopObserving('mousemove', this.mMoveHandler);
        if (this.mUpHandler) document.stopObserving('mouseup', this.mUpHandler);

        if (this.elem) {
            Charts.element.removeChild(this.elem);
        }
        chUtil.ajax({
            id: this.id,
            a: 'remove'
        });

        // remove all connections to and from this widget
        this.getConnections().invoke('disconnect', true);

        Charts.unregisterComponent(this);
        Charts.redraw();
    },
    setColor: function(color) {
        if (!color) {
            color = '333333';
        }
        this.config.color = color;
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                config: {
                    color: color
                }
            }
        });

        //any connections should inherit the same color, unless that color is transparent.
        if (color !== 'transparent') {
            this.getOutgoingConnections().invoke('setColor', color);
        }

        this._onSetColor();
    },

    _onSetColor: function() {
        this.shape.setStyle('color', '#' + this.config.color);
    },

    setColorBackground: function(color) {
        if (!color) {
            color = 'FFFFFF';
        }
        this.config.color_background = color;
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                config: {
                    color_background: color
                }
            }
        });

        this._onSetColorBackground();
    },

    _onSetColorBackground: function() {
        this.shape.setStyle('fillColor', '#' + this.config.color_background);
    }
}
