/* ABSTRACT SHAPE
 ******************************************************************************/
var AbstractShape = Class.create({
    /** Returns the shape's boundary rectangle. */
    getBounds: function() {
        return this.bounds;
    },

    /** Resets all styles or a sets single style property of the shape. */
    setStyle: function(style) {
        if (arguments.length == 2) {
            var previousValue = this.style[arguments[0]];
            this.style[arguments[0]] = arguments[1];
            return previousValue;
        } else {
            this.style = style;
        }

        this.onStyleChange(this.style);
    },

    /** Sets a set of styles on the shape. */
    setStyles: function(styles) {
        $H(styles).each(function(entry) {
            this.style[entry.key] = entry.value;
        }.bind(this));
        this.onStyleChange(this.style);
    },

    /** Called when the style of the shape changes. */
    onStyleChange: function() {}
});
