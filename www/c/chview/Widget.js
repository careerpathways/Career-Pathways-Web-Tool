
var Widget = Class.create(Component, {
    type: '',
    id: 0,
    x: 0,
    y: 0,
    h: 0,
    w: 0,

    initialize: function(options) {
        this.config = {
            color: DEFAULT_COLOR
        };
        this.entity = true;
        Object.extend(this, options || {});

        this.type = this.getType();
        if (!chColor.include(this.config.color) && this.config.color !== 'transparent') {
            this.config.color = DEFAULT_COLOR;
        }
        if (!chColor.include(this.config.color_background) && this.config.color_background !== 'transparent') {
            this.config.color_background = DEFAULT_COLOR_BACKGROUND;
        }
        this.connectionIDs = [];
        this.outgoingConnections = new Hash();
        this.incomingConnections = new Hash();

        if (this.getElem) {
            Charts.element.appendChild(this.getElem());
            this.repositionElement();
            this._onContentChange();
        }

        if (this.setup) {
            this.setup();
        }
    },

    setColor: function(color) {
        this.color = color;
    },

    /** Returns all connections (both incoming and outgoing). */
    getConnections: function() {
        // Array.concat isn't working in ie7
        return [this.getIncomingConnections(), this.getOutgoingConnections()].flatten();
    },

    /** Registers an outgoing connection (a connection from this widget to another). */
    registerOutgoingConnection: function(connection) {
        this.outgoingConnections.set(connection.destination.id, connection);
    },

    /** Returns all outoing connections. */
    getOutgoingConnections: function() {
        return this.outgoingConnections.values();
    },

    /** Removes an outgoing connection. */
    unregisterOutgoingConnection: function(connection) {
        this.outgoingConnections.unset(connection.destination.id);
    },

    /** Registers an incoming connection (a connection from another widget to this widget). */
    registerIncomingConnection: function(connection) {
        this.incomingConnections.set(connection.source.id, connection);
    },

    /** Returns all incoming connections. */
    getIncomingConnections: function() {
        return this.incomingConnections.values();
    },

    /** Removes an incoming connection. */
    unregisterIncomingConnection: function(connection) {
        this.incomingConnections.unset(connection.source.id);
    },

    /** Returns true if this widget has a connection to the destination. */
    outgoingConnectionExists: function(destination) {
        return this.outgoingConnections.get(destination.id) != null;
    },

    reposition: function() {},

    getWidth: function() {
        return parseInt(this.w);
    },

    getHeight: function() {
        return parseInt(this.h);
    },

    getTop: function() {
        return parseInt(this.y) - 19; //
    },

    getLeft: function() {
        return parseInt(this.x);
    },

    getBottom: function() {
        return this.getTop() + this.getHeight();
    },

    getRight: function() {
        return this.getLeft() + this.getWidth();
    }
});

Widget.toIntegerPoint = function(point) {
    return {
        x: parseInt(point.x),
        y: parseInt(point.y)
    };
};
