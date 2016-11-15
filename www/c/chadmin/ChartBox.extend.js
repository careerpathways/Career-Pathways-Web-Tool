
ChartBox.addMethods(WidgetAdmin);
ChartBox.ABSOLUTE_MINIMUM_WIDTH = 20;
ChartBox.DEFAULT_OPTIONS = {
    x: 0,
    y: 100,
    h: 100,
    w: 150,
    config: {
        title: 'title',
        content: 'content',
        content_html: 'content'
    }
};

ChartBox.addMethods({
    setProgram: function(program) {
        this.config.program = program;
        chUtil.ajax({
            a: 'setProgram',
            object_id: this.id,
            program_id: program
        });

        this.setColor();
    },
    changeTitle: function() {
        if (document.editingTitle) return;
        var self = this;
        var input = document.createElement('input');
        input.value = this.config.title;
        input.style.width = (this.w - 20) + 'px';
        Event.observe(input, 'blur', function() {
            self.saveTitle(input);
        }, false);
        this.titleElement.innerHTML = '';
        this.titleElement.appendChild(input);
        input.focus();
        input.select();
        document.editingTitle = true;
    },
    changeTitleColor: function(color) {
        if (color === 'transparent') {
            //this.outerRectangle.setStyle('fillColor', 'rgba(0,0,0,0)');
            this.elem.children[0].style.color = 'rgba(0,0,0,0)';
            this.config.color_title = 'transparent'; //make sure this.reposition() knows about this change
        } else {
            this.elem.children[0].style.color = '#' + color;
            this.config.color_title = color; //make sure this.reposition() knows about this change
        }

        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                config: {
                    color_title: color
                }
            }
        });

        this.reposition(); //update view
    },
    saveTitle: function(input) {
        this.titleElement.innerHTML = input.value || '&nbsp;';
        this.config.title = input.value;
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                config: {
                    title: input.value
                }
            }
        });
        document.editingTitle = false;
        this._onContentChange();
        this.reposition();
        Charts.redraw();
    },
    changeContent: function() {
        if (document.editingBox) return;
        Charts.showEditor(this);
        document.editingBox = true;
    },

    duplicate: function(callback) {
        return Charts.createComponent(ChartBox, {
            x: parseInt(this.x),
            y: parseInt(this.getTop() + this.getHeight()) + 30,
            h: this.h,
            w: this.w,
            config: {
                color: this.config.color.replace(/#/g, ''),
                color_background: this.config.color_background.replace(/#/g, ''),
                title: this.config.title,
                content: this.config.content,
                content_html: this.config.content_html
            }
        }, callback);
    },

    /** Handles a connection action (click, etc.) for a box */
    connect: function() {
        //first click means we are setting the connection source and waiting for more info
        if (Charts.waitingConnectionSource == null) {
            Charts.waitingConnectionSource = this;
            linkBoxesMenuItem.cfg.setProperty('text', LINK_TO_HERE_LABEL);
            linkCirclesMenuItem.cfg.setProperty('text', LINK_TO_HERE_LABEL);
            return;
        }

        //don't allow links to objects we have already linked to
        if (Charts.waitingConnectionSource.outgoingConnectionExists(this)) {
            return;
        }

        linkBoxesMenuItem.cfg.setProperty('text', LINK_TO_LABEL);
        linkCirclesMenuItem.cfg.setProperty('text', LINK_TO_LABEL);

        //clicking self toggles connection source off and on
        if (Charts.waitingConnectionSource.id == this.id) {
            Charts.waitingConnectionSource = null;
            return;
        }

        //second click means we are setting the connection destination
        var connection = this.connectFrom(Charts.waitingConnectionSource);

        //remove half-link-waiting indicators regardless of what connection attempt has been made
        Charts.waitingConnectionSource = null;
    },

    connectFrom: function(beginning) {
        var data = Connection.determineDefaultConnectionData(beginning, this);
        var params = Object.clone(data);
        Object.extend(params, {
            source_id: Charts.waitingConnectionSource.id,
            destination_id: this.id,
            a: 'connect'
        });

        var connection = new Connection(beginning, this, data);

        chUtil.ajax(params, function(ajax) {
            connection.id = ajax.responseText;

            Charts.registerComponent(connection);

            // TODO does this really belong here? should be in event-handling code
            Charts.redraw();
        }.bind(this));

        return connection;
    },

    getControlPoints: function() {
        var left = this.getAnchorPointPosition({
            side: Side.LEFT,
            position: 50
        });
        left.applyPosition = this.applyLeftPosition.bind(this);

        var right = this.getAnchorPointPosition({
            side: Side.RIGHT,
            position: 50
        });
        right.applyPosition = this.applyRightPosition.bind(this);

        if (this.w < this.getMinimumContentWidth()) {
            left.color = '#ff0000';
            right.color = '#ff0000';
        }
        return [
            left,
            right
        ];
    },

    getMinimumContentWidth: function() {
        this.contentElement.style.overflow = 'visible';
        this.titleElement.style.overflow = 'visible';
        var result = Math.max(this.titleElement.offsetWidth, this.contentElement.offsetWidth) / Charts.textSizeMultiplier + 20;

        this.contentElement.style.overflow = 'hidden';
        this.titleElement.style.overflow = 'hidden';

        return result;
    },

    applyRightPosition: function(position) {
        this.w = Math.max(position.x - this.getLeft(), ChartBox.ABSOLUTE_MINIMUM_WIDTH);
        this._onWidthChange(position, Side.RIGHT);
    },

    applyLeftPosition: function(position) {
        this.w = Math.max(this.getRight() - position.x, ChartBox.ABSOLUTE_MINIMUM_WIDTH);
        this.x = position.x;
        this._onWidthChange(position, Side.LEFT);
    },

    _onWidthChange: function(position, side) {
        this.repositionElement();
        this._onContentChange();
        this.reposition();

        var newPosition = this.getAnchorPointPosition({
            side: side,
            position: 50
        });
        position.x = newPosition.x;
        position.y = newPosition.y;
    },

    applyPosition: function(position) {
        this.x = position.x;
        this.y = position.y;
        this.repositionElement();
        this.reposition();
    },

    _onSetColor: function() {
        if (this.config.color === 'transparent') {
            this.outerRectangle.setStyle('fillColor', 'rgba(0,0,0,0)');
        } else {
            this.outerRectangle.setStyle('fillColor', '#' + this.config.color);
        }
    },
    _onSetColorBackground: function() {
        var color;
        if (this.config.color_background === 'transparent') {
            color = 'rgba(0,0,0,0)';
        } else {
            color = '#' + this.config.color_background;
        }
        this.innerRectangle.setStyle('fillColor', color);
    },
    onReshape: function() {
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                x: this.x,
                y: this.y,
                h: this.h,
                w: this.w
            }
        });
    }
});
