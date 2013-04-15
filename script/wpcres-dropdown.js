
// JavaScript Document
(function() {
    tinymce.create('tinymce.plugins.wpcres', {
        init : function(ed, url) {
          // nothing to do here
        },
        createControl : function(n, cm) {
            if(n=='wpcres'){
                var mlb = cm.createListBox('wpcresList',{
                    title : 'wpCRES',
                    max_width: 300,
                    onselect : function(v){
                         var ed=this.control_manager.editor;
                         var t=document.forms.post.post_title;
                         var el=document.getElementById('content_wpcresList');
                         
                         ed.focus();
                         ed.selection.setContent('[wpcres id="' + v + '"]');
                         
                         t.focus();
                         t.value=el.options[el.selectedIndex].text;
                         return false;
                    }
                }, tinymce.ui.NativeListBox);
                mlb.onRenderMenu.add(function(c, m) {
                    m.settings['max_width'] = 300;
                });
                // Add some values to the list box
                    for (var i in my_wpcres_posts){
                        
                        mlb.add(my_wpcres_posts[i][0],my_wpcres_posts[i][1]);
                    }

                    // Return the new listbox instance
                return mlb;
            }
            return null;
        } 
    });
    tinymce.PluginManager.add('wpcres', tinymce.plugins.wpcres);
})();

