
(function($){

    // === helper functions ===

    //Find number of objects in an object
    function ObjectLength_Modern( object ) {
        return Object.keys(object).length;
    }

    //Old browsers
    function ObjectLength_Legacy( object ) {
        var length = 0;
        for( var key in object ) {
            if( object.hasOwnProperty(key) ) {
                ++length;
            }
        }
        return length;
    }

    //Candy
    var ObjectLength =
        Object.keys ? ObjectLength_Modern : ObjectLength_Legacy;





    /**
     *  === APN - Approved Program Name and Skillset selection behavior ===
     *
     * Show Approved Program Names based on Skillset
     */

    function APN( args ){
        window.APNScope = this;

        // Provide this scope to callbacks
        var _this = this;

        // Provide arguments to this scope
        this.args = args || {
            drawingId : 0,
            drawingType : '', //Choices: 'pathways' (roadmap drawings), 'post' (plan of study), 'post_views' (post views)
            programId : 0
        };

        var ROADMAP_DRAWING_TYPE = 'pathways',
            POST_DRAWING_TYPE = 'post',
            POST_VIEWS_TYPE = 'post_views';

        //double check this value and error if it's not correct.
        if(args.drawingType !== ROADMAP_DRAWING_TYPE && args.drawingType !== POST_DRAWING_TYPE && args.drawingType !== POST_VIEWS_TYPE){
            if(console && console.error){
                console.error('args.drawingType needs to be either "'+ROADMAP_DRAWING_TYPE+'" (for roadmap drawings) or "'+POST_DRAWING_TYPE+'" (for post drawings) or "'+POST_VIEWS_TYPE+'" (for post views). Got: ' + args.drawingType);
            }
            return;
        }

        // Get a list of all programs (Approved Program Names) and make them available to this scope.
        this.allPrograms = {};
        var _url = '/a/drawings_post.php?mode=json&resource=programs&drawingtype=' + this.args.drawingType;
        $.get(_url,
            function( _allPrograms ){
                APNScope.allPrograms = JSON.parse(_allPrograms);
                //Set program name select initially
                APNScope.programName.filterSelect( APNScope.skillset.selectedId(), _this.args.programId );
            });


        //skillset run-time model
        this.skillset = {
            $htmlSelect: $('#skillset_id'), //html id of the <select> element
            selectedId: function(){
                return this.$htmlSelect.children('option:selected').attr('value');
            },
            selectedName: function(){
                return this.$htmlSelect.children('option:selected').text();
            },
            //update the skillset <select> to the appropriate skillset based on a program id.
            updateSelected: function(program){
                if(program.skillset_id != this.selectedId()){
                    this.$htmlSelect.val(program.skillset_id);
                    this.save();
                    //make sure only program names with this skillset id are shown, if they're not already.
                    _this.programName.filterSelect(program.skillset_id, program.id);
                }
            },
            save: function(){
                var drawingId = APNScope.args.drawingId,
                    drawingType = APNScope.args.drawingType;

                var postParams =  {
                    action: 'skillset',
                    mode: drawingType,
                    id: drawingId,
                    skillset_id: this.selectedId()
                }
                
                if(drawingId){
                    $.post('drawings_post.php', postParams,
                        function() {
                            APNScope.programName.$saveBtn.click(); //save program name also            

                            $('#skillsetConf').html('Saved!');

                            $('#skillset select').css({backgroundColor: '#99FF99'});
                            setTimeout(function() {
                                $('#skillset select').css({backgroundColor: '#FFFFFF'});
                                $('#skillsetConf').html('');
                            }, 500);
                        }
                    );
                } else {
                    //console.log('not saving because new drawing')
                }
            }
        };

        //approved program name run-time model
        this.programName = {
        
            $htmlSelect: $('#program_id'), //html id of the <select> element
            selected: function(){
                var _selectedId = this.selectedId();
                for ( var i = 0; i < _this.allPrograms.length; i++ ){
                    if(_this.allPrograms[i].id == _selectedId){
                        return _this.allPrograms[i];
                    }
                    
                }
            },
            selectedId: function(){
                return this.$htmlSelect.children('option:selected').attr('value');
            },
            selectedName: function(){
                return this.$htmlSelect.children('option:selected').text();
            },

            //apply approved program name to all areas of the view which display it
            apply: function(){
            },

            // Save Approved Program Name back to the database
            save: function(){
                var drawingId = APNScope.args.drawingId,
                    drawingType = APNScope.args.drawingType;

                var _url = '/a/drawings_post.php'
                    + '?mode=' + drawingType
                    + '&id=' + drawingId
                    + '&changeTitle=true'
                    + '&program_id=' + URLEncode( this.selectedId() );

                var _this = this; //provide this scope to callback
                if(drawingId){
                    $.get( _url, function(response){
                        var r = eval(response);
                        $('#drawing_header').html(r.header);
                        if('function' === typeof updateDrawingLinks){
                            updateDrawingLinks(r.code);
                        }
                        $("body").trigger("drawingheaderchanged");
                    });
                }
            },

            // Filter the Approved Program Name <select> to only show names within the current skill set.
            filterSelect: function( skillsetId, programId ) {
                var options = '',
                    allPrograms = _this.allPrograms;

                options += '<option value="0"></option>';
                
                if(skillsetId){
                    for ( var i = 0; i < allPrograms.length; i++ ){
                        if ( allPrograms[i] && allPrograms[i].title && allPrograms[i].title.length > 0 && allPrograms[i].skillset_id == skillsetId ) {
                            var selected = ( allPrograms[i].id == programId ) ? 'selected="selected"' : '';
                            options += '<option value="' + allPrograms[i].id + '" ' + selected + '>' + allPrograms[i].title + '</option>';
                        }
                    }
                } else {
                    for ( var i = 0; i < allPrograms.length; i++ ){
                        if ( allPrograms[i] && allPrograms[i].title && allPrograms[i].title.length > 0 ) {
                            var selected = ( allPrograms[i].id == programId ) ? 'selected="selected"' : '';
                            options += '<option value="' + allPrograms[i].id + '" ' + selected + '>' + allPrograms[i].title + '</option>';
                        }
                    }
                }

                _this.programName.$htmlSelect.html( options );
            },

            $saveBtn: $( '.approved-program-name .save' ),

            $htmlSelect: $( 'select#program_id' )
        };

        // === Event handlers ===
        //when a new skill set is selected, change which Approved Program Names are available
        this.skillset.$htmlSelect.bind( 'change', function() {
            _this.programName.filterSelect( _this.skillset.selectedId() );
            _this.skillset.save();
            //when creating new drawings, make sure the drawing_title field gets updated when skillset changes
            $('#drawing_form.new_drawing #drawing_title').val(_this.programName.selectedName());
        });
        
        this.programName.$htmlSelect.bind( 'change', function() {
            _this.skillset.updateSelected(_this.programName.selected());
            //when creating new drawings, make sure the drawing_title field gets updated when program name changes
            $('#drawing_form.new_drawing #drawing_title').val(_this.programName.selectedName());
        });     
            

        //when the approved program name save button is clicked
        this.programName.$saveBtn.click( function() {
            var select = $( this ).parent().find( 'select option:selected' );
            //Grab the program name and ID from the sibling <select>
            var newProgramId = select.val(),
                newProgramName = select.text();
            _this.programName.save();
        });    

        return this;
    }

    window.APN = APN; //provide this module to global scope

}(jQuery));
