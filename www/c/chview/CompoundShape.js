
/* COMPOUND SHAPE
 ******************************************************************************/
var CompoundShape = Class.create(AbstractShape, {
    initialize: function(components) {
        this.bounds = null;
        this.resetShapes(components);
    },

    /** Returns the points that make up all of the components. */
    getPoints: function() {
        return this.components.invoke('getPoints').flatten();
    },

    /** Adds a component to the compound shape, expanding the bounding
     *  rectangle to include the new shape.
     */
    addShape: function(component) {
        this.components.push(component);
        if (this.bounds == null) {
            this.bounds = component.getBounds();
        } else {
            this.bounds = this.bounds.compound(component.getBounds());
        }
    },

    /** Replaces all current shapes in the compound shape. */
    resetShapes: function(components) {
        this.components = components || [];

        this.recalculateBounds();
    },

    /** Recalculates the boundary rectangle. Used if the shapes that comprise
     *  the compound shape are repositioned.
     */
    recalculateBounds: function() {
        this.bounds = new Geometry.Bounds(this.components.invoke('getBounds').invoke('getPoints').flatten());
    },

    draw: function(context) {
        this.components.invoke('draw', context);
    },

    /** Returns true if any of the shapes that comprise the compound shape
     *  contain the point.
     */
    contains: function(point) {
        return
        this.bounds.contains(point) &&
            this.components.any(function(component) {
                return component.contains(point);
            });
    }
});
