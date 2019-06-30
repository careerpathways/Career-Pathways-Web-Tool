
Charts.showEditor = function(mychUtil) {
    this.mychUtil = mychUtil;
    
    chGreybox.create('', 620, 300);
    document.getElementById('greybox_content').appendChild(this.editor);
    
    document.getElementById('greybox_content').style.paddingRight = '30px';
    document.getElementById('greybox_content').style.paddingBottom = '45px';
    document.getElementById('greybox_content').style.paddingLeft = '15px';
    
    tinymce.init({
        selector: '#mceBox',
        plugins: 'code image jbimages link lists spellchecker table',
        toolbar1: 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | formatselect | fontsizeselect | fontselect',
        toolbar2: 'bullist numlist | outdent indent blockquote | undo redo | link unlink | image jbimages | forecolor backcolor code',
        toolbar3: 'table | tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablesplitcells tablemergecells | spellchecker',
        menubar: false,
        font_formats: "Arial=arial,helvetica,sans-serif;" +
            "Arial Black=arial black,avant garde;" +
            "Comic Sans MS=comic sans ms,sans-serif;" +
            "Courier New=courier new,courier;" +
            "Georgia=georgia,palatino;" +
            "Impact=impact,chicago;" +
            "Tahoma=tahoma,arial,helvetica,sans-serif;" +
            "Terminal=terminal,monaco;" +
            "Times New Roman=times new roman,times;" +
            "Trebuchet MS=trebuchet ms,geneva;" +
            "Verdana=verdana,geneva", //NOTE - the last one needs to NOT have a semi-colon at the end.
        spellchecker_rpc_url: "/common/tinymce/plugins/spellchecker/rpc.php",
        spellchecker_languages: "+English=en",
        branding: false,
        width : 709,
        resize: 'both',
        init_instance_callback: function() {
            tinymce.activeEditor.setContent(mychUtil.config.content);
        },
    });
    
    chGreybox.preClose = function() {
        if (tinymce.activeEditor) {
            tinymce.activeEditor.destroy();
        }
        document.editingBox = false;
    };
};

Charts.insertFCKcontent = function() {
    var thexhtml = tinymce.activeEditor.getContent();
    this.mychUtil.contentElement.innerHTML = thexhtml;
    this.mychUtil.config.content_html = thexhtml;
    this.mychUtil.config.content = thexhtml;

    chUtil.ajax({
        id: this.mychUtil.id,
        a: 'update',
        content: {
            config: {
                content_html: thexhtml,
                content: thexhtml
            }
        }
    });

    this.mychUtil._onContentChange();
    this.mychUtil.reposition();
    this.mychUtil = null;
    tinymce.activeEditor.destroy();
    document.editingBox = false;
    chGreybox.close();
    Charts.redraw();
};
Charts.waitingConnectionSource = null;
