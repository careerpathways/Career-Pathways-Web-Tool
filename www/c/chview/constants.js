var VERTICAL = 'v';
var HORIZONTAL = 'h';
var DEFAULT_COLOR = '333333';
var DEFAULT_COLOR_BACKGROUND = 'FFFFFF';

var ARROW_LENGTH_MULTIPLIER = 1.5;
var ARROW_THICKNESS_MULTIPLIER = 3.5;

var Side = {
    TOP: 'n',
    BOTTOM: 's',
    RIGHT: 'e',
    LEFT: 'w'
};

var AnchorPoint = {
    TOP_LEFT: {
        side: Side.TOP,
        position: 0
    },
    TOP_CENTER: {
        side: Side.TOP,
        position: 50
    },
    TOP_RIGHT: {
        side: Side.TOP,
        position: 100
    },
    MIDDLE_LEFT: {
        side: Side.LEFT,
        position: 50
    },
    MIDDLE_RIGHT: {
        side: Side.RIGHT,
        position: 50
    },
    BOTTOM_LEFT: {
        side: Side.BOTTOM,
        position: 0
    },
    BOTTOM_CENTER: {
        side: Side.BOTTOM,
        position: 50
    },
    BOTTOM_RIGHT: {
        side: Side.BOTTOM,
        position: 100
    }
};
