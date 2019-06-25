
Charts.showEditor = function(mychUtil) {
    this.mychUtil = mychUtil;

    // Load tinyMCE in place of the object
    tinyMCE.init({
        mode: "none",
        theme: "advanced",
        plugins: "jbimages,spellchecker,style,table,fullscreen",
        theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect,fontselect",
        theme_advanced_buttons2: "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,|,image,jbimages,|,cleanup,styleprops,forecolor,backcolor,code",
        theme_advanced_buttons3: "tablecontrols,|,spellchecker",
        theme_advanced_buttons4: "",
        theme_advanced_fonts: "Arial=arial,helvetica,sans-serif;" +
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
        theme_advanced_toolbar_location: "top",
        theme_advanced_toolbar_align: "left",
        theme_advanced_statusbar_location: false,
        theme_advanced_advanced_resizing: false,
        spellchecker_languages: "+English=en",
        spellchecker_rpc_url: "/common/tinymce/plugins/spellchecker/rpc.php",
        init_instance_callback: function() {
            var ed = tinyMCE.get('mceBox');
            ed.setContent(mychUtil.config.content);
        },
        convert_urls: false
    });

    chGreybox.create('', 620, 300);
    document.getElementById('greybox_content').appendChild(this.editor);

    document.getElementById('greybox_content').style.paddingRight = '30px';
    document.getElementById('greybox_content').style.paddingBottom = '45px';
    document.getElementById('greybox_content').style.paddingLeft = '15px';
    tinyMCE.execCommand('mceAddControl', true, 'mceBox');

    chGreybox.preClose = function() {
        tinyMCE.execCommand('mceRemoveControl', false, 'mceBox');
        document.editingBox = false;
    };
}

Charts.insertFCKcontent = function() {
    var thexhtml = tinyMCE.activeEditor.getContent();
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
    tinyMCE.execCommand('mceRemoveControl', false, 'mceBox');
    document.editingBox = false;
    chGreybox.close();
    Charts.redraw();
}
Charts.waitingConnectionSource = null;
