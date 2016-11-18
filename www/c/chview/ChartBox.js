/***
 basic box functions
***/
ChartBox = Class.create(Widget, {
    getType: function() {
        return 'box';
    },

    elem: null,

    getElem: function() {
        /* NOTE: putting the class in the constructor, i.e.
         * new Element('div', {'class': 'ctepathwaysBox'}); doesn't work in IE
         * 8 with Prototype earlier than v1.6.1. See
         * https://prototype.lighthouseapp.com/projects/8886/tickets/529 */
        this.elem = new Element('div').addClassName('ctepathwaysBox');

        if (this.w > 0) this.elem.style.width = (this.getWidth() - 20) + 'px';

        this.titleElement = new Element('div').addClassName('ctepathwaysBoxTitle');
        this.contentElement = new Element('div').addClassName('ctepathwaysBoxContent');

        this.elem.appendChild(this.titleElement);
        this.elem.appendChild(this.contentElement);

        this.titleElement.update(this.config.title || '&nbsp;');
        this.contentElement.update(this.config.content_html);

        // Set default color if not defined
        if (!this.config.color_title) {
            this.config.color_title = 'ffffff';
        }

        // Assign color value. Special case for transparent.
        if (this.config.color_title == 'transparent') {
            this.titleElement.style.color = 'rgba(0,0,0,0)';
        } else {
            this.titleElement.style.color = '#' + this.config.color_title;
        }

        this.elem.style.zIndex = "0";
        return this.elem;
    },

    /**
     * Small helper function to get correct properties for innerRectangle.
     * Handles special case where we need rounded corners if title is hidden.
     */
    getInnerRectangleProps: function() {
        if('transparent' !== this.config.color_background && this.config.color_background.indexOf('#') < 0){
            this.config.color_background = '#' + this.config.color_background;
        }

        var _props = {
            fillColor: this.config.color_background,
            fill: true,
            bottomLeftRadius: this.borderThickness,
            bottomRightRadius: this.borderThickness
        };

        // Round the inner rectangles top corners if the title is transparent.
        // We hide the title if it's transparent (by removing its geometry)
        if (this.config.color_title == 'transparent') {
            _props.topLeftRadius = this.borderThickness;
            _props.topRightRadius = this.borderThickness;
        } else {
            //Make sure to remove rounded corners, e.g. when interactively editing.
            _props.topLeftRadius = 0;
            _props.topRightRadius = 0;
        }
        return _props;
    },

    createShape: function() {
        this.borderThickness = 5;
        if (this.config.color_background === 'transparent') {
            this.config.color_background = 'rgba(0,0,0,0)';
        } else {
            this.config.color_background = '#' + this.config.color_background;
        }

        this.innerRectangle = new Rectangle({
            x: 0,
            y: 0
        }, 0, 0, this.getInnerRectangleProps());

        if (this.config.color === 'transparent') {
            this.config.color = 'rgba(0,0,0,0)';
        } else {
            this.config.color = '#' + this.config.color;
        }
        this.outerRectangle = new Rectangle({
            x: 0,
            y: 0
        }, 0, 0, {
            fillColor: this.config.color,
            strokeColor: '#F00000',
            fill: true,
            strokeWidth: 2,
            radius: this.borderThickness * 2
        });

        return new CompoundShape([this.outerRectangle, this.innerRectangle]);
    },

    getLayer: function() {
        return 1;
    },

    getHeight: function() {
        return this.height;
    },

    /** Returns the position of the anchor point. */
    getAnchorPointPosition: function(anchorPoint) {
        var result;

        switch (anchorPoint.side) {
            case Side.TOP:
                result = {
                    y: this.getTop()
                };
                break;
            case Side.BOTTOM:
                result = {
                    y: this.getBottom()
                };
                break;
            case Side.LEFT:
                result = {
                    x: this.getLeft()
                };
                break;
            case Side.RIGHT:
                result = {
                    x: this.getRight()
                };
                break;
        }

        switch (anchorPoint.side) {
            case Side.TOP:
            case Side.BOTTOM:
                result.x = this.getLeft() + this.getWidth() * anchorPoint.position / 100;
                break;
            case Side.LEFT:
            case Side.RIGHT:
                result.y = this.getTop() + this.getHeight() * anchorPoint.position / 100;
                break;
        }

        return result;
    },

    repositionElement: function() {
        this.elem.style.top = (this.getTop()) * Charts.textSizeMultiplier + 'px';
        this.elem.style.left = (this.getLeft() + 10) * Charts.textSizeMultiplier + 'px';
        this.elem.style.width = (this.getWidth() - 20) * Charts.textSizeMultiplier + 'px';
    },

    /** @deprecated */
    _onContentChange: function() {
        // This work is now handled within this.reposition() for consolidation
    },

    reposition: function() {
        var titlePadding = 4 * Charts.textSizeMultiplier + 'px';
        this.titleElement.style.paddingTop = titlePadding;
        this.titleElement.style.paddingBottom = titlePadding;

        var titlePadding,
            titleHeight,
            contentHeight,
            totalHeight;

        if (this.config.color_title == 'transparent') {
            // Title is hidden, alter the ChartBox's geometry as necessary.
            titlePadding = 0;
            titleHeight = this.borderThickness;
        } else {
            titlePadding = 4 * Charts.textSizeMultiplier + 'px';
            titleHeight = this.titleElement.offsetHeight / Charts.textSizeMultiplier;
        }

        contentHeight = this.contentElement.offsetHeight / Charts.textSizeMultiplier;
        totalHeight = titleHeight + contentHeight + 10;

        this.titleElement.style.paddingTop = titlePadding;
        this.titleElement.style.paddingBottom = titlePadding;
        this.titleHeight = titleHeight
        this.contentHeight = contentHeight;
        // Shift content up and down according to title height
        this.contentElement.style.top = titleHeight + 3 +'px';
        this.height = totalHeight;

        // console.log('titlePadding ' + titlePadding);
        // console.log('titleHeight ' + titleHeight);
        // console.log('contentHeight ' + contentHeight);
        // console.log('totalHeight ' + totalHeight);

        var pos = {
            x: this.getLeft(),
            y: this.getTop()
        };

        var thickness = this.borderThickness;
        this.outerRectangle.reposition(pos, this.getWidth(), this.getHeight());

        this.innerRectangle.reposition(
            Geometry.translatedPoint(pos, thickness, this.titleHeight),
            this.getWidth() - thickness * 2,
            this.getHeight() - thickness - this.titleHeight
        );

        this.innerRectangle.setStyles(this.getInnerRectangleProps());

        this.outerRectangle.setStyle('radius', thickness * 2);

        this.shape.recalculateBounds();

        this.getConnections().invoke('reposition');
    }
});
