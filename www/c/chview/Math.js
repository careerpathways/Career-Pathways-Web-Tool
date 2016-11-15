
/* MATH
 ******************************************************************************/
Object.extend(Math, {
    minArray: function(array) {
        return Math.min.apply(Math, array);
    },

    maxArray: function(array) {
        return Math.max.apply(Math, array);
    }
});
